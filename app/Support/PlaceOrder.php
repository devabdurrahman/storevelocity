<?php

namespace App\Support;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;

class PlaceOrder
{
    public function __construct(
        private CurrentStore $currentStore,
        private Cart $cart,
    ) {
    }

    public function handle(array $customer): Order
    {
        $store = $this->currentStore->getOrFail();
        $storeId = $store->id;

        if ($store->blockedPhones()->where('phone', $customer['phone'])->exists()) {
            // Deliberately vague, so it doesn't start an argument
            throw new CheckoutException('We are unable to process this order online. Please contact the store directly.');
        }

        $hasRecentPending = $store->orders()
            ->where('customer_phone', $customer['phone'])
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();

        if ($hasRecentPending) {
            throw new CheckoutException('You already placed an order a moment ago. Please wait for our call, or contact the store.');
        }

        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        $zones = Delivery::zones($store);
        $zone = $customer['delivery_zone'] ?? (count($zones) === 1 ? array_key_first($zones) : null);

        if (! $zone || ! isset($zones[$zone])) {
            throw new CheckoutException('Please choose where we should deliver.');
        }

        $order = DB::transaction(function () use ($storeId, $lines, $customer, $zone) {
            // Lock the store row (protects the order counter)
            $store = Store::whereKey($storeId)->lockForUpdate()->firstOrFail();

            $productIds = $lines->pluck('product.id')->unique()->all();

            // Lock products and variations and re-check stock with fresh data
            $products = $store->products()
                ->where('is_active', true)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $variantIds = $lines->pluck('variant.id')->filter()->unique()->all();

            $variants = $variantIds
                ? ProductVariant::whereIn('id', $variantIds)
                    ->whereIn('product_id', $productIds)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id')
                : collect();

            $subtotal = 0;
            $items = [];

            foreach ($lines as $line) {
                $product = $products[$line['product']->id] ?? null;
                $variant = $line['variant'] ? ($variants[$line['variant']->id] ?? null) : null;

                $available = null;

                if ($product) {
                    $available = $product->has_variants ? $variant?->stock : $product->stock;
                }

                if ($available === null || $available < $line['quantity']) {
                    throw new CheckoutException(
                        ($product?->name ?? 'An item') . ' is no longer available in the quantity you chose. Please review your cart.'
                    );
                }

                // Prices always come from the database, never from the cart or the browser
                $unitPrice = (float) ($variant?->price ?? $product->price);
                $lineTotal = $unitPrice * $line['quantity'];
                $subtotal += $lineTotal;

                $items[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'unit_price' => $unitPrice,
                    'quantity' => $line['quantity'],
                    'total' => $lineTotal,
                ];
            }

            $store->increment('order_counter');
            $shipping = Delivery::feeFor($store, $zone, $subtotal);

            $order = $store->orders()->create([
                'public_id' => (string) Str::ulid(),
                'number' => $store->order_counter,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_address' => $customer['address'],
                'customer_note' => $customer['note'] ?? null,
                'payment_method' => 'cod',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping,
                'total' => $subtotal + $shipping,
                'delivery_zone' => $zone,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'variant_id' => $item['variant']?->id,
                    'product_name' => $item['product']->name,
                    'variant_name' => $item['variant'] ? $this->variantDescription($item['product'], $item['variant']) : null,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);

                // Stock comes off the variation when there is one, otherwise off the product
                if ($item['variant']) {
                    $item['variant']->decrement('stock', $item['quantity']);
                } else {
                    $item['product']->decrement('stock', $item['quantity']);
                }
            }

            return $order;
        });

        $this->cart->clear();

        return $order;
    }

    /** For example "Size: M, Colour: Red". Saved on the order, so it never changes later. */
    private function variantDescription(Product $product, ProductVariant $variant): string
    {
        return collect([
            [$product->option1_name, $variant->option1],
            [$product->option2_name, $variant->option2],
        ])
            ->filter(fn ($pair) => filled($pair[1]))
            ->map(fn ($pair) => filled($pair[0]) ? $pair[0] . ': ' . $pair[1] : $pair[1])
            ->implode(', ');
    }
}