@php
    $logoClasses = match ($theme->logoSize()) {
        'small' => 'h-8 max-w-36 sm:h-10 sm:max-w-48',
        'large' => 'h-12 max-w-52 sm:h-14 sm:max-w-64 lg:h-16 lg:max-w-80',
        default => 'h-10 max-w-44 sm:h-12 sm:max-w-56 lg:h-14 lg:max-w-72',
    };

    $footerCategories = $theme->categories();
@endphp

<footer class="mt-12 bg-white text-white border-t border-gray-200">

    {{-- Main Footer --}}
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">

            {{-- Store Information --}}
            <div>
                <a href="{{ route('storefront.index') }}"
                   class="inline-flex shrink-0 rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white {{ $linkClass ?? '' }}">
                    @if ($theme->logoUrl())
                        <img src="{{ $theme->logoUrl() }}"
                             alt="{{ $store->name }}"
                             class="w-auto object-contain {{ $logoClasses }}">
                    @else
                        <span class="font-display block max-w-44 truncate text-xl font-bold leading-tight sm:max-w-xs sm:text-2xl">
                            {{ $store->name }}
                        </span>
                    @endif
                </a>

                @if ($theme->footerAboutText())
                    <p class="mt-4 max-w-xs text-sm leading-6 text-gray-600">
                        {{ $theme->footerAboutText() }}
                    </p>
                @endif

                {{-- Social Links --}}
                @if ($theme->facebookUrl() || $theme->whatsappUrl())
                    <div class="mt-5 flex items-center gap-2.5">
                        @if ($theme->facebookUrl())
                            <a href="{{ $theme->facebookUrl() }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               aria-label="Visit us on Facebook"
                               class="flex h-10 w-10 items-center justify-center rounded-full bg-brand text-white transition hover:bg-brand hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.9c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3V10H7.3v3h2.8v8h3.4z"/>
                                </svg>
                            </a>
                        @endif

                        @if ($theme->whatsappUrl())
                            <a href="{{ $theme->whatsappUrl() }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               aria-label="Chat with us on WhatsApp"
                               class="flex h-10 w-10 items-center justify-center rounded-full bg-brand text-white transition hover:bg-brand hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M20.52 3.48A11.86 11.86 0 0 0 12.04 0C5.45 0 .09 5.35.08 11.94c0 2.1.55 4.15 1.6 5.96L0 24l6.25-1.64a11.95 11.95 0 0 0 5.78 1.47h.01c6.59 0 11.95-5.36 11.96-11.95a11.87 11.87 0 0 0-3.48-8.4ZM12.04 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.47 4.46-9.92 9.93-9.92a9.87 9.87 0 0 1 7.02 2.91 9.86 9.86 0 0 1 2.9 7.03c-.01 5.47-4.46 9.92-9.96 9.92Zm5.45-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.49-.9-.8-1.5-1.78-1.68-2.08-.17-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.22 1.35.19 1.85.12.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Categories --}}
            <div>
                <h3 class="text-sm font-semibold capitalize text-gray-900">
                    Categories
                </h3>

                <ul class="mt-4 space-y-2.5 text-sm text-gray-600">
                    @forelse ($footerCategories as $navCategory)
                        <li>
                            <a href="{{ route('storefront.category', ['slug' => $navCategory->slug]) }}"
                               class="inline-flex transition-colors hover:text-gray-600 focus-visible:text-white focus-visible:outline-none">
                                {{ $navCategory->name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-white/50">Categories coming soon.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Delivery & Payment --}}
            <div>
                <h3 class="text-sm font-semibold capitalize text-gray-900">
                    Delivery & Payment
                </h3>

                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-[18px] w-[18px] shrink-0 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <rect x="2.5" y="5" width="19" height="14" rx="2"/>
                            <path d="M2.5 10h19M7 15h3"/>
                        </svg>
                        <span>Cash on delivery</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-[18px] w-[18px] shrink-0 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path d="M3 7h12v11H3z"/>
                            <path d="M15 11h4l3 3v4h-7z"/>
                            <circle cx="7.5" cy="19" r="1.5"/>
                            <circle cx="18.5" cy="19" r="1.5"/>
                        </svg>
                        <span>{{ \App\Support\Delivery::summary($store) }}.</span>
                    </li>
                </ul>
            </div>

            {{-- Contact Information --}}
            <div>
                <h3 class="text-sm font-semibold capitalize text-gray-900">
                    Get in touch
                </h3>

                <p class="mt-4 max-w-xs text-sm leading-6 text-gray-600">
                    Have questions about your order? Feel free to contact us.
                </p>

                <div class="mt-4 flex flex-col items-start gap-3">
                    @if ($theme->whatsappUrl())
                        <a href="{{ $theme->whatsappUrl() }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2.5 text-sm text-gray-600 transition hover:text-gray-600 focus-visible:text-gray-600">
                            <svg class="h-[17px] w-[17px] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M20.52 3.48A11.86 11.86 0 0 0 12.04 0C5.45 0 .09 5.35.08 11.94c0 2.1.55 4.15 1.6 5.96L0 24l6.25-1.64a11.95 11.95 0 0 0 5.78 1.47h.01c6.59 0 11.95-5.36 11.96-11.95a11.87 11.87 0 0 0-3.48-8.4ZM12.04 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.47 4.46-9.92 9.93-9.92a9.87 9.87 0 0 1 7.02 2.91 9.86 9.86 0 0 1 2.9 7.03c-.01 5.47-4.46 9.92-9.96 9.92Zm5.45-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.49-.9-.8-1.5-1.78-1.68-2.08-.17-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.22 1.35.19 1.85.12.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
                            </svg>
                            WhatsApp
                        </a>
                    @endif

                    @if ($theme->facebookUrl())
                        <a href="{{ $theme->facebookUrl() }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2.5 text-sm text-gray-600 transition hover:text-gray-600 focus-visible:text-gray-600">
                            <svg class="h-[17px] w-[17px] shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.9c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3V10H7.3v3h2.8v8h3.4z"/>
                            </svg>
                            Facebook
                        </a>
                    @endif

                    @unless ($theme->whatsappUrl() || $theme->facebookUrl())
                        <span class="text-sm text-white/50">
                            Contact details coming soon.
                        </span>
                    @endunless
                </div>
            </div>

        </div>
    </div>

    {{-- Footer Bottom Bar --}}
    <div class="border-t border-gray-200">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-5 text-center sm:flex-row sm:text-left">
            <p class="text-xs text-gray-600">
                © {{ date('Y') }} {{ $store->name }}. All rights reserved.
            </p>

            <a href="{{ config('app.url') }}"
               class="text-xs text-gray-600 transition hover:text-gray-600">
                Powered by {{ config('app.name') }}
            </a>
        </div>
    </div>

</footer>


{{-- Floating WhatsApp Button --}}
{{-- Hidden on cart and checkout pages --}}
@if ($theme->whatsappUrl() && ! $__env->hasSection('flow'))
    <a href="{{ $theme->whatsappUrl() }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Chat with us on WhatsApp"
       class="fixed bottom-5 right-5 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-brand text-brand-ink shadow-xl transition duration-200 hover:scale-105 hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
        <svg class="h-7 w-7"
             viewBox="0 0 24 24"
             fill="currentColor"
             xmlns="http://www.w3.org/2000/svg"
             aria-hidden="true">
            <path d="M20.52 3.48A11.86 11.86 0 0 0 12.04 0C5.45 0 .09 5.35.08 11.94c0 2.1.55 4.15 1.6 5.96L0 24l6.25-1.64a11.95 11.95 0 0 0 5.78 1.47h.01c6.59 0 11.95-5.36 11.96-11.95a11.87 11.87 0 0 0-3.48-8.4ZM12.04 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.52-5.25c0-5.47 4.46-9.92 9.93-9.92a9.87 9.87 0 0 1 7.02 2.91 9.86 9.86 0 0 1 2.9 7.03c-.01 5.47-4.46 9.92-9.96 9.92Zm5.45-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.49-.9-.8-1.5-1.78-1.68-2.08-.17-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.22 1.35.19 1.85.12.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/>
        </svg>
    </a>
@endif
