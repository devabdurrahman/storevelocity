<!DOCTYPE html>
<html lang="en">
<head>
    @include('storefront.partials.head')
    <title>{{ $seo['title'] ?? ($__env->yieldContent('title') ?: $store->name) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { --shop-radius: 0; }
        body { font-family: 'Hind Siliguri', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Playfair Display', 'Noto Serif Bengali', serif; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased"
      x-data="{ menuOpen: false }"
      x-effect="document.body.classList.toggle('overflow-hidden', menuOpen)"
      @keydown.escape.window="menuOpen = false"
      @resize.window="if (window.innerWidth >= 768) menuOpen = false">
    <a href="#content"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2 focus:shadow-lg">
        Skip to content
    </a>
    @include('storefront.partials.category-drawer')

    @include('storefront.partials.announcement')

    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-[1fr_auto_1fr] items-center gap-2 px-4 py-4 sm:gap-4 sm:py-6">
            <div class="flex items-center gap-1 justify-self-start">
            @if ($theme->categories()->isNotEmpty())
                @include('storefront.partials.menu-button')
            @endif

            {{-- Search opens as a small panel --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button"
                            @click="open = !open; $nextTick(() => $refs.panel.querySelector('input')?.focus())"
                            :aria-expanded="open"
                            class="flex h-10 items-center gap-2 text-sm font-medium hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                        </svg>
                        <span class="hidden sm:inline">Search</span>
                        <span class="sr-only sm:hidden">Search</span>
                    </button>

                    <div x-show="open" x-cloak x-ref="panel"
                         @click.outside="open = false" @keydown.escape.window="open = false"
                         class="absolute left-0 top-full z-30 mt-3 w-72 border border-gray-200 bg-white p-3 shadow-lg sm:w-80">
                        @include('storefront.partials.search', ['id' => 'store-search'])
                    </div>
                </div>
            </div>

            @include('storefront.partials.logo', ['linkClass' => 'justify-self-center'])

            <a href="{{ route('storefront.cart') }}"
               class="inline-flex h-10 items-center justify-self-end text-sm font-medium hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
                <livewire:cart-count />
            </a>
        </div>

        @if ($theme->categories()->isNotEmpty())
            @php
                $currentSlug = request()->route()?->parameter('slug');
                $allActive = request()->routeIs('storefront.products') && ! request('q');
            @endphp

            <nav aria-label="Categories" class="hidden pb-4 md:block">
                <x-shop.scroller class="mx-auto max-w-6xl px-4 text-sm">
                    <div class="mx-auto flex w-max gap-7">
                        <a href="{{ route('storefront.products') }}" @if ($allActive) aria-current="page" @endif
                           @class(['whitespace-nowrap underline-offset-8 hover:underline', 'font-semibold underline decoration-2' => $allActive, 'text-gray-600' => ! $allActive])>All</a>

                        @foreach ($theme->categories() as $navCategory)
                            <a href="{{ route('storefront.category', ['slug' => $navCategory->slug]) }}"
                               @if ($currentSlug === $navCategory->slug) aria-current="page" @endif
                               @class(['whitespace-nowrap underline-offset-8 hover:underline', 'font-semibold underline decoration-2' => $currentSlug === $navCategory->slug, 'text-gray-600' => $currentSlug !== $navCategory->slug])>{{ $navCategory->name }}</a>
                        @endforeach
                    </div>
                </x-shop.scroller>
            </nav>
        @endif
    </header>

    <main id="content" class="mx-auto max-w-6xl px-4 py-10">
        @include('storefront.partials.flash')
        @yield('content')
    </main>

    @include('storefront.partials.footer')
</body>
</html>