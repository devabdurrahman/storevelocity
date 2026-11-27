@php
    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';

    // Each theme keeps its own corners and button shape
    $corner = $isFresh ? 'rounded-3xl' : ($isRunway ? '' : 'rounded-2xl');
    $pill = $isRunway ? '' : 'rounded-full';

    $button = 'inline-flex shrink-0 items-center justify-center px-6 py-2.5 text-sm font-semibold hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--ring)] ' . $pill;
@endphp

@if ($promo['image'])
    {{-- One surface: the picture fills the right side and fades into the background --}}
    <section class="relative isolate mb-12 overflow-hidden {{ $corner }} md:min-h-64"
             style="background-color: {{ $promo['bg'] }};">
        <img src="{{ $promo['image'] }}" alt="" loading="lazy"
             class="promo-fade block h-48 w-full object-cover md:absolute md:inset-y-0 md:right-0 md:h-full md:w-[62%]">

        <div class="relative z-10 px-6 pb-8 pt-2 md:flex md:min-h-64 md:max-w-[46%] md:flex-col md:justify-center md:px-12 md:py-10 lg:px-14">
            <h2 class="font-display text-2xl font-bold leading-snug sm:text-3xl sm:leading-snug lg:text-4xl lg:leading-snug"
                style="color: {{ $promo['headline'] }};">{{ $promo['title'] }}</h2>

            @if ($promo['text'])
                <p class="mt-2 max-w-md text-base leading-relaxed sm:text-lg"
                   style="color: {{ $promo['subtext'] }};">{{ $promo['text'] }}</p>
            @endif

            @if ($promo['button'])
                <div class="mt-5 md:mt-6">
                    <a href="{{ $promo['url'] }}" class="{{ $button }}"
                       style="--ring: {{ $promo['bg_dark'] ? '#ffffff' : '#111827' }}; background-color: {{ $promo['button_bg'] }}; color: {{ $promo['button_ink'] }};">{{ $promo['button'] }}</a>
                </div>
            @endif
        </div>
    </section>
@else
    {{-- No picture: a slim strip in the chosen colour --}}
    <section class="mb-12 flex flex-col gap-5 px-6 py-8 sm:px-10 md:flex-row md:items-center md:justify-between {{ $corner }}"
             style="background-color: {{ $promo['color'] }}; color: {{ $promo['ink'] }};">
        <div class="max-w-2xl">
            <h2 class="font-display text-2xl font-bold leading-snug sm:text-3xl sm:leading-snug">{{ $promo['title'] }}</h2>

            @if ($promo['text'])
                <p class="mt-2 text-base leading-relaxed" style="opacity: .9;">{{ $promo['text'] }}</p>
            @endif
        </div>

        @if ($promo['button'])
            <a href="{{ $promo['url'] }}" class="{{ $button }}"
               style="--ring: {{ $promo['ink'] }}; background-color: {{ $promo['ink'] }}; color: {{ $promo['color'] }};">{{ $promo['button'] }}</a>
        @endif
    </section>
@endif