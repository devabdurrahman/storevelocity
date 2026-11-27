<?php

namespace App\Livewire;

use App\Support\Cart;
use App\Support\CurrentStore;
use App\Support\Tracking;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Support\Throttle;

class AddToCart extends Component
{
    public int $productId;
    public int $stock = 99;       // only used to disable the + button; the server enforces the real limit
    public int $quantity = 1;
    public bool $compact = false;
    public bool $added = false;

    #[On('cart-updated')]
    public function refresh(): void
    {
        // Listening is enough: Livewire re-renders after this runs
    }

    public function add(): void
    {
        if (! Throttle::allow('cart', 40)) {
            return;
        }
        
        $this->validate(['quantity' => 'required|integer|min:1|max:99']);

        // Same store-scoped lookup as the cart itself
        $product = app(CurrentStore::class)->getOrFail()->visibleProducts()->findOrFail($this->productId);

        app(Cart::class)->add($this->productId, $this->quantity);

        $this->added = true;
        $this->dispatch('cart-updated');

        if ($product->stock > 0) {
            $payload = json_encode(
                Tracking::product($product, min($this->quantity, $product->stock)),
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );

            $this->js("window.storeTrack && window.storeTrack('add_to_cart', {$payload})");
        }
    }

    public function increment(): void
    {
        $this->quantity = 1;
        $this->add();
    }

    public function decrement(): void
    {
        $cart = app(Cart::class);
        $cart->setQuantity((string) $this->productId, $cart->quantityOf($this->productId) - 1);

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.add-to-cart', [
            'inCart' => app(Cart::class)->quantityOf($this->productId),
        ]);
    }
}