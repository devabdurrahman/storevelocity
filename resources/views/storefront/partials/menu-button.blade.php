@php
    $shape = match ($theme->slug) {
        'fresh' => 'rounded-full bg-gray-100 hover:bg-gray-200',
        'runway' => 'hover:bg-gray-100',
        default => 'rounded-shop hover:bg-gray-100',
    };
@endphp

<button type="button" @click="menuOpen = true" :aria-expanded="menuOpen" aria-controls="category-menu" aria-haspopup="dialog"
        class="flex h-10 w-10 shrink-0 items-center justify-center md:hidden {{ $shape }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
    <span class="sr-only">Browse categories</span>
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
        <path d="M4 7h16M4 12h16M4 17h16"/>
    </svg>
</button>