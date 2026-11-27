<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="{{ $theme->actionColor() }}">
<link rel="icon" href="{{ $theme->logoUrl() ?? asset('favicon.svg') }}">

@include('storefront.partials.seo')
@include('storefront.partials.tracking')

<style>
    :root {
        --store-brand: {{ $theme->actionColor() }};
        --store-brand-ink: {{ $theme->actionInk() }};
        --store-brand-raw: {{ $theme->brandColor() }};
        --shop-radius: 0.5rem;
    }

    /* Themes override this */
    .font-display { font-family: inherit; }

    /* A very light tint of the brand colour for quiet backgrounds */
    .bg-brand-wash {
        background-color: #f9fafb;
        background-color: color-mix(in srgb, var(--store-brand-raw) 7%, white);
    }

    .richtext p { margin-bottom: 0.75rem; }
    .richtext ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.75rem; }
    .richtext ol { list-style: decimal; padding-left: 1.25rem; margin-bottom: 0.75rem; }
    .richtext li { margin-bottom: 0.25rem; }
    .richtext a { text-decoration: underline; }
    .richtext strong { font-weight: 600; }

    [x-cloak] { display: none !important; }

    .no-scrollbar { scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }

    /* A soft fade at the right edge when a row can scroll */
    /* .fade-x {
        -webkit-mask-image: linear-gradient(to right, #000 calc(100% - 2.5rem), transparent);
        mask-image: linear-gradient(to right, #000 calc(100% - 2.5rem), transparent);
    } */
    /* Soft fades only on the side that has more to scroll */
    .fade-r {
        -webkit-mask-image: linear-gradient(to right, #000 calc(100% - 2.5rem), transparent);
        mask-image: linear-gradient(to right, #000 calc(100% - 2.5rem), transparent);
    }
    .fade-l {
        -webkit-mask-image: linear-gradient(to left, #000 calc(100% - 2.5rem), transparent);
        mask-image: linear-gradient(to left, #000 calc(100% - 2.5rem), transparent);
    }
    .fade-lr {
        -webkit-mask-image: linear-gradient(to right, transparent, #000 2.5rem, #000 calc(100% - 2.5rem), transparent);
        mask-image: linear-gradient(to right, transparent, #000 2.5rem, #000 calc(100% - 2.5rem), transparent);
    }

    /* Touch screens swipe, so they don't need the arrow buttons */
    @media (pointer: coarse) {
        .scroll-arrow { display: none !important; }
    }

    /* The picture in a promotion banner fades into its background */
    .promo-fade {
        -webkit-mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
        mask-image: linear-gradient(to bottom, #000 55%, transparent 100%);
    }
    @media (min-width: 768px) {
        .promo-fade {
            -webkit-mask-image: linear-gradient(to right, transparent 0%, #000 32%);
            mask-image: linear-gradient(to right, transparent 0%, #000 32%);
        }
    }
    /* Buttons deepen on hover instead of fading lighter */
    .hover\:opacity-90:hover { opacity: 1 !important; filter: brightness(0.92); }

    /* A question that is open gets a clearer border in the store's colour */
    .faq-item { transition: border-color .15s ease; }
    .faq-item[open] { border-color: color-mix(in srgb, var(--store-brand-raw) 55%, #d1d5db); }

    /* Hero slider: reserve the space so nothing jumps while pictures load */
    .slider-box { aspect-ratio: var(--ratio-m); max-height: 70vh; }
    @media (min-width: 640px) { .slider-box { aspect-ratio: var(--ratio); } }

    /* Touch screens swipe, so they don't need arrow buttons */
    @media (pointer: coarse) { .slider-arrow { display: none !important; } }
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])