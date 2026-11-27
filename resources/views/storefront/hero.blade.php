@php
    $style = $theme->heroStyle();
    $image = $theme->heroImageUrl();
    $mobile = $theme->heroMobileImageUrl();
    $size = $mobile ? null : $theme->heroImageSize();
    $url = $theme->heroButtonUrl();

    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';

    // Each theme keeps its own corners
    $corner = $isFresh ? 'rounded-3xl' : ($isRunway ? '' : 'rounded-shop');
    $pill = $isFresh ? 'rounded-full' : ($isRunway ? '' : 'rounded-shop');

    $button = 'inline-flex items-center justify-center px-6 py-3 font-semibold hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 ' . $pill;
@endphp

@if ($style === 'slider')
    @include('storefront.hero-slider')

@elseif ($style === 'picture')
    {{-- The picture already carries its own words. Show it whole, and make all of it a link. --}}
    <section aria-label="{{ $store->name }} promotion" class="mb-8">
        <a href="{{ $url }}"
           class="block overflow-hidden {{ $corner }} {{ $isRunway ? '' : 'border border-gray-200' }} focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
            <picture>
                @if ($mobile)
                    <source media="(max-width: 639px)" srcset="{{ $mobile }}">
                @endif
                <img src="{{ $image }}" alt="{{ $theme->heroAlt() }}"
                     @if ($size) width="{{ $size[0] }}" height="{{ $size[1] }}" @endif
                     fetchpriority="high" decoding="async"
                     class="block h-auto max-h-[70vh] w-full object-cover">
            </picture>
        </a>
    </section>

@elseif ($style === 'overlay')
    {{-- Headline and button on top of the photo, with a dark shade so they stay readable --}}
    <section class="relative isolate mb-8 flex min-h-80 items-end overflow-hidden {{ $corner }} bg-gray-900 text-white sm:min-h-[26rem] sm:items-center">
        <picture>
            @if ($mobile)
                <source media="(max-width: 639px)" srcset="{{ $mobile }}">
            @endif
            <img src="{{ $image }}" alt="" fetchpriority="high" decoding="async"
                 class="absolute inset-0 -z-10 h-full w-full object-cover">
        </picture>

        <div class="absolute inset-0 -z-10 sm:hidden"
             style="background: linear-gradient(to top, rgba(0,0,0,.8), rgba(0,0,0,.4) 60%, rgba(0,0,0,.1));"></div>
        <div class="absolute inset-0 -z-10 hidden sm:block"
             style="background: linear-gradient(to right, rgba(0,0,0,.72), rgba(0,0,0,.45) 45%, rgba(0,0,0,0) 80%);"></div>

        <div class="w-full px-6 py-8 sm:max-w-xl sm:px-12 sm:py-16 lg:max-w-2xl">
            <h1 class="font-display text-3xl font-bold leading-snug sm:text-5xl sm:leading-tight">{{ $theme->heroTitle() }}</h1>

            @if ($theme->heroSubtitle())
                <p class="mt-3 text-base leading-relaxed text-white/90 sm:text-lg">{{ $theme->heroSubtitle() }}</p>
            @endif

            <a href="{{ $url }}" class="{{ $button }} mt-6 bg-white text-gray-900">{{ $theme->heroButtonText() }}</a>
        </div>
    </section>

@elseif ($style === 'split')
    {{-- Photo on one side, words on the other --}}
    <section class="mb-8 overflow-hidden {{ $corner }} border border-gray-200 bg-white md:grid md:grid-cols-2">
        <picture class="block md:order-last">
            @if ($mobile)
                <source media="(max-width: 639px)" srcset="{{ $mobile }}">
            @endif
            <img src="{{ $image }}" alt="{{ $theme->heroAlt() }}" fetchpriority="high" decoding="async"
                 class="aspect-[4/3] w-full object-cover md:aspect-auto md:h-full md:min-h-[22rem]">
        </picture>

        <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-14">
            <h1 class="font-display text-3xl font-bold leading-snug sm:text-4xl">{{ $theme->heroTitle() }}</h1>

            @if ($theme->heroSubtitle())
                <p class="mt-3 max-w-md text-lg leading-relaxed text-gray-600">{{ $theme->heroSubtitle() }}</p>
            @endif

            <div class="mt-6">
                <a href="{{ $url }}" class="{{ $button }} bg-brand text-brand-ink">{{ $theme->heroButtonText() }}</a>
            </div>
        </div>
    </section>

@elseif ($isFresh)
    {{-- No picture: a confident brand-colour panel --}}
    <section class="mb-8 rounded-3xl bg-brand px-6 py-10 text-brand-ink sm:px-12 sm:py-14">
        <h1 class="font-display max-w-3xl text-3xl font-bold leading-snug sm:text-5xl sm:leading-tight">{{ $theme->heroTitle() }}</h1>

        @if ($theme->heroSubtitle())
            <p class="mt-3 max-w-xl text-lg leading-relaxed opacity-90">{{ $theme->heroSubtitle() }}</p>
        @endif

        <a href="{{ $url }}" class="{{ $button }} mt-6 bg-white text-gray-900">{{ $theme->heroButtonText() }}</a>
    </section>

@elseif ($isRunway)
    {{-- No picture: an editorial headline between two thin lines --}}
    <section class="mb-14 border-y border-gray-200 py-10 sm:py-16">
        <h1 class="font-display max-w-4xl text-4xl font-bold leading-snug sm:text-6xl sm:leading-tight">{{ $theme->heroTitle() }}</h1>

        @if ($theme->heroSubtitle())
            <p class="mt-4 max-w-xl text-lg leading-relaxed text-gray-700">{{ $theme->heroSubtitle() }}</p>
        @endif

        <a href="{{ $url }}" class="{{ $button }} mt-6 bg-brand text-brand-ink">{{ $theme->heroButtonText() }}</a>
    </section>

@else
    {{-- No picture: a clean panel with a small accent bar --}}
    <section class="mb-8 rounded-shop border border-gray-200 bg-white px-6 py-10 sm:px-12 sm:py-14">
        <span class="mb-5 block h-1 w-12 rounded bg-brand"></span>

        <h1 class="font-display max-w-3xl text-3xl font-bold leading-snug sm:text-5xl sm:leading-tight">{{ $theme->heroTitle() }}</h1>

        @if ($theme->heroSubtitle())
            <p class="mt-3 max-w-xl text-lg leading-relaxed text-gray-600">{{ $theme->heroSubtitle() }}</p>
        @endif

        <a href="{{ $url }}" class="{{ $button }} mt-6 bg-brand text-brand-ink">{{ $theme->heroButtonText() }}</a>
    </section>
@endif