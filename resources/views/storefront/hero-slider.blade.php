@php
    $slides = $theme->heroSlides();
    $ratios = $theme->heroSliderRatios();
    $count = count($slides);

    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';

    // Each theme keeps its own corners
    $corner = $isFresh ? 'rounded-3xl' : ($isRunway ? '' : 'rounded-shop');
    $control = $isRunway ? '' : 'rounded-full';

    $autoplay = $count > 1 && $theme->heroSliderAutoplay();
@endphp

<script>
    window.heroSlider = function (count, autoplay, interval) {
        return {
            index: 0, count: count, playing: autoplay, hover: false, focus: false, timer: null, reduced: false,

            init() {
                this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (this.reduced) { this.playing = false; }

                this.$nextTick(() => this.start());
                document.addEventListener('visibilitychange', () => { document.hidden ? this.stop() : this.start(); });
                window.addEventListener('resize', () => this.go(this.index, false));
            },

            go(i, smooth = true) {
                this.index = (i + this.count) % this.count;
                const track = this.$refs.track;
                track.scrollTo({ left: track.clientWidth * this.index, behavior: smooth && !this.reduced ? 'smooth' : 'auto' });
            },

            next() { this.go(this.index + 1); },
            prev() { this.go(this.index - 1); },

            onScroll() {
                const track = this.$refs.track;
                this.index = Math.round(track.scrollLeft / track.clientWidth);
            },

            start() {
                this.stop();
                if (!this.playing || this.count < 2) { return; }
                this.timer = setInterval(() => { if (!this.hover && !this.focus) { this.next(); } }, interval);
            },

            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },

            toggle() { this.playing = !this.playing; this.playing ? this.start() : this.stop(); },

            touch() { this.hover = true; clearTimeout(this.releaseTimer); this.releaseTimer = setTimeout(() => { this.hover = false; }, 4000); },
        };
    };
</script>

<section class="group/slider relative mb-8"
         role="region" aria-roledescription="carousel" aria-label="{{ $store->name }} offers"
         style="--ratio: {{ $ratios['desktop'] }}; --ratio-m: {{ $ratios['mobile'] }};"
         x-data="heroSlider({{ $count }}, {{ $autoplay ? 'true' : 'false' }}, {{ $theme->heroSliderSeconds() * 1000 }})"
         @mouseenter="hover = true" @mouseleave="hover = false"
         @focusin="focus = true" @focusout="focus = false"
         @touchstart.passive="touch()"
         @keydown.arrow-left="prev()" @keydown.arrow-right="next()">

    <div class="relative overflow-hidden {{ $corner }} {{ $isRunway ? '' : 'border border-gray-200' }}">
        <div x-ref="track"
             @scroll.passive.debounce.80ms="onScroll()"
             :aria-live="playing ? 'off' : 'polite'"
             class="no-scrollbar flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain">

            @foreach ($slides as $i => $slide)
                <div class="slider-box w-full shrink-0 snap-center"
                     role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $count }}"
                     :aria-hidden="index !== {{ $i }} ? 'true' : 'false'">

                    @if ($slide['url'])
                        <a href="{{ $slide['url'] }}" :tabindex="index === {{ $i }} ? 0 : -1"
                           class="block h-full w-full focus-visible:outline-2 focus-visible:-outline-offset-4 focus-visible:outline-gray-900">
                    @endif

                    <picture>
                        @if ($slide['mobile'])
                            <source media="(max-width: 639px)" srcset="{{ $slide['mobile'] }}">
                        @endif
                        <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" draggable="false" decoding="async"
                             @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                             class="block h-full w-full object-cover">
                    </picture>

                    @if ($slide['url'])
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($count > 1)
            {{-- Arrows: shown on hover with a mouse, hidden on touch screens (swiping is natural there) --}}
            <button type="button" @click="prev()" aria-label="Previous slide"
                    class="slider-arrow absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center {{ $control }} bg-white/90 text-gray-900 opacity-0 shadow-md transition-opacity hover:bg-white focus-visible:opacity-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 group-hover/slider:opacity-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
            </button>

            <button type="button" @click="next()" aria-label="Next slide"
                    class="slider-arrow absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center {{ $control }} bg-white/90 text-gray-900 opacity-0 shadow-md transition-opacity hover:bg-white focus-visible:opacity-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 group-hover/slider:opacity-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
            </button>

            {{-- Dots, and a pause button when slides change by themselves --}}
            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1 {{ $control }} bg-black/40 px-2 py-0.5 backdrop-blur-sm">
                @if ($autoplay)
                    <button type="button" @click="toggle()" :aria-label="playing ? 'Pause slides' : 'Play slides'"
                            class="flex h-6 w-6 items-center justify-center text-white focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-white">
                        <svg x-show="playing" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                        <svg x-show="!playing" x-cloak class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5v14l12-7z"/></svg>
                    </button>
                @endif

                @foreach ($slides as $i => $slide)
                    <button type="button" @click="go({{ $i }})" aria-label="Go to slide {{ $i + 1 }}"
                            :aria-current="index === {{ $i }} ? 'true' : 'false'"
                            class="flex h-6 items-center justify-center px-1 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-white">
                        <span class="block h-2 {{ $control }} bg-white transition-all duration-200"
                              :class="index === {{ $i }} ? 'w-5' : 'w-2 opacity-60'"></span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</section>