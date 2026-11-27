@php
    [$height, $width] = match ($theme->logoSize()) {
        'small' => ['h-8 sm:h-10', 'max-w-36 sm:max-w-48'],
        'large' => ['h-12 sm:h-14 lg:h-16', 'max-w-52 sm:max-w-64 lg:max-w-80'],
        default => ['h-10 sm:h-12 lg:h-14', 'max-w-44 sm:max-w-56 lg:max-w-72'],
    };
@endphp

<a href="{{ route('storefront.index') }}"
   class="block min-w-0 shrink rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900 {{ $width }} {{ $linkClass ?? '' }}">
    @if ($theme->logoUrl())
        <img src="{{ $theme->logoUrl() }}" alt="{{ $store->name }}" class="{{ $height }} w-auto max-w-full object-contain">
    @else
        <span class="font-display block truncate text-xl font-bold leading-tight sm:text-2xl">{{ $store->name }}</span>
    @endif
</a>