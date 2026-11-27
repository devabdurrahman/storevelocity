<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Cart;
use App\Support\CurrentStore;
use App\Support\Tracking;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Support\Throttle;

class ProductBuyBox extends Component
{
    public int $productId;
    public ?string $selected1 = null;
    public ?string $selected2 = null;
    public int $quantity = 1;

    #[Computed]
    public function product(): Product
    {
        // Only a visible product of THIS store, with its active variations
        return app(CurrentStore::class)->getOrFail()->visibleProducts()
            ->with(['variants' => fn ($query) => $query->where('is_active', true)])
            ->findOrFail($this->productId);
    }

    #[Computed]
    public function variant(): ?ProductVariant
    {
        $product = $this->product;

        if (! $this->selected1 || ($product->option2_name && ! $this->selected2)) {
            return null;
        }

        return $product->variants->first(
            fn ($variant) => $variant->option1 === $this->selected1
                && (! $product->option2_name || $variant->option2 === $this->selected2)
        );
    }

    public function mount(): void
    {
        // A single variation needs no choosing
        $variants = $this->product->variants;

        if ($variants->count() === 1) {
            $this->selected1 = $variants->first()->option1;
            $this->selected2 = $variants->first()->option2;
        }
    }

    public function choose(int $option, int $index): void
    {
        $values = collect($this->buildOptions($option))->pluck('value')->all();
        $value = $values[$index] ?? null;

        if ($value === null) {
            return;
        }

        if ($option === 1) {
            $this->selected1 = $value;
        } else {
            $this->selected2 = $value;
        }

        $this->quantity = 1;
        $this->resetErrorBag();
    }

    public function add(): void
    {
        if (! Throttle::allow('cart', 40)) {
            $this->addError('selection', 'You are doing that too quickly. Please wait a moment.');

            return;
        }
        
        $this->validate(['quantity' => 'required|integer|min:1|max:99']);

        $variant = $this->variant;

        if (! $variant) {
            $this->addError('selection', 'Please choose ' . strtolower((string) $this->missingLabel()) . '.');

            return;
        }

        if ($variant->stock < 1) {
            $this->addError('selection', 'This option is out of stock.');

            return;
        }

        $quantity = min($this->quantity, $variant->stock);

        // The cart re-checks the variation on the server
        app(Cart::class)->add($this->productId, $quantity, $variant->id);

        $this->dispatch('cart-updated');

        $payload = json_encode(
            Tracking::product($this->product, $quantity, $variant),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );

        $this->js("window.storeTrack && window.storeTrack('add_to_cart', {$payload})");
    }

    protected function missingLabel(): ?string
    {
        $product = $this->product;

        if (! $this->selected1) {
            return $product->option1_name;
        }

        if ($product->option2_name && ! $this->selected2) {
            return $product->option2_name;
        }

        return null;
    }

    /** The values for one option, with whether each can currently be bought. */
    protected function buildOptions(int $n): array
    {
        $variants = $this->product->variants;
        $field = 'option' . $n;
        $other = $n === 1 ? 'option2' : 'option1';
        $otherSelected = $n === 1 ? $this->selected2 : $this->selected1;
        $selected = $n === 1 ? $this->selected1 : $this->selected2;

        return $variants->pluck($field)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->values()
            ->map(function ($value) use ($variants, $field, $other, $otherSelected, $selected) {
                $available = $variants->contains(
                    fn ($variant) => $variant->{$field} === $value
                        && $variant->stock > 0
                        && (! $otherSelected || $variant->{$other} === $otherSelected)
                );

                return ['value' => $value, 'available' => $available, 'active' => $selected === $value];
            })
            ->all();
    }

    public function render()
    {
        $product = $this->product;
        $variant = $this->variant;

        return view('product-buy-box', [
            'product' => $product,
            'variant' => $variant,
            'price' => $variant ? (float) ($variant->price ?? $product->price) : null,
            'options1' => $this->buildOptions(1),
            'options2' => $product->option2_name ? $this->buildOptions(2) : [],
            'missing' => $this->missingLabel(),
            'inCart' => $variant ? app(Cart::class)->quantityOf($this->productId, $variant->id) : 0,
        ]);
    }
}