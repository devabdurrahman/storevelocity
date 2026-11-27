@extends($themeLayout)

@section('content')
    @php
        $product->loadMissing('category');
        $images = $product->imageUrls();
    @endphp

    <nav aria-label="Breadcrumb" class="mb-5 text-sm text-gray-600">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <li><a href="{{ route('storefront.index') }}" class="hover:text-gray-900 hover:underline">Home</a></li>

            @if ($product->category)
                <li aria-hidden="true">/</li>
                <li>
                    <a href="{{ route('storefront.category', ['slug' => $product->category->slug]) }}"
                       class="hover:text-gray-900 hover:underline">{{ $product->category->name }}</a>
                </li>
            @endif

            <li aria-hidden="true">/</li>
            <li aria-current="page" class="text-gray-900">{{ $product->name }}</li>
        </ol>
    </nav>

    {{-- Photos on the left, buying details on the right --}}
    <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
        <div>
            @if ($images)
                <div x-data="{ active: 0, images: @js($images) }">
                    <div class="aspect-square overflow-hidden rounded-shop border border-gray-200 bg-white">
                        <img :src="images[active]" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="mt-3 flex gap-2" x-show="images.length > 1">
                        <template x-for="(img, i) in images" :key="i">
                            <button type="button" @click="active = i" :aria-label="'Show photo ' + (i + 1)"
                                    :aria-current="active === i ? 'true' : 'false'"
                                    class="h-16 w-16 overflow-hidden rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900"
                                    :class="active === i ? 'ring-2 ring-gray-900' : 'ring-1 ring-gray-200'">
                                <img :src="img" alt="" class="h-full w-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            @else
                <x-shop.photo :product="$product" class="aspect-square w-full rounded-shop border border-gray-200" />
            @endif
        </div>

        <div>
            <h1 class="font-display text-3xl font-bold leading-snug">{{ $product->name }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1">
                <x-shop.price :product="$product" size="text-2xl" />
                @unless ($product->has_variants)
                    <x-shop.stock :product="$product" />
                @endunless
            </div>

            @if ($product->short_description)
                <p class="mt-4 leading-relaxed text-gray-700">{{ $product->short_description }}</p>
            @endif

            @if ($product->has_variants && $product->isAvailable())
                <livewire:product-buy-box :product-id="$product->id" />
            @elseif (! $product->has_variants && $product->stock > 0)
                <livewire:add-to-cart :product-id="$product->id" :stock="$product->stock" />
            @else
                <p class="mt-6 rounded-shop bg-gray-100 px-4 py-3 text-sm text-gray-700">
                    This product is out of stock right now.
                </p>
            @endif

            <ul class="mt-8 space-y-2.5 border-t border-gray-200 pt-5 text-sm text-gray-700">
                <li class="flex items-center gap-2.5">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                    Cash on delivery
                </li>
                <li class="flex items-center gap-2.5">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7zM7 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/></svg>
                    Delivery: {{ \App\Support\Delivery::summary($store) }}
                </li>
            </ul>
        </div>
    </div>

    {{-- The description, across the whole row --}}
    @if ($product->descriptionHtml())
        <section class="mt-12 border-t border-gray-200 pt-8">
            <h2 class="font-display text-xl font-bold leading-snug">Description</h2>
            <div class="richtext mt-4 leading-relaxed text-gray-700">{!! $product->descriptionHtml() !!}</div>
        </section>
    @endif

    {{-- Related products --}}
    @if ($related->isNotEmpty())
        <div class="mt-12 border-t border-gray-200 pt-10">
            @include('storefront.partials.shelf', ['title' => 'Related products', 'items' => $related, 'url' => $relatedUrl, 'rows' => \App\Support\Theme::RELATED_ROWS])
        </div>
    @endif

    <script>
        window.storeTrack && window.storeTrack('view_item', @js(\App\Support\Tracking::product($product)));
    </script>
@endsection