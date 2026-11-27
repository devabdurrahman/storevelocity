<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Support\CurrentStore;
use App\Support\Delivery;
use App\Support\Throttle;

class CartPage extends Component
{
    #[On('cart-updated')]
    public function refresh(): void
    {
    }

    public function setQuantity(string $lineKey, int $quantity): void
    {
        if (! Throttle::allow('cart', 60)) {
            return;
        }
        app(Cart::class)->setQuantity($lineKey, $quantity);
        $this->dispatch('cart-updated');
    }

    public function remove(string $lineKey): void
    {
        if (! Throttle::allow('cart', 60)) {
            return;
        }
        app(Cart::class)->remove($lineKey);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $store = app(CurrentStore::class)->getOrFail();
        $cart = app(Cart::class);

        $subtotal = $cart->subtotal();
        $zones = Delivery::zones($store);

        // One area: the charge is known. Two areas: it is chosen at checkout.
        if (count($zones) === 1) {
            $zone = (string) array_key_first($zones);
            $shipping = Delivery::feeFor($store, $zone, $subtotal);
            $total = $subtotal + $shipping;
            $zoneLabel = $zones[$zone]['label'];
        } else {
            $shipping = null;
            $total = null;
            $zoneLabel = null;
        }

        return view('livewire.cart-page', [
            'lines' => $cart->lines(),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'zoneLabel' => $zoneLabel,
            'deliveryNote' => 'Chosen at checkout',
        ]);
    }
}