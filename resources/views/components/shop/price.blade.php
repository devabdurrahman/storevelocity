@props(['product', 'size' => 'text-base'])

<span {{ $attributes->class(['inline-flex flex-wrap items-baseline gap-x-2 tabular-nums']) }}>
    @if ($product->hasPriceRange())
        <span class="text-xs font-normal text-gray-600">From</span>
    @endif

    <span class="{{ $size }} font-bold"><x-shop.money :amount="$product->priceFrom()" /></span>

    @if (! $product->has_variants && $product->onSale())
        <span class="text-sm text-gray-500 line-through"><x-shop.money :amount="$product->compare_price" /></span>
        <span class="rounded bg-red-50 px-1.5 py-0.5 text-xs font-semibold text-red-700">−{{ $product->discountPercent() }}%</span>
    @endif

    @if ($product->unit)
        <span class="text-xs font-normal text-gray-500">{{ $product->unit }}</span>
    @endif
</span>