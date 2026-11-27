<div @class(['rounded-shop border border-gray-200 bg-white p-5' => ! ($flat ?? false)])>
    <h2 class="font-display text-lg font-bold">Order summary</h2>

    @if ($showItems ?? false)
        <ul class="mt-4 divide-y divide-gray-100">
            @foreach ($lines as $line)
                <li class="flex items-center gap-3 py-3">
                    <div class="relative shrink-0">
                        <x-shop.photo :product="$line['product']" class="h-14 w-14 rounded-md" />
                        <span class="absolute -right-2 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-900 px-1 text-xs font-semibold tabular-nums text-white">{{ $line['quantity'] }}</span>
                    </div>
                    <p class="min-w-0 flex-1 text-sm leading-snug">{{ $line['name'] }}</p>
                    <p class="shrink-0 text-sm font-medium tabular-nums"><x-shop.money :amount="$line['total']" /></p>
                </li>
            @endforeach
        </ul>
    @endif

    <dl class="mt-4 space-y-2 border-t border-gray-200 pt-4 text-sm">
        <div class="flex justify-between">
            <dt class="text-gray-600">Subtotal</dt>
            <dd class="tabular-nums"><x-shop.money :amount="$subtotal" /></dd>
        </div>

        <div class="flex justify-between gap-4">
            <dt class="text-gray-600">
                Delivery
                @if (! empty($zoneLabel)) 
                <span class="text-gray-500">({{ $zoneLabel }})</span>
                @endif
            </dt>
            <dd class="text-right tabular-nums">
                @if ($shipping === null)
                    <span class="text-gray-600">{{ $deliveryNote ?? 'Chosen at checkout' }}</span>
                @elseif ($shipping == 0)
                    Free
                @else
                    <x-shop.money :amount="$shipping" />
                @endif
            </dd>
        </div>

        @if ($total !== null)
            <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-bold">
                <dt>Total</dt>
                <dd class="tabular-nums"><x-shop.money :amount="$total" /></dd>
            </div>
        @endif
    </dl>
</div>