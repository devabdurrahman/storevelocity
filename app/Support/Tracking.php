<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Builds the one internal payload shape that the page script
 * turns into Meta Pixel and Google Analytics events.
 * Never put customer details (name, phone, address) in here.
 */
class Tracking
{
    private const CURRENCY = 'BDT';

    public static function item(Product $product, int $quantity = 1, ?ProductVariant $variant = null): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name . ($variant ? ' (' . $variant->label() . ')' : ''),
            'price' => (float) ($variant?->price ?? $product->price),
            'quantity' => $quantity,
            'category' => $product->category?->name,
        ];
    }

    public static function product(Product $product, int $quantity = 1, ?ProductVariant $variant = null): array
    {
        $item = self::item($product, $quantity, $variant);

        return [
            'currency' => self::CURRENCY,
            'items' => [$item],
            'value' => $item['price'] * $quantity,
        ];
    }

    public static function cart(): array
    {
        $lines = app(Cart::class)->lines();

        return [
            'currency' => self::CURRENCY,
            'items' => $lines
                ->map(fn ($line) => self::item($line['product'], $line['quantity'], $line['variant']))
                ->values()
                ->all(),
            'value' => (float) $lines->sum('total'),
        ];
    }

    public static function purchase(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'currency' => self::CURRENCY,
            'transaction_id' => 'S' . $order->store_id . 'O' . $order->number,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->product_id ?? 'item-' . $item->id,
                'name' => $item->product_name . ($item->variant_name ? ' (' . $item->variant_name . ')' : ''),
                'price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'category' => null,
            ])->values()->all(),
            'value' => (float) $order->subtotal,
            'shipping' => (float) $order->shipping_fee,
            'total' => (float) $order->total,
        ];
    }
}