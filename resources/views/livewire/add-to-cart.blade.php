<div>
    @if ($compact)
        {{-- Product grids: "Add", then a - 1 + stepper --}}
        @if ($inCart > 0)
            <div class="inline-flex items-center rounded-shop border border-gray-300 bg-white">
                <button type="button" wire:click="decrement" aria-label="Remove one"
                        class="flex h-9 w-9 items-center justify-center text-lg hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">−</button>
                <span class="w-6 text-center text-sm font-semibold tabular-nums" aria-live="polite">{{ $inCart }}</span>
                <button type="button" wire:click="increment" aria-label="Add one more" @disabled($inCart >= $stock)
                        class="flex h-9 w-9 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">+</button>
            </div>
        @else
            <button type="button" wire:click="increment" aria-label="Add to cart"
                    class="rounded-shop bg-brand px-4 py-2 text-sm font-semibold text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                Add
            </button>
        @endif
    @else
        {{-- Product page --}}
        <div class="mt-6 flex gap-3">
            <div class="inline-flex shrink-0 items-center rounded-shop border border-gray-300 bg-white">
                <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" aria-label="Decrease quantity" @disabled($quantity <= 1)
                        class="flex h-12 w-11 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">−</button>
                <span class="w-8 text-center font-semibold tabular-nums" aria-live="polite">{{ $quantity }}</span>
                <button type="button" wire:click="$set('quantity', {{ min(99, $stock, $quantity + 1) }})" aria-label="Increase quantity" @disabled($quantity >= $stock)
                        class="flex h-12 w-11 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">+</button>
            </div>

            <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add"
                    class="h-12 flex-1 rounded-shop bg-brand px-6 font-semibold text-brand-ink hover:opacity-90 disabled:opacity-60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                <span wire:loading.remove wire:target="add">Add to cart</span>
                <span wire:loading wire:target="add">Adding...</span>
            </button>
        </div>

        @error('quantity') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

        @if ($added || $inCart > 0)
            <p class="mt-3 text-sm text-green-700">
                {{ $inCart }} in your cart.
                <a href="{{ route('storefront.cart') }}" class="font-semibold underline underline-offset-2">View cart</a>
            </p>
        @endif
    @endif
</div>