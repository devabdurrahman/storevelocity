@extends($themeLayout)

@section('title', ($search !== '' ? 'Search' : ($category?->name ?? 'Products')) . ' - ' . $store->name)

@section('content')
    <div class="mb-5 flex flex-wrap items-end justify-between gap-x-4 gap-y-1">
        <h1 class="font-display text-2xl font-bold leading-snug">
            {{ $search !== '' ? 'Results for “' . $search . '”' : ($category?->name ?? 'All products') }}
        </h1>
        @include('storefront.partials.sort', ['noun' => 'item', 'nounPlural' => 'items'])
    </div>

    @if ($products->isEmpty())
        @include('storefront.partials.empty')
    @else
        @include('storefront.partials.product-grid', ['items' => $products])
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
@endsection