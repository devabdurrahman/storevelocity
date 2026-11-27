<!DOCTYPE html>
<html lang="en">
<head>
    @include('storefront.partials.head')
    <title>{{ $seo['title'] ?? ($__env->yieldContent('title') ?: $store->name) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Anek Bangla', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased"
      x-data="{ menuOpen: false }"
      x-effect="document.body.classList.toggle('overflow-hidden', menuOpen)"
      @keydown.escape.window="menuOpen = false"
      @resize.window="if (window.innerWidth >= 768) menuOpen = false">
    <a href="#content"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-shop focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">
        Skip to content
    </a>
    @include('storefront.partials.category-drawer')

    @include('storefront.partials.announcement')

    <div x-data="{ searchOpen: false }" class="sticky top-0 z-30 border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center gap-2 px-4 py-3 sm:gap-3 md:gap-6 md:py-4">
            @if ($theme->categories()->isNotEmpty())
                @include('storefront.partials.menu-button')
            @endif

            @include('storefront.partials.logo')

            {{-- Search: in the middle on larger screens --}}
            <div class="hidden flex-1 md:mx-auto md:block md:max-w-2xl">
                @include('storefront.partials.search', ['variant' => 'attached', 'id' => 'store-search'])
            </div>

            <div class="ml-auto flex items-center gap-2">
                {{-- Search: an icon on phones --}}
                <button type="button" @click="searchOpen = !searchOpen; $nextTick(() => $refs.mobileSearch.querySelector('input')?.focus())"
                        :aria-expanded="searchOpen" aria-label="Search"
                        class="flex h-10 w-10 items-center justify-center rounded-shop hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 md:hidden">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </button>

                @if ($theme->whatsappUrl())
                    <a href="{{ $theme->whatsappUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="hidden h-10 items-center gap-2 rounded-shop px-3 text-sm font-medium hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 md:inline-flex">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12a8 8 0 0 1-11.7 7.1L4 20l.9-5.1A8 8 0 1 1 21 12z"/>
                        </svg>
                        <span class="hidden lg:inline">Chat</span>
                        <span class="sr-only lg:hidden">Chat on WhatsApp</span>
                    </a>
                @endif

                <a href="{{ route('storefront.cart') }}"
                   class="inline-flex h-10 items-center rounded-shop border border-gray-300 px-3.5 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    <livewire:cart-count />
                </a>
            </div>
        </div>

        <div x-show="searchOpen" x-cloak x-ref="mobileSearch" class="border-t border-gray-100 px-4 py-3 md:hidden">
            @include('storefront.partials.search', ['variant' => 'attached', 'id' => 'store-search-mobile'])
        </div>
    </div>

    @if ($theme->categories()->isNotEmpty())
        @php
            $currentSlug = request()->route()?->parameter('slug');
            $allActive = request()->routeIs('storefront.products') && ! request('q');
        @endphp

        <nav aria-label="Categories" class="hidden border-b border-gray-200 bg-white md:block">
            <x-shop.scroller class="mx-auto max-w-6xl px-4 text-sm">
                <div class="flex w-max gap-6">
                    <a href="{{ route('storefront.products') }}" @if ($allActive) aria-current="page" @endif
                       @class([
                           'whitespace-nowrap border-b-2 py-3',
                           'border-brand font-semibold' => $allActive,
                           'border-transparent text-gray-600 hover:text-gray-900' => ! $allActive,
                       ])>All</a>

                    @foreach ($theme->categories() as $navCategory)
                        <a href="{{ route('storefront.category', ['slug' => $navCategory->slug]) }}"
                           @if ($currentSlug === $navCategory->slug) aria-current="page" @endif
                           @class([
                               'whitespace-nowrap border-b-2 py-3',
                               'border-brand font-semibold' => $currentSlug === $navCategory->slug,
                               'border-transparent text-gray-600 hover:text-gray-900' => $currentSlug !== $navCategory->slug,
                           ])>{{ $navCategory->name }}</a>
                    @endforeach
                </div>
            </x-shop.scroller>
        </nav>
    @endif

    <main id="content" class="mx-auto max-w-6xl px-4 py-8">
        @include('storefront.partials.flash')
        @yield('content')
    </main>

    @include('storefront.partials.footer')
</body>
</html>