@extends($themeLayout)

@section('title', ($search !== '' ? 'Search' : ($category?->name ?? 'Collection')) . ' - ' . $store->name)

@section('content')
    <div class="mb-10 flex flex-wrap items-end justify-between gap-x-6 gap-y-1">
        <h1 class="font-display text-3xl font-bold leading-snug sm:text-4xl">
            {{ $search !== '' ? 'Results for “' . $search . '”' : ($category?->name ?? 'All products') }}
        </h1>
        @include('storefront.partials.sort', ['noun' => 'piece', 'nounPlural' => 'pieces'])
    </div>

    @if ($products->isEmpty())
        @include('storefront.partials.empty')
    @else
        @include('storefront.partials.product-grid', ['items' => $products])
        <div class="mt-12">{{ $products->links() }}</div>
    @endif
@endsection