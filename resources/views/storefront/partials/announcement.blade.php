@if ($theme->announcement())
    <div class="bg-brand px-4 py-2 text-center text-sm font-medium text-brand-ink">
        {{ $theme->announcement() }}
    </div>
@endif