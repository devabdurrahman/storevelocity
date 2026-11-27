@php
    $variant = $variant ?? 'default';
    $id = $id ?? 'store-search';

    $inputClasses = match ($variant) {
        'pill' => 'rounded-full border-transparent bg-gray-100 focus:border-gray-300 focus:bg-white',
        'attached' => 'rounded-l-shop rounded-r-none border-gray-300 bg-white',
        default => 'rounded-shop border-gray-300 bg-white',
    };
@endphp

<form action="{{ route('storefront.products') }}" method="get" role="search" class="relative flex w-full">
    <label for="{{ $id }}" class="sr-only">Search products</label>

    @if ($variant !== 'attached')
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
    @endif

    <input id="{{ $id }}" name="q" type="search" value="{{ request('q') }}" placeholder="Search products"
           autocomplete="off" enterkeyhint="search"
           class="w-full border py-2.5 pr-3 text-base placeholder:text-gray-500 focus:outline-none focus:ring-0 focus-visible:outline-none {{ $variant === 'attached' ? 'pl-4' : 'pl-10' }} {{ $inputClasses }}">

    @if ($variant === 'attached')
        <button type="submit" aria-label="Search"
                class="rounded-r-shop bg-brand px-4 text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
        </button>
    @endif
</form>