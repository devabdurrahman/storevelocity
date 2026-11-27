<div class="mx-auto max-w-sm py-16 text-center">
    <h2 class="font-display text-xl font-bold">
        {{ $search !== '' ? 'Nothing matches “' . $search . '”' : 'Nothing here yet' }}
    </h2>
    <p class="mt-2 text-gray-600">
        {{ $search !== '' ? 'Check the spelling or try a shorter word.' : 'New products are added regularly. Please check back soon.' }}
    </p>
    @if ($search !== '')
        <a href="{{ route('storefront.products') }}"
           class="mt-5 inline-flex rounded-shop bg-brand px-5 py-2.5 font-semibold text-brand-ink hover:opacity-90">Show all products</a>
    @endif
</div>