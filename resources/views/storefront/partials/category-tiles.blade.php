<section class="mb-12">
    <h2 class="font-display mb-4 text-xl font-bold leading-snug sm:text-2xl">Shop by category</h2>

    @switch($theme->slug)
        @case('fresh')
            {{-- Round tiles, three to a row on phones, wrapping instead of scrolling --}}
            <div class="flex flex-wrap justify-center gap-x-3 gap-y-5">
                @foreach ($featuredCategories as $featured)
                    @php $cover = $featured->coverUrl(); @endphp

                    <a href="{{ route('storefront.category', ['slug' => $featured->slug]) }}"
                       class="group block basis-[calc((100%_-_1.5rem)/3)] text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 sm:basis-[calc((100%_-_2.25rem)/4)] md:basis-[calc((100%_-_3.75rem)/6)]">
                        <div class="mx-auto flex aspect-square w-full max-w-28 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-white">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <span class="font-display flex h-full w-full items-center justify-center bg-brand-wash text-2xl font-bold text-gray-700">{{ mb_strtoupper(mb_substr($featured->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <p class="mt-2 line-clamp-2 text-xs font-medium leading-snug group-hover:underline sm:text-sm">{{ $featured->name }}</p>
                    </a>
                @endforeach
            </div>
            @break

        @case('runway')
            {{-- Up to three tall tiles, always side by side --}}
            <div class="grid grid-cols-3 gap-3 md:gap-5">
                @foreach ($featuredCategories->take(3) as $featured)
                    @php $cover = $featured->coverUrl(); @endphp

                    <a href="{{ route('storefront.category', ['slug' => $featured->slug]) }}"
                       class="group block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gray-900">
                        <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="" loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <span class="font-display flex h-full w-full items-center justify-center text-4xl font-bold text-gray-400">{{ mb_strtoupper(mb_substr($featured->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <p class="font-display mt-3 line-clamp-2 text-base font-bold leading-snug group-hover:underline sm:text-xl">{{ $featured->name }}</p>
                    </a>
                @endforeach
            </div>
            @break

        @default
            {{-- Square tiles, three to a row on phones, six on desktops --}}
            <div class="flex flex-wrap justify-center gap-3">
                @foreach ($featuredCategories as $featured)
                    @php $cover = $featured->coverUrl(); @endphp

                    <a href="{{ route('storefront.category', ['slug' => $featured->slug]) }}"
                       class="group block basis-[calc((100%_-_1.5rem)/3)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 lg:basis-[calc((100%_-_3.75rem)/6)]">
                        <div class="aspect-square overflow-hidden rounded-shop border border-gray-200 bg-gray-100">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="" loading="lazy"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <span class="font-display flex h-full w-full items-center justify-center text-3xl font-bold text-gray-400">{{ mb_strtoupper(mb_substr($featured->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <p class="mt-2 line-clamp-2 text-sm font-medium leading-snug group-hover:underline">{{ $featured->name }}</p>
                    </a>
                @endforeach
            </div>
    @endswitch
</section>