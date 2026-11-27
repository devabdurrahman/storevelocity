@extends($themeLayout)

@section('title', 'Checkout - ' . $store->name)
@section('robots', 'noindex, nofollow')
@section('flow', '1')

@section('content')
    <h1 class="font-display text-2xl font-bold leading-snug sm:text-3xl">Checkout</h1>

    <div class="mt-6">
        <livewire:checkout />
    </div>

    @php($trackingCart = \App\Support\Tracking::cart())

    @if (count($trackingCart['items']))
        <script>
            window.storeTrack && window.storeTrack('begin_checkout', @js($trackingCart));
        </script>
    @endif
@endsection