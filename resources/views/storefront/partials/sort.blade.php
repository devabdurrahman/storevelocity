@php
    $sorts = \App\Support\ProductSort::options();
    $current = $sort ?? \App\Support\ProductSort::DEFAULT;

    $isFresh = $theme->slug === 'fresh';
    $isRunway = $theme->slug === 'runway';
    $shape = $isFresh ? 'rounded-full pl-4' : ($isRunway ? '' : 'rounded-shop');
@endphp

<form method="get" action="{{ url()->current() }}" x-data class="flex flex-wrap items-center gap-x-5 gap-y-2">
    @if (request()->filled('q') && is_string(request('q')))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif

    <p class="text-sm text-gray-600">{{ $products->total() }} {{ $products->total() === 1 ? $noun : $nounPlural }}</p>

    @if ($products->total() > 1)
        <label class="flex items-center gap-2 text-sm">
            <span class="text-gray-600">Sort by</span>

            {{-- A short pause before the page reloads, so keyboard users can arrow through the choices --}}
            <select name="sort" x-on:change.debounce.500ms="$el.form.requestSubmit()"
                    class="cursor-pointer border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-gray-900 {{ $shape }}">
                @foreach ($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($current === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <noscript>
            <button type="submit" class="rounded border border-gray-300 px-3 py-2 text-sm font-medium">Apply</button>
        </noscript>
    @endif
</form>