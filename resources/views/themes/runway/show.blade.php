@extends($themeLayout)

@section('content')
    @php
        $product->loadMissing('category');
        $images = $product->imageUrls();
    @endphp

    {{-- Photos on the left, buying details on the right --}}
    <div class="grid gap-10 lg:grid-cols-12 lg:gap-14">
        <div class="lg:col-span-7">
            @if ($images)
                <div class="-mx-4 flex snap-x snap-mandatory gap-2 overflow-x-auto px-4 lg:mx-0 lg:block lg:space-y-4 lg:overflow-visible lg:px-0">
                    @foreach ($images as $i => $url)
                        <img src="{{ $url }}" alt="{{ $product->name }}{{ $i > 0 ? ' (photo ' . ($i + 1) . ')' : '' }}"
                             loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                             class="aspect-[4/5] w-[85%] shrink-0 snap-center object-cover lg:w-full">
                    @endforeach
                </div>
            @else
                <x-shop.photo :product="$product" class="aspect-[4/5] w-full" />
            @endif
        </div>

        <div class="lg:col-span-5">
            <div class="lg:sticky lg:top-8">
                @if ($product->category)
                    <a href="{{ route('storefront.category', ['slug' => $product->category->slug]) }}"
                       class="text-sm text-gray-600 underline-offset-4 hover:underline">{{ $product->category->name }}</a>
                @endif

                <h1 class="font-display mt-2 text-3xl font-bold leading-snug sm:text-4xl">{{ $product->name }}</h1>

                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <x-shop.price :product="$product" size="text-xl" />
                    @unless ($product->has_variants)
                        <x-shop.stock :product="$product" />
                    @endunless
                </div>

                @if ($product->short_description)
                    <p class="mt-6 leading-relaxed text-gray-700">{{ $product->short_description }}</p>
                @endif

                @if ($product->has_variants && $product->isAvailable())
                    <livewire:product-buy-box :product-id="$product->id" />
                @elseif (! $product->has_variants && $product->stock > 0)
                    <livewire:add-to-cart :product-id="$product->id" :stock="$product->stock" />
                @else
                    <p class="mt-6 bg-gray-100 px-4 py-3 text-sm text-gray-700">This piece is sold out.</p>
                @endif

                <div class="mt-8 space-y-1.5 border-t border-gray-200 pt-5 text-sm text-gray-700">
                    <p>Cash on delivery</p>
                    <p>Delivery: {{ \App\Support\Delivery::summary($store) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- The description, across the whole row --}}
    @if ($product->descriptionHtml())
        <section class="mt-14 border-t border-gray-200 pt-8">
            <h2 class="font-display text-2xl font-bold">Description</h2>
            <div class="richtext mt-4 leading-relaxed text-gray-700">{!! $product->descriptionHtml() !!}</div>
        </section>
    @endif

    {{-- Related products --}}
    @if ($related->isNotEmpty())
        <div class="mt-14 border-t border-gray-200 pt-10">
            @include('storefront.partials.shelf', ['title' => 'Related products', 'items' => $related, 'url' => $relatedUrl, 'rows' => \App\Support\Theme::RELATED_ROWS])
        </div>
    @endif

    <script>
        window.storeTrack && window.storeTrack('view_item', @js(\App\Support\Tracking::product($product)));
    </script>
@endsection