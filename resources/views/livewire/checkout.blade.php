@php
    $field = 'mt-1.5 w-full rounded-shop border bg-white px-3.5 py-3 text-base placeholder:text-gray-400 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-gray-900';
@endphp

<div class="grid gap-8 lg:grid-cols-12 lg:items-start">
    {{-- Phones: collapsible summary at the top --}}
    <details class="rounded-shop border border-gray-200 bg-white lg:hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 font-medium [&::-webkit-details-marker]:hidden">
            <span>Order summary</span>
            <span class="font-bold tabular-nums"><x-shop.money :amount="$total ?? $subtotal" /></span>
        </summary>
        <div class="border-t border-gray-200 p-4">
            @include('storefront.partials.order-summary', ['showItems' => true, 'flat' => true])
        </div>
    </details>

    <form wire:submit="place" class="space-y-5 lg:col-span-7">
        {{-- Honeypot: real customers never see or fill this --}}
        <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label>Leave this empty</label>
            <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
        </div>

        @error('cart')
            <div role="alert" class="rounded-shop border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ $message }}
                <a href="{{ route('storefront.cart') }}" class="font-semibold underline">Back to cart</a>
            </div>
        @enderror

        <section class="rounded-shop border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-display text-lg font-bold">Your details</h2>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="co-name" class="block text-sm font-medium">Full name</label>
                    <input id="co-name" type="text" wire:model.blur="name" autocomplete="name"
                           class="{{ $field }} {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
                    @error('name') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="co-phone" class="block text-sm font-medium">Mobile number</label>
                    <input id="co-phone" type="tel" inputmode="tel" wire:model.blur="phone" autocomplete="tel" placeholder="01712345678"
                           class="{{ $field }} {{ $errors->has('phone') ? 'border-red-500' : 'border-gray-300' }}">
                    @error('phone')
                        <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                    @else
                        <p class="mt-1.5 text-sm text-gray-600">The store will call this number to confirm your order.</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-shop border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-display text-lg font-bold">Delivery area</h2>

            @if (count($zoneOptions) === 1)
                @php $only = collect($zoneOptions)->first(); @endphp

                <div class="mt-4 flex items-center justify-between gap-3 rounded-shop border border-gray-900 bg-gray-50 p-4">
                    <div>
                        <p class="font-medium">{{ $only['label'] }}</p>
                        <p class="text-sm text-gray-600">This store delivers {{ strtolower($only['label']) }} only.</p>
                    </div>
                    <p class="shrink-0 font-semibold tabular-nums">
                        @if ($only['charge'] == 0) Free @else <x-shop.money :amount="$only['charge']" /> @endif
                    </p>
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($zoneOptions as $key => $option)
                        <label class="flex cursor-pointer items-start justify-between gap-3 rounded-shop border border-gray-300 p-4 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                            <span class="flex items-start gap-3">
                                <input type="radio" wire:model.live="deliveryZone" value="{{ $key }}" class="mt-1 accent-brand">
                                <span>
                                    <span class="block font-medium">{{ $option['label'] }}</span>
                                    @if ($option['free_over'] !== null)
                                        <span class="block text-sm text-gray-600">Free on orders of <x-shop.money :amount="$option['free_over']" /> or more</span>
                                    @endif
                                </span>
                            </span>
                            <span class="shrink-0 font-semibold tabular-nums">
                                @if ($option['charge'] == 0) Free @else <x-shop.money :amount="$option['charge']" /> @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('deliveryZone') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            @endif

            <div class="mt-5 space-y-4">
                <div>
                    <label for="co-address" class="block text-sm font-medium">Full address</label>
                    <textarea id="co-address" rows="3" wire:model.blur="address" autocomplete="street-address"
                              placeholder="House, road, area, thana, district"
                              class="{{ $field }} {{ $errors->has('address') ? 'border-red-500' : 'border-gray-300' }}"></textarea>
                    @error('address') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="co-note" class="block text-sm font-medium">Note for the store <span class="font-normal text-gray-500">(optional)</span></label>
                    <textarea id="co-note" rows="2" wire:model.blur="note"
                              class="{{ $field }} {{ $errors->has('note') ? 'border-red-500' : 'border-gray-300' }}"></textarea>
                    @error('note') <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-shop border border-gray-200 bg-white p-5 sm:p-6">
            <h2 class="font-display text-lg font-bold">Payment</h2>

            <div class="mt-4 rounded-shop border border-gray-900 bg-gray-50 p-4">
                <p class="font-medium">Cash on delivery</p>
                <p class="text-sm text-gray-600">Pay in cash when your order arrives.</p>
            </div>
        </section>

        <div>
            <button type="submit" wire:loading.attr="disabled"
                    class="flex w-full items-center justify-center rounded-shop bg-brand px-6 py-4 text-base font-semibold text-brand-ink hover:opacity-90 disabled:opacity-60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                <span wire:loading.remove wire:target="place">
                    Place order
                    @if ($total !== null), <x-shop.money :amount="$total" /> @endif
                </span>
                <span wire:loading wire:target="place">Placing your order...</span>
            </button>
            <p class="mt-3 text-center text-sm text-gray-600">The store will contact you to confirm your order.</p>
        </div>
    </form>

    <aside class="hidden lg:col-span-5 lg:block lg:sticky lg:top-24">
        @include('storefront.partials.order-summary', ['showItems' => true])
    </aside>
</div>