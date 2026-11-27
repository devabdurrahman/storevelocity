<span class="inline-flex items-center gap-2.5">
    <span class="relative">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/>
        </svg>

        @if ($count > 0)
            <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold leading-none tabular-nums text-brand-ink" aria-hidden="true">{{ $count }}</span>
        @endif
    </span>

    <span class="sr-only">Cart, {{ $count }} {{ $count === 1 ? 'item' : 'items' }}</span>

    @if ($count > 0)
        <span class="hidden text-sm font-semibold tabular-nums sm:inline"><x-shop.money :amount="$total" /></span>
    @else
        <span class="hidden text-sm font-medium sm:inline">Cart</span>
    @endif
</span>