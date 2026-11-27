<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCount extends Component
{
    #[On('cart-updated')]
    public function refresh(): void
    {
        // Listening is enough: Livewire re-renders after this runs
    }

    public function render()
    {
        $lines = app(Cart::class)->lines();

        return view('livewire.cart-count', [
            'count' => (int) $lines->sum('quantity'),
            'total' => (float) $lines->sum('total'),
        ]);
    }
}