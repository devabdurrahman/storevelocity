<a href="{{ $product->storefrontUrl() }}"
   class="flex flex-col overflow-hidden rounded-shop border border-gray-200 bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
    <div class="relative">
        <x-shop.photo :product="$product" class="aspect-square w-full" />
        @unless ($product->isAvailable())
            <span class="absolute left-2 top-2 rounded bg-white px-2 py-1 text-xs font-medium text-gray-700">Out of stock</span>
        @endunless
    </div>
    <div class="flex flex-1 flex-col gap-1 p-3">
        <h2 class="line-clamp-2 text-sm font-medium leading-snug">{{ $product->name }}</h2>
        @if ($product->lowStock())
            <p class="text-xs font-medium text-amber-700">Only {{ $product->totalStock() }} left</p>
        @endif
        <x-shop.price :product="$product" class="mt-auto pt-1" />
    </div>
</a>