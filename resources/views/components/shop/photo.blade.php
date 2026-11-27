@props(['product'])
@php $url = $product->mainImageUrl(); @endphp
@if ($url)
    <img src="{{ $url }}" alt="{{ $product->name }}" loading="lazy" {{ $attributes->class(['object-cover']) }}>
@else
    <div {{ $attributes->class(['flex items-center justify-center bg-gray-100 text-gray-300']) }}>
        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.5"/><path d="m21 15-5-5L5 21"/>
        </svg>
    </div>
@endif