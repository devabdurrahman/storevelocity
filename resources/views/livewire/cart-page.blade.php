<div>
    @if ($lines->isEmpty())
        <div class="mx-auto max-w-md py-16 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/>
                </svg>
            </div>
            <h2 class="font-display mt-5 text-xl font-bold">Your cart is empty</h2>
            <p class="mt-2 text-gray-600">Add something you like and it will show up here.</p>
            <a href="{{ route('storefront.products') }}"
               class="mt-6 inline-flex rounded-shop bg-brand px-6 py-3 font-semibold text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                Browse products
            </a>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-12 lg:items-start">
            <ul class="divide-y divide-gray-200 rounded-shop border border-gray-200 bg-white lg:col-span-8">
                @foreach ($lines as $line)
                    @php $product = $line['product']; @endphp

                    <li wire:key="line-{{ $line['key'] }}" class="flex gap-4 p-4">
                        <a href="{{ $product->storefrontUrl() }}" class="shrink-0">
                            <x-shop.photo :product="$product" class="h-20 w-20 rounded-md sm:h-24 sm:w-24" />
                        </a>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ $product->storefrontUrl() }}"
                                   class="font-medium leading-snug hover:underline">{{ $line['name'] }}</a>
                                <p class="shrink-0 font-semibold tabular-nums"><x-shop.money :amount="$line['total']" /></p>
                            </div>

                            <p class="mt-0.5 text-sm text-gray-600"><x-shop.money :amount="$line['unit_price']" /> each</p>

                            @if ($line['quantity'] >= $line['stock'])
                                <p class="mt-1 text-xs font-medium text-amber-700">This is all we have in stock.</p>
                            @endif

                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="inline-flex items-center rounded-shop border border-gray-300">
                                    <button type="button" wire:click="setQuantity('{{ $line['key'] }}', {{ $line['quantity'] - 1 }})"
                                            aria-label="Decrease quantity of {{ $line['name'] }}"
                                            class="flex h-10 w-10 items-center justify-center text-lg hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">−</button>
                                    <span class="w-8 text-center text-sm font-semibold tabular-nums" aria-live="polite">{{ $line['quantity'] }}</span>
                                    <button type="button" wire:click="setQuantity('{{ $line['key'] }}', {{ $line['quantity'] + 1 }})"
                                            aria-label="Increase quantity of {{ $line['name'] }}" @disabled($line['quantity'] >= $line['stock'])
                                            class="flex h-10 w-10 items-center justify-center text-lg hover:bg-gray-50 disabled:opacity-30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">+</button>
                                </div>

                                <button type="button" wire:click="remove('{{ $line['key'] }}')"
                                        class="text-sm text-gray-600 underline underline-offset-2 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="space-y-3 lg:sticky lg:top-24 lg:col-span-4">
                @include('storefront.partials.order-summary')

                <p class="text-sm text-gray-600">Pay with cash when your order arrives.</p>

                <a href="{{ route('storefront.checkout') }}"
                   class="hidden w-full items-center justify-center rounded-shop bg-brand px-6 py-3.5 text-center font-semibold text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 lg:flex">
                    Go to checkout
                </a>
                <a href="{{ route('storefront.products') }}" class="block text-center text-sm text-gray-600 underline underline-offset-2 hover:text-gray-900">
                    Continue shopping
                </a>
            </aside>
        </div>

        {{-- Phones: the total and the checkout button stay in reach --}}
        <div class="h-20 lg:hidden"></div>
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 p-3 backdrop-blur lg:hidden"
             style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
            <div class="mx-auto flex max-w-6xl items-center gap-4">
                <div>
                    <p class="text-xs text-gray-600">{{ $total === null ? 'Subtotal' : 'Total' }}</p>
                    <p class="text-lg font-bold tabular-nums"><x-shop.money :amount="$total ?? $subtotal" /></p>
                </div>
                <a href="{{ route('storefront.checkout') }}"
                   class="flex-1 rounded-shop bg-brand px-6 py-3 text-center font-semibold text-brand-ink hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    Go to checkout
                </a>
            </div>
        </div>
    @endif
</div>