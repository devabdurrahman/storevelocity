<div class="mt-6">
    @foreach ([1 => $options1, 2 => $options2] as $n => $options)
        @continue(empty($options))

        @php
            $name = $n === 1 ? $product->option1_name : $product->option2_name;
            $selected = $n === 1 ? $selected1 : $selected2;
        @endphp

        <fieldset class="mb-5">
            <legend class="text-sm font-medium">
                {{ $name }}@if ($selected)<span class="font-normal text-gray-600">: {{ $selected }}</span>@endif
            </legend>

            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($options as $index => $option)
                    <button type="button" wire:click="choose({{ $n }}, {{ $index }})" wire:key="opt-{{ $n }}-{{ $index }}"
                            aria-pressed="{{ $option['active'] ? 'true' : 'false' }}"
                            @class([
                                'min-w-12 rounded-shop border px-4 py-2.5 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900',
                                'border-gray-900 bg-gray-900 text-white' => $option['active'],
                                'border-gray-300 bg-white hover:border-gray-900' => ! $option['active'] && $option['available'],
                                'border-gray-200 bg-gray-50 text-gray-400 line-through' => ! $option['active'] && ! $option['available'],
                            ])>
                        {{ $option['value'] }}
                    </button>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    @if ($variant)
        <div class="mb-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <span class="text-2xl font-bold tabular-nums"><x-shop.money :amount="$price" /></span>

            @if ($variant->stock <= 0)
                <span class="text-sm font-medium text-gray-500">Out of stock</span>
            @elseif ($variant->stock <= 5)
                <span class="text-sm font-medium text-amber-700">Only {{ $variant->stock }} left</span>
            @else
                <span class="text-sm font-medium text-green-700">In stock</span>
            @endif
        </div>
    @endif

    @php
        $canAdd = $variant && $variant->stock > 0;
        $max = $variant ? max(1, min(99, $variant->stock)) : 1;
    @endphp

    <div class="flex gap-3">
        <div class="inline-flex shrink-0 items-center rounded-shop border border-gray-300 bg-white">
            <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" aria-label="Decrease quantity"
                    @disabled($quantity <= 1 || ! $canAdd)
                    class="flex h-12 w-11 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">−</button>
            <span class="w-8 text-center font-semibold tabular-nums" aria-live="polite">{{ $quantity }}</span>
            <button type="button" wire:click="$set('quantity', {{ min($max, $quantity + 1) }})" aria-label="Increase quantity"
                    @disabled(! $canAdd || $quantity >= $max)
                    class="flex h-12 w-11 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">+</button>
        </div>

        <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add" @disabled(! $canAdd)
                class="h-12 flex-1 rounded-shop bg-brand px-6 font-semibold text-brand-ink hover:opacity-90 disabled:opacity-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
            <span wire:loading.remove wire:target="add">
                {{ $canAdd ? 'Add to cart' : ($variant ? 'Out of stock' : 'Choose ' . strtolower((string) $missing)) }}
            </span>
            <span wire:loading wire:target="add">Adding...</span>
        </button>
    </div>

    @error('selection') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    @error('quantity') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror

    @if ($inCart > 0)
        <p class="mt-3 text-sm text-green-700">
            {{ $inCart }} in your cart.
            <a href="{{ route('storefront.cart') }}" class="font-semibold underline underline-offset-2">View cart</a>
        </p>
    @endif
</div>