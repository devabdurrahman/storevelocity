<?php

namespace App\Livewire;

use App\Exceptions\CheckoutException;
use App\Support\Cart;
use App\Support\CurrentStore;
use App\Support\Delivery;
use App\Support\PlaceOrder;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Checkout extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $address = '';
    public ?string $note = null;
    public string $deliveryZone = '';
    public string $website = ''; // honeypot

    protected function zones(): array
    {
        return Delivery::zones(app(CurrentStore::class)->getOrFail());
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'deliveryZone' => ['required', Rule::in(array_keys($this->zones()))],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid mobile number, for example 01712345678.',
            'deliveryZone.required' => 'Please choose where we should deliver.',
            'deliveryZone.in' => 'Please choose where we should deliver.',
        ];
    }

    public function mount()
    {
        if (app(Cart::class)->lines()->isEmpty()) {
            return $this->redirectRoute('storefront.cart');
        }

        // With only one delivery area there is nothing to choose
        $zones = $this->zones();

        if (count($zones) === 1) {
            $this->deliveryZone = (string) array_key_first($zones);
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'phone', 'address', 'note', 'deliveryZone'], true)) {
            $this->validateOnly($property);
        }
    }

    public function place()
    {
        // Honeypot: real customers never see or fill this field
        if ($this->website !== '') {
            return $this->redirectRoute('storefront.index');
        }

        $data = $this->validate();

        // Store the number in one format: 01XXXXXXXXX
        $data['phone'] = preg_replace('/^(\+?88)/', '', $data['phone']);

        $data['delivery_zone'] = $data['deliveryZone'];
        unset($data['deliveryZone']);

        $storeId = app(CurrentStore::class)->getOrFail()->id;
        $ipKey = "checkout:ip:{$storeId}:" . request()->ip();
        $phoneKey = "checkout:phone:{$storeId}:{$data['phone']}";

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $this->addError('cart', 'Too many order attempts. Please wait a while, or contact the store directly.');

            return;
        }

        RateLimiter::hit($ipKey, 600);
        RateLimiter::hit($phoneKey, 3600);

        try {
            $order = app(PlaceOrder::class)->handle($data);
        } catch (CheckoutException $e) {
            $this->addError('cart', $e->getMessage());

            return;
        }

        $this->dispatch('cart-updated');

        return $this->redirectRoute('storefront.order.thanks', ['publicId' => $order->public_id]);
    }

    public function render()
    {
        $store = app(CurrentStore::class)->getOrFail();
        $cart = app(Cart::class);

        $subtotal = $cart->subtotal();
        $zones = Delivery::zones($store);

        $zone = isset($zones[$this->deliveryZone]) ? $this->deliveryZone : null;
        $shipping = $zone ? Delivery::feeFor($store, $zone, $subtotal) : null;

        // What each area costs for THIS cart (free-delivery rules included)
        $zoneOptions = collect($zones)
            ->map(fn (array $area, string $key) => $area + ['charge' => Delivery::feeFor($store, $key, $subtotal)])
            ->all();

        return view('livewire.checkout', [
            'lines' => $cart->lines(),
            'subtotal' => $subtotal,
            'zoneOptions' => $zoneOptions,
            'shipping' => $shipping,
            'total' => $shipping === null ? null : $subtotal + $shipping,
            'zoneLabel' => $zone ? $zones[$zone]['label'] : null,
            'deliveryNote' => 'Choose a delivery area',
        ]);
    }
}