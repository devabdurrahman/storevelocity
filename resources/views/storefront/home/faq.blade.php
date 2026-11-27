@php
    $faq = $theme->faqItems();

    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';

    $round = $isFresh ? 'rounded-3xl' : ($isRunway ? '' : 'rounded-2xl');

    // The box around each question, per theme
    $item = $isRunway
        ? 'faq-item border-b border-gray-300'
        : 'faq-item ' . $round . ' border border-gray-200 bg-white';
@endphp

@if ($faq)
    <section class="mb-12 mt-4" aria-labelledby="home-faq-title">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8 text-center sm:mb-10">
                <h2 id="home-faq-title" class="font-display text-2xl font-bold leading-snug sm:text-3xl sm:leading-snug">{{ $theme->faqTitle() }}</h2>

                @if ($theme->faqText())
                    <p class="mx-auto mt-3 max-w-xl leading-relaxed text-gray-600">{{ $theme->faqText() }}</p>
                @endif
            </div>

            <div @class(['border-t border-gray-300' => $isRunway, 'space-y-3' => ! $isRunway])>
                @foreach ($faq as $entry)
                    <details name="home-faq" class="group {{ $item }}">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6 sm:py-5 {{ $round }} focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-gray-900 [&::-webkit-details-marker]:hidden">
                            <span @class([
                                'text-base leading-snug sm:text-lg',
                                'font-display font-semibold' => $isRunway,
                                'font-semibold' => ! $isRunway,
                            ])>{{ $entry['q'] }}</span>

                            {{-- A plus that turns into a minus when the question is open --}}
                            <span @class([
                                'relative flex shrink-0 items-center justify-center text-gray-700',
                                'h-8 w-8 rounded-full bg-brand-wash' => $isFresh,
                                'h-6 w-6' => ! $isFresh,
                            ]) aria-hidden="true">
                                <span class="absolute h-0.5 w-3.5 rounded bg-current"></span>
                                <span class="absolute h-3.5 w-0.5 rounded bg-current transition-transform duration-200 group-open:rotate-90"></span>
                            </span>
                        </summary>

                        <p class="max-w-2xl whitespace-pre-line px-5 pb-5 leading-relaxed text-gray-600 sm:px-6 sm:pb-6">{{ $entry['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif