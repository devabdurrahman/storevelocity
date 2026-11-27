@php
    $cols = $theme->gridColumns();

    $gap = match ($theme->slug) {
        'fresh' => 'gap-3',
        'runway' => 'gap-x-4 gap-y-10',
        default => 'gap-3 sm:gap-4',
    };

    $card = view()->exists('storefront.cards.' . $theme->slug)
        ? 'storefront.cards.' . $theme->slug
        : 'storefront.cards.classic';

    // A paginator keeps its products inside getCollection()
    $items = method_exists($items, 'getCollection')
        ? $items->getCollection()->values()
        : collect($items)->values();

    $count = $items->count();
    $points = ['base', 'sm', 'md', 'lg'];

    // Written out in full so Tailwind can see every class name
    $gridClasses = [
        'base' => [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3', 4 => 'grid-cols-4', 5 => 'grid-cols-5'],
        'sm' => [1 => 'sm:grid-cols-1', 2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-3', 4 => 'sm:grid-cols-4', 5 => 'sm:grid-cols-5'],
        'md' => [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4', 5 => 'md:grid-cols-5'],
        'lg' => [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5'],
    ];

    $perRow = [];
    $take = [];

    foreach ($points as $point) {
        $full = $cols[$point];

        // Normal pages (product list, categories): every product, the theme's own columns
        if (empty($rows)) {
            $perRow[$point] = $full;
            $take[$point] = $count;

            continue;
        }

        $maxRows = $rows[$point] ?? 2;

        $perRow[$point] = $full;
        $take[$point] = min($count, $maxRows * $full);

        // Not enough for two full rows: make two EQUAL rows that fit what we have
        if ($maxRows >= 2 && $count > $full && $count < 2 * $full) {
            $balanced = intdiv($count, 2);

            if ($balanced >= 2) {
                $perRow[$point] = $balanced;
                $take[$point] = $balanced * 2;
            } else {
                $take[$point] = $full; // too few for two even rows: one row
            }
        }
    }

    // Home shelves: the listed screen sizes show as many products as the desktop does
    foreach (($sameAsDesktop ?? []) as $point) {
        if (isset($take[$point])) {
            $take[$point] = $take['lg'];
        }
    }

    $columns = collect($points)->map(fn ($point) => $gridClasses[$point][$perRow[$point]])->implode(' ') . ' ' . $gap;

    $card = $card;

    // Which cards show at each screen size
    $visibility = function (int $index) use ($take): string {
        return implode(' ', [
            $index < $take['base'] ? 'contents' : 'hidden',
            $index < $take['sm'] ? 'sm:contents' : 'sm:hidden',
            $index < $take['md'] ? 'md:contents' : 'md:hidden',
            $index < $take['lg'] ? 'lg:contents' : 'lg:hidden',
        ]);
    };
@endphp

<div class="grid {{ $columns }}">
    @foreach ($items as $index => $product)
        @if (empty($rows))
            @include($card, ['product' => $product])
        @else
            <div class="{{ $visibility($index) }}">
                @include($card, ['product' => $product])
            </div>
        @endif
    @endforeach
</div>