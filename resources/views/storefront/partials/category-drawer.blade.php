@php
    $categories = $theme->categories();
    $currentSlug = request()->route()?->parameter('slug');
    $allActive = request()->routeIs('storefront.products') && ! request('q');

    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';

    $panel = $isFresh ? 'rounded-r-3xl' : '';

    $row = match (true) {
        $isFresh => 'mx-3 flex items-center rounded-xl px-4 py-3 text-base',
        $isRunway => 'font-display flex items-center border-b border-gray-200 px-6 py-4 text-lg',
        default => 'flex items-center border-l-4 px-5 py-3.5 text-base',
    };

    $active = match (true) {
        $isFresh => 'bg-brand font-semibold text-brand-ink',
        $isRunway => 'font-bold underline decoration-2 underline-offset-8',
        default => 'border-brand bg-gray-50 font-semibold',
    };

    $idle = match (true) {
        $isFresh => 'text-gray-800 hover:bg-gray-100',
        $isRunway => 'text-gray-700 hover:bg-gray-50',
        default => 'border-transparent text-gray-700 hover:bg-gray-50',
    };

    $focus = 'focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-gray-900';
@endphp

@if ($categories->isNotEmpty())
    {{-- The dimmed page behind the panel --}}
    <div x-show="menuOpen" x-cloak x-transition.opacity.duration.200ms
         @click="menuOpen = false" aria-hidden="true"
         class="fixed inset-0 z-50 bg-black/50 md:hidden"></div>

    {{-- The panel --}}
    <div id="category-menu" x-show="menuOpen" x-cloak x-trap="menuOpen"
         x-transition:enter="transition-transform ease-out duration-200 motion-reduce:transition-none"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition-transform ease-in duration-150 motion-reduce:transition-none"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         role="dialog" aria-modal="true" aria-label="Browse categories"
         class="fixed inset-y-0 left-0 z-50 flex w-[86%] max-w-xs flex-col bg-white shadow-xl md:hidden {{ $panel }}">

        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-3">
            <p class="font-display text-lg font-bold">Categories</p>

            <button type="button" @click="menuOpen = false" autofocus aria-label="Close menu"
                    class="flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <nav aria-label="Categories" class="flex-1 overflow-y-auto overscroll-contain py-2">
            <ul @class(['space-y-1' => $isFresh])>
                <li>
                    <a href="{{ route('storefront.products') }}" @if ($allActive) aria-current="page" @endif
                       class="{{ $row }} {{ $allActive ? $active : $idle }} {{ $focus }}">All products</a>
                </li>

                @foreach ($categories as $category)
                    @php $isCurrent = $currentSlug === $category->slug; @endphp

                    <li>
                        <a href="{{ route('storefront.category', ['slug' => $category->slug]) }}" @if ($isCurrent) aria-current="page" @endif
                           class="{{ $row }} {{ $isCurrent ? $active : $idle }} {{ $focus }}">{{ $category->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($theme->whatsappUrl())
            <div class="border-t border-gray-200 p-4">
                <a href="{{ $theme->whatsappUrl() }}" target="_blank" rel="noopener noreferrer"
                   class="flex items-center justify-center gap-2 border border-gray-300 px-4 py-3 text-sm font-semibold hover:bg-gray-50 {{ $isRunway ? '' : 'rounded-full' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12a8 8 0 0 1-11.7 7.1L4 20l.9-5.1A8 8 0 1 1 21 12z"/>
                    </svg>
                    Chat on WhatsApp
                </a>
            </div>
        @endif
    </div>
@endif