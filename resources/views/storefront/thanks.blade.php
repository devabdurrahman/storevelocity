@extends($themeLayout)

@section('title', 'Order placed - ' . $store->name)
@section('robots', 'noindex, nofollow')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand text-brand-ink">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
            </div>
            <h1 class="font-display mt-5 text-3xl font-bold leading-snug">Order placed</h1>
            <p class="mt-2 text-gray-600">
                Thank you, {{ $order->customer_name }}. Your order number is
                <strong class="text-gray-900">#{{ $order->number }}</strong>.
            </p>
        </div>

        <section class="mt-8 rounded-shop border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-display text-lg font-bold">What happens next</h2>
            <ul class="mt-3 space-y-2 text-gray-700">
                <li>{{ $store->name }} will call {{ $order->customer_phone }} to confirm your order.</li>

                @if ($order->payment_method === 'bkash' && $order->payment_status === 'paid')
                    <li>Your bKash payment has been received{{ $order->bkash_trx_id ? ' (TrxID ' . $order->bkash_trx_id . ')' : '' }}.</li>
                @elseif ($order->payment_method === 'bkash')
                    <li>We are waiting for your bKash payment to be confirmed.</li>
                @else
                    <li>Pay in cash when your order arrives.</li>
                @endif
                @if ($order->tracking_code)
                    <li>Your parcel has been booked with Steadfast Courier. Tracking code: <strong class="text-gray-900">{{ $order->tracking_code }}</strong></li>
                @endif
            </ul>
        </section>

        <section class="mt-5 rounded-shop border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-display text-lg font-bold">Your order</h2>

            <ul class="mt-3 divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 py-3 text-sm">
                        <span>{{ $item->product_name }}@if ($item->variant_name) ({{ $item->variant_name }})@endif <span class="text-gray-500">× {{ $item->quantity }}</span></span>
                        <span class="shrink-0 tabular-nums"><x-shop.money :amount="$item->total" /></span>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-2 space-y-2 border-t border-gray-200 pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd class="tabular-nums"><x-shop.money :amount="$order->subtotal" /></dd></div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Delivery
                        @if ($order->delivery_zone) ({{ \App\Support\Delivery::label($order->delivery_zone) }})
                        @endif
                    </dt>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-bold"><dt>Total</dt><dd class="tabular-nums"><x-shop.money :amount="$order->total" /></dd></div>
            </dl>

            <div class="mt-5 border-t border-gray-200 pt-4 text-sm">
                <p class="font-medium">Delivering to</p>
                <p class="mt-1 whitespace-pre-line text-gray-600">{{ $order->customer_address }}</p>
            </div>
        </section>

        <div class="mt-8 text-center">
            <a href="{{ route('storefront.products') }}"
               class="inline-flex rounded-shop bg-brand px-6 py-3 font-semibold text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                Continue shopping
            </a>
        </div>
    </div>

    @if ($track)
        <script>
            window.storeTrack && window.storeTrack('purchase', @js($track));
        </script>
    @endif
@endsection