@props(['product'])
@php $stock = $product->totalStock(); @endphp

@if ($stock <= 0)
    <span class="text-sm font-medium text-gray-500">Out of stock</span>
@elseif ($stock <= 5)
    <span class="text-sm font-medium text-amber-700">Only {{ $stock }} left</span>
@else
    <span class="text-sm font-medium text-green-700">In stock</span>
@endif