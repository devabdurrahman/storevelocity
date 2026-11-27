@extends($themeLayout)

@section('title', $store->name)

@section('content')
    @php $promos = $theme->promos(); @endphp

    @if ($theme->heroEnabled())
        @include('storefront.hero')
    @endif

    <!-- @if ($theme->slug !== 'runway')
        @include('storefront.partials.info-bar')
    @endif -->

    @if ($featuredCategories->isNotEmpty())
        @include('storefront.partials.category-tiles')
    @endif

    @if (isset($promos[0]))
        @include('storefront.home.promo', ['promo' => $promos[0]])
    @endif

    @if ($featuredProducts->isNotEmpty())
        @include('storefront.partials.shelf', ['title' => 'Featured', 'items' => $featuredProducts])
    @endif

    @if (isset($promos[1]))
        @include('storefront.home.promo', ['promo' => $promos[1]])
    @endif

    @if ($newArrivals->isNotEmpty())
        @include('storefront.partials.shelf', ['title' => 'New arrivals', 'items' => $newArrivals])
    @endif

    @if (isset($promos[2]))
        @include('storefront.home.promo', ['promo' => $promos[2]])
    @endif

    @if ($featuredProducts->isEmpty() && $newArrivals->isEmpty())
        @include('storefront.partials.empty', ['search' => ''])
    @endif

    @include('storefront.home.faq')
@endsection