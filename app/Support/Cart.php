<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;

/**
 * The cart lives in the session as [lineKey => quantity].
 * A line key is "12" for a plain product, or "12:34" for product 12, variation 34.
 */
class Cart
{
    public function __construct(private CurrentStore $currentStore)
    {
    }

    private function store(): Store
    {
        return $this->currentStore->getOrFail();
    }

    private function key(): string
    {
        return 'cart.' . $this->store()->id;
    }

    private function raw(): array
    {
        return session($this->key(), []);
    }

    private function makeKey(int $productId, ?int $variantId): string
    {
        return $variantId ? "{$productId}:{$variantId}" : (string) $productId;
    }

    /** @return array{0: int, 1: ?int} */
    private function parseKey(string $key): array
    {
        $parts = explode(':', $key);

        return [(int) $parts[0], isset($parts[1]) ? (int) $parts[1] : null];
    }

    /** Stock for one product or one variation. 0 means it can't be bought. */
    private function stockFor(?Product $product, ?int $variantId): int
    {
        if (! $product) {
            return 0;
        }

        if ($product->has_variants) {
            $variant = $variantId
                ? $product->variants()->where('is_active', true)->find($variantId)
                : null;

            return $variant ? (int) $variant->stock : 0;
        }

        return (int) $product->stock;
    }

    public function add(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        // Only products customers may see, from THIS store
        $product = $this->store()->visibleProducts()->findOrFail($productId);

        if ($product->has_variants) {
            // The variation must belong to this product, be active, and exist
            $product->variants()->where('is_active', true)->findOrFail($variantId);
        } else {
            $variantId = null;
        }

        $stock = $this->stockFor($product, $variantId);
        $lineKey = $this->makeKey($productId, $variantId);

        $cart = $this->raw();
        $newQuantity = min(($cart[$lineKey] ?? 0) + $quantity, $stock);

        if ($newQuantity < 1) {
            return; // out of stock
        }

        $cart[$lineKey] = $newQuantity;
        session([$this->key() => $cart]);
    }

    public function setQuantity(string $lineKey, int $quantity): void
    {
        $cart = $this->raw();

        if (! array_key_exists($lineKey, $cart)) {
            return;
        }

        [$productId, $variantId] = $this->parseKey($lineKey);

        $product = $this->store()->visibleProducts()->find($productId);
        $stock = $this->stockFor($product, $variantId);

        if ($quantity < 1 || $stock < 1) {
            unset($cart[$lineKey]);
        } else {
            $cart[$lineKey] = min($quantity, $stock);
        }

        session([$this->key() => $cart]);
    }

    public function remove(string $lineKey): void
    {
        $cart = $this->raw();
        unset($cart[$lineKey]);
        session([$this->key() => $cart]);
    }

    public function clear(): void
    {
        session()->forget($this->key());
    }

    public function quantityOf(int $productId, ?int $variantId = null): int
    {
        return (int) ($this->raw()[$this->makeKey($productId, $variantId)] ?? 0);
    }

    /**
     * Each line: key, product, variant (or null), name, unit_price, quantity, stock, total.
     * Lines that can no longer be bought (hidden, removed, out of stock) are dropped.
     */
    public function lines(): Collection
    {
        $cart = $this->raw();

        if (empty($cart)) {
            return collect();
        }

        $entries = [];

        foreach ($cart as $lineKey => $quantity) {
            [$productId, $variantId] = $this->parseKey((string) $lineKey);
            $entries[(string) $lineKey] = ['product_id' => $productId, 'variant_id' => $variantId, 'qty' => (int) $quantity];
        }

        $products = $this->store()->visibleProducts()
            ->whereIn('id', collect($entries)->pluck('product_id')->unique()->all())
            ->with(['variants' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->keyBy('id');

        $lines = collect();

        foreach ($entries as $lineKey => $entry) {
            $product = $products->get($entry['product_id']);

            if (! $product) {
                continue;
            }

            $variant = null;

            if ($product->has_variants) {
                $variant = $product->variants->firstWhere('id', $entry['variant_id']);

                if (! $variant) {
                    continue;
                }

                $stock = (int) $variant->stock;
                $unitPrice = (float) ($variant->price ?? $product->price);
            } else {
                $stock = (int) $product->stock;
                $unitPrice = (float) $product->price;
            }

            if ($stock < 1) {
                continue;
            }

            $quantity = min($entry['qty'], $stock);

            $lines->push([
                'key' => (string) $lineKey,
                'product' => $product,
                'variant' => $variant,
                'name' => $product->name . ($variant ? ' (' . $variant->label() . ')' : ''),
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'stock' => $stock,
                'total' => $unitPrice * $quantity,
            ]);
        }

        return $lines->values();
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) $this->lines()->sum('total');
    }
}