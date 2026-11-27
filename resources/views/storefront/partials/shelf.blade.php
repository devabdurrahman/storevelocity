<section class="mb-12">
    <div class="mb-4 flex items-end justify-between gap-4">
        <h2 class="font-display text-xl font-bold leading-snug sm:text-2xl">{{ $title }}</h2>
        <a href="{{ $url ?? route('storefront.products') }}"
           class="shrink-0 text-sm font-medium underline underline-offset-4 hover:no-underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
            View all
        </a>
    </div>

    @include('storefront.partials.product-grid', [
        'items' => $items,
        'rows' => $rows ?? \App\Support\Theme::HOME_ROWS,
        // Phones show as many products as the desktop. Add 'sm' and 'md' to match tablets and laptops too.
        'sameAsDesktop' => $sameAsDesktop ?? (isset($rows) ? [] : ['base']),
    ])
</section>