<div class="mb-6 flex flex-wrap gap-x-8 gap-y-2 rounded-shop bg-brand-wash px-4 py-3 text-sm text-gray-800">
    <p class="flex items-center gap-2">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
        Cash on delivery
    </p>
    <p class="flex items-center gap-2">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7zM7 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/></svg>
        Delivery: {{ \App\Support\Delivery::summary($store) }}
    </p>
</div>