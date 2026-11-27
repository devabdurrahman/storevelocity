@php $images = $product->imageUrls(); @endphp

<a href="{{ $product->storefrontUrl() }}"
   class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
    <div class="relative aspect-[4/5] overflow-hidden bg-gray-100">
        @if ($images)
            <img src="{{ $images[0] }}" alt="{{ $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition-opacity duration-300 {{ isset($images[1]) ? 'group-hover:opacity-0' : '' }}">
            @if (isset($images[1]))
                <img src="{{ $images[1] }}" alt="" loading="lazy"
                     class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            @endif
        @else
            <x-shop.photo :product="$product" class="h-full w-full" />
        @endif

        @unless ($product->isAvailable())
            <span class="absolute left-3 top-3 bg-white px-2.5 py-1 text-xs font-medium">Sold out</span>
        @endunless
    </div>

    <h2 class="mt-3 text-sm leading-snug">{{ $product->name }}</h2>
    <x-shop.price :product="$product" size="text-sm" class="mt-0.5 text-gray-700" />
</a>