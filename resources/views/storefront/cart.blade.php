@extends($themeLayout)

@section('title', 'Cart - ' . $store->name)
@section('robots', 'noindex, nofollow')
@section('flow', '1')

@section('content')
    <h1 class="font-display text-2xl font-bold leading-snug sm:text-3xl">Your cart</h1>

    <div class="mt-6">
        <livewire:cart-page />
    </div>
@endsection