<div class="flex flex-col rounded-2xl border border-gray-200 bg-white p-3">
    <a href="{{ $product->storefrontUrl() }}"
       class="block rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
        <div class="relative">
            <x-shop.photo :product="$product" class="aspect-square w-full rounded-xl" />
            @unless ($product->isAvailable())
                <span class="absolute left-2 top-2 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-700">Out of stock</span>
            @endunless
        </div>
        <h2 class="mt-2.5 line-clamp-2 text-sm font-medium leading-snug">{{ $product->name }}</h2>
    </a>

    @if ($product->lowStock())
        <p class="mt-1 text-xs font-medium text-amber-700">Only {{ $product->totalStock() }} left</p>
    @endif

    <div class="mt-auto flex items-center justify-between gap-2 pt-3">
        <x-shop.price :product="$product" class="font-display" />

        @if ($product->isAvailable())
            @if ($product->has_variants)
                <a href="{{ $product->storefrontUrl() }}"
                   class="rounded-full border border-gray-300 px-3.5 py-1.5 text-sm font-semibold hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    Options
                </a>
            @else
                <livewire:add-to-cart :product-id="$product->id" :stock="$product->stock" :compact="true" wire:key="add-{{ $product->id }}" />
            @endif
        @endif
    </div>
</div>