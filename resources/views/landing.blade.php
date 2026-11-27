@php
    $brand = config('app.name');
    $domain = config('app.central_domain');
    $isBn = app()->getLocale() === 'bn';
    $otherLocale = $isBn ? 'en' : 'bn';
    $bnDigits = ['১', '২', '৩', '৪', '৫'];

    $replace = ['brand' => $brand, 'days' => $daysText, 'price' => $priceText, 'domain' => $domain];
    $t = fn (string $key) => __('landing.' . $key, $replace);
    // For text that sits inside a list in the language file
    $fill = fn (string $text) => strtr($text, [':brand' => $brand, ':days' => $daysText, ':price' => $priceText, ':domain' => $domain]);

    $tick = '<svg class="mt-0.5 h-5 w-5 shrink-0 text-teal-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>';

    $tiles = ['bg-teal-100', 'bg-amber-100', 'bg-rose-100', 'bg-sky-100'];

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    $appSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => $brand,
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'url' => route('landing'),
        'offers' => ['@type' => 'Offer', 'price' => (string) $planPrice, 'priceCurrency' => 'BDT'],
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($t('faq'))->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $fill($item['a'])],
        ])->all(),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="motion-safe:scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>{{ $t('meta.title') }}</title>
    <meta name="description" content="{{ $t('meta.description') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('landing') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brand }}">
    <meta property="og:title" content="{{ $t('meta.title') }}">
    <meta property="og:description" content="{{ $t('meta.description') }}">
    <meta property="og:url" content="{{ route('landing') }}">
    <meta name="twitter:card" content="summary_large_image">

    <script type="application/ld+json">{!! json_encode($appSchema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqSchema, $jsonFlags) !!}</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wdth,wght@75..125,400..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --pond: #0A3D3A;
            --wash: #F4F8F7;
            --marigold: #F6B73C;
            --marigold-ink: #2B1D00;
        }

        /* No letter-spacing anywhere: preserve Bengali conjunct letters */
        body { font-family: 'Anek Bangla', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif; }

        .display { font-weight: 800; font-stretch: 110%; }
        .bg-wash { background-color: var(--wash); }
        .bg-pond { background-color: var(--pond); }
        .bg-marigold { background-color: var(--marigold); color: var(--marigold-ink); }

        .saas-grid-pattern {
            background-image: radial-gradient(rgba(15, 118, 110, 0.12) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: no-preference) {
            .rise { animation: rise .5s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: var(--d, 0s); }
        }
    </style>
</head>
<body class="overflow-x-hidden bg-slate-50/50 pb-24 text-slate-900 antialiased selection:bg-teal-100 selection:text-teal-900 md:pb-0">

    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-2.5 focus:shadow-xl focus:ring-2 focus:ring-teal-700">
        Skip to content
    </a>

    {{-- Modern SaaS Sticky Header with Glassmorphism --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md transition-all duration-200">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
            <a href="{{ route('landing') }}" class="group flex items-center gap-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2 rounded-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-600 to-teal-800 text-white shadow-sm shadow-teal-700/20 transition-transform group-hover:scale-105">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m13 2-2 10h8L7 22l2-10H1L13 2z"/>
                    </svg>
                </div>
                <span class="display text-xl font-bold tracking-tight text-slate-900">{{ $brand }}</span>
            </a>

            <nav class="flex items-center gap-2 sm:gap-6 text-sm" aria-label="Main">
                <div class="hidden items-center gap-1 md:flex">
                    <a href="#features" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors">{{ $t('nav.features') }}</a>
                    <a href="#pricing" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors">{{ $t('nav.pricing') }}</a>
                    <a href="#faq" class="rounded-lg px-3 py-1.5 font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-colors">{{ $t('nav.faq') }}</a>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-4">
                    <a href="{{ route('landing', ['lang' => $otherLocale]) }}" lang="{{ $otherLocale }}"
                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20M2 12h20"/>
                        </svg>
                        {{ $t('nav.switch') }}
                    </a>
                    
                    <a href="{{ $loginUrl }}" class="font-semibold text-slate-700 hover:text-teal-700 px-2 py-1.5 transition-colors">{{ $t('nav.login') }}</a>
                    
                    <a href="{{ $registerUrl }}"
                       class="hidden items-center justify-center rounded-xl bg-teal-700 px-4 py-2 font-semibold text-white shadow-sm shadow-teal-700/25 hover:bg-teal-800 active:scale-[0.98] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2 sm:inline-flex">
                        {{ $t('nav.cta') }}
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main id="main">

        {{-- Hero Section --}}
        <section class="relative overflow-hidden border-b border-slate-200/60 bg-gradient-to-b from-white via-teal-50/30 to-slate-50 saas-grid-pattern">
            <div class="absolute -top-24 left-1/2 -z-10 h-96 w-[600px] -translate-x-1/2 rounded-full bg-teal-200/30 blur-3xl"></div>
            
            <div class="mx-auto grid max-w-7xl gap-12 px-4 pb-20 pt-12 sm:px-6 sm:pt-16 lg:grid-cols-12 lg:items-center lg:gap-16 lg:pb-28 lg:pt-20">
                <div class="lg:col-span-6 lg:text-left">
                    {{-- Trial badge pill --}}
                    <div class="inline-flex items-center gap-2 rounded-full border border-teal-200/80 bg-teal-50/80 px-3.5 py-1 text-xs font-semibold text-teal-800 shadow-2xs backdrop-blur-xs">
                        <span class="flex h-2 w-2 rounded-full bg-teal-600 animate-pulse"></span>
                        <span>{{ $daysText }} {{ $isBn ? 'দিনের ফ্রি ট্রায়াল' : 'Days Free Trial' }} • {{ $isBn ? 'কোনো কার্ড প্রয়োজন নেই' : 'No credit card needed' }}</span>
                    </div>

                    <h1 class="display mt-6 text-4xl leading-tight sm:text-5xl sm:leading-[1.2] lg:text-6xl lg:leading-[1.18] text-slate-900">
                        {{ $t('hero.title') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600 sm:text-xl sm:leading-relaxed">
                        {{ $t('hero.subtitle') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ $registerUrl }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-8 py-3.5 text-base font-bold text-white shadow-md shadow-teal-700/30 hover:bg-teal-800 hover:shadow-lg hover:shadow-teal-700/35 active:scale-[0.98] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2">
                            <span>{{ $t('hero.cta') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#features"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300/90 bg-white/80 px-6 py-3.5 text-base font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                            {{ $t('hero.secondary') }}
                        </a>
                    </div>

                    <div class="mt-6 flex items-center gap-2 text-xs font-medium text-slate-500">
                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $t('hero.note') }}</span>
                    </div>
                </div>

                {{-- Interactive Hero Mockup Card --}}
                <div class="lg:col-span-6" aria-hidden="true">
                    <div class="relative mx-auto max-w-lg lg:max-w-none">
                        {{-- Ambient backdrop gradient shadow --}}
                        <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-teal-500/20 to-amber-500/20 blur-xl opacity-75"></div>

                        <div class="relative rounded-2xl border border-slate-200/90 bg-white/95 p-5 sm:p-7 shadow-2xl backdrop-blur-sm">
                            {{-- Window bar --}}
                            <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                                    <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                                    <span class="ml-2 font-mono text-xs text-slate-400">{{ $t('demo.url') }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $t('demo.desk_live') }}
                                </span>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-12 sm:items-start">
                                {{-- Phone storefront frame --}}
                                <div class="sm:col-span-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-3 shadow-inner">
                                    <div class="rounded-xl bg-white p-3 shadow-sm border border-slate-100">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                            <span class="display text-xs font-bold text-teal-800">{{ $t('demo.store') }}</span>
                                            <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-2">
                                            @foreach ($t('demo.products') as $i => $product)
                                                <div class="group rounded-lg border border-slate-100 bg-slate-50/50 p-1.5 text-center">
                                                    <div class="aspect-square rounded-md {{ $tiles[$i % 4] }} transition-transform group-hover:scale-105"></div>
                                                    <p class="mt-1.5 truncate text-[11px] font-semibold text-slate-800">{{ $product['name'] }}</p>
                                                    <p class="text-[10px] font-bold text-teal-700">{{ $product['price'] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Orders live feed --}}
                                <div class="sm:col-span-7">
                                    <div class="flex items-center justify-between pb-2">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $t('demo.desk_title') }}</p>
                                        <span class="text-[11px] font-medium text-slate-500">Real-time sync</span>
                                    </div>

                                    <ul class="space-y-2.5">
                                        @foreach ($t('demo.orders') as $i => $order)
                                            @php
                                                $badgeStyle = match ($order['tone']) {
                                                    'new' => 'bg-amber-100 text-amber-900 border-amber-200',
                                                    'confirmed' => 'bg-teal-50 text-teal-800 border-teal-200',
                                                    'shipped' => 'bg-sky-50 text-sky-800 border-sky-200',
                                                    default => 'bg-slate-100 text-slate-800 border-slate-200',
                                                };
                                            @endphp
                                            <li class="rise flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/60 p-3 shadow-2xs hover:bg-slate-50 transition-colors"
                                                style="--d: {{ number_format(0.2 + $i * 0.3, 1) }}s">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white font-mono text-xs font-bold text-slate-700 shadow-2xs border border-slate-100">
                                                        #{{ $i + 1 }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-xs font-bold text-slate-800">{{ $order['name'] }}</p>
                                                        <p class="text-[11px] font-medium text-slate-500">{{ $order['total'] }}</p>
                                                    </div>
                                                </div>
                                                <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeStyle }}">
                                                    {{ $order['status'] }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Inbox vs Direct Storefront Contrast Section --}}
        <section class="border-b border-slate-200/80 bg-white py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <h2 class="display text-3xl leading-snug sm:text-4xl sm:leading-snug text-slate-900">
                        {{ $t('inbox.title') }}
                    </h2>
                    <p class="mt-4 text-lg leading-relaxed text-slate-600">
                        {{ $t('inbox.text') }}
                    </p>
                </div>

                <div class="mt-14 grid gap-8 lg:grid-cols-12 lg:items-center">
                    {{-- The messy chat experience --}}
                    <div class="lg:col-span-5 rounded-2xl border border-rose-200/80 bg-rose-50/30 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-3 w-3 rounded-full bg-rose-500"></span>
                                <p class="text-xs font-bold uppercase tracking-wider text-rose-800">{{ $t('inbox.inbox_label') }}</p>
                            </div>
                            <span class="text-xs text-rose-600 font-medium">Time-consuming</span>
                        </div>
                        <ul class="mt-5 space-y-3">
                            @foreach ($t('inbox.chat') as $i => $message)
                                <li @class([
                                    'w-fit max-w-[85%] rounded-2xl px-4 py-2.5 text-sm shadow-2xs leading-relaxed',
                                    'rounded-bl-xs bg-white text-slate-800 border border-slate-200/70' => $i % 2 === 0,
                                    'rounded-br-xs bg-slate-800 text-white ml-auto' => $i % 2 === 1,
                                ])>
                                    {{ $message }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Arrow separator for large screens --}}
                    <div class="hidden lg:col-span-2 lg:flex lg:flex-col lg:items-center lg:justify-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-800 shadow-sm border border-teal-200">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </div>
                        <span class="mt-2 text-xs font-bold uppercase text-teal-800">Automated</span>
                    </div>

                    {{-- The polished StoreVelocity storefront checkout solution --}}
                    <div class="lg:col-span-5 rounded-2xl border border-teal-200/80 bg-white p-6 sm:p-8 shadow-xl ring-1 ring-teal-500/10">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-3 w-3 rounded-full bg-teal-600"></span>
                                <p class="text-xs font-bold uppercase tracking-wider text-teal-900">{{ $t('inbox.store_label') }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                Instant Order
                            </span>
                        </div>

                        <div class="mt-5">
                            <div class="mb-4 aspect-[16/9] rounded-xl bg-gradient-to-tr from-teal-100 to-teal-50 flex items-center justify-center text-teal-700">
                                <svg class="h-10 w-10 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                            </div>
                            
                            <h3 class="text-lg font-bold text-slate-900">{{ $t('inbox.product') }}</h3>

                            <div class="mt-2 flex flex-wrap items-baseline gap-2.5">
                                <span class="text-2xl font-extrabold text-slate-900">{{ $t('inbox.price') }}</span>
                                <span class="text-sm font-medium text-slate-400 line-through">{{ $t('inbox.old') }}</span>
                                <span class="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-100">{{ $t('inbox.badge') }}</span>
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">{{ $t('inbox.size') }}</p>
                                <div class="mt-2 flex gap-2">
                                    @foreach ($t('inbox.sizes') as $size)
                                        <span @class([
                                            'flex h-9 min-w-9 items-center justify-center rounded-lg border text-xs font-bold transition-colors',
                                            'border-teal-700 bg-teal-700 text-white shadow-2xs' => $size === 'L',
                                            'border-slate-200 bg-slate-50 text-slate-400 line-through' => $size === $t('inbox.sold_out'),
                                            'border-slate-200 bg-white text-slate-700 hover:border-slate-300' => $size !== 'L' && $size !== $t('inbox.sold_out'),
                                        ])>{{ $size }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <ul class="mt-4 space-y-1.5 border-t border-slate-100 pt-3 text-xs text-slate-600">
                                @foreach ($t('inbox.delivery') as $line)
                                    <li class="flex items-center gap-2">
                                        <svg class="h-4 w-4 shrink-0 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <button type="button" class="mt-5 w-full rounded-xl bg-teal-700 py-3 text-center text-sm font-bold text-white shadow-md shadow-teal-700/20 hover:bg-teal-800 transition-all">
                                {{ $t('inbox.button') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Core Features Section (Grid + Mockups) --}}
        <section id="features" class="scroll-mt-6 bg-wash py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="rounded-full bg-teal-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800">Everything Included</span>
                    <h2 class="display mt-4 text-3xl leading-snug sm:text-4xl sm:leading-snug text-slate-900">
                        {{ $t('features_title') }}
                    </h2>
                </div>

                <div class="mt-16 space-y-16 sm:space-y-24">
                    @foreach ($t('features') as $i => $feature)
                        <div @class([
                            'grid items-center gap-10 lg:grid-cols-12 lg:gap-14 rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-10 shadow-sm transition-shadow hover:shadow-md',
                            'lg:[&>*:first-child]:order-2' => $i % 2 === 1,
                        ])>
                            <div class="lg:col-span-6">
                                <span class="display inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-sm font-bold text-teal-800 border border-teal-100">
                                    0{{ $i + 1 }}
                                </span>
                                <h3 class="display mt-4 text-2xl leading-snug sm:text-3xl text-slate-900">{{ $feature['title'] }}</h3>
                                <p class="mt-3 text-base sm:text-lg leading-relaxed text-slate-600">{{ $feature['text'] }}</p>

                                <ul class="mt-6 space-y-3">
                                    @foreach ($feature['points'] as $point)
                                        <li class="flex items-start gap-3 text-slate-700 leading-relaxed text-sm sm:text-base">
                                            {!! $tick !!}
                                            <span>{{ $point }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="lg:col-span-6 rounded-2xl border border-slate-100 bg-slate-50/70 p-5 sm:p-7 shadow-inner" aria-hidden="true">
                                @switch($i)

                                    @case(0)
                                        {{-- Three themes --}}
                                        @php $themes = $t('mock.themes'); @endphp
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-2xs hover:border-teal-400 transition-colors">
                                                <div class="mb-2 flex items-center gap-1">
                                                    <span class="h-2 w-5 rounded bg-slate-800"></span>
                                                    <span class="h-2 flex-1 rounded bg-slate-200"></span>
                                                    <span class="h-2 w-2 rounded bg-teal-600"></span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <span class="aspect-square rounded bg-teal-100"></span>
                                                    <span class="aspect-square rounded bg-amber-100"></span>
                                                    <span class="aspect-square rounded bg-rose-100"></span>
                                                    <span class="aspect-square rounded bg-sky-100"></span>
                                                </div>
                                                <p class="mt-2 text-center text-xs font-bold text-slate-700">{{ $themes[0] }}</p>
                                            </div>
                                            <div class="rounded-xl border-2 border-teal-600 bg-teal-50/50 p-2.5 shadow-2xs">
                                                <div class="mb-2 flex gap-1">
                                                    <span class="h-2 w-5 rounded-full bg-teal-700"></span>
                                                    <span class="h-2 w-3 rounded-full bg-teal-300"></span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <span class="aspect-square rounded-lg bg-white shadow-2xs"></span>
                                                    <span class="aspect-square rounded-lg bg-white shadow-2xs"></span>
                                                    <span class="aspect-square rounded-lg bg-white shadow-2xs"></span>
                                                    <span class="aspect-square rounded-lg bg-white shadow-2xs"></span>
                                                </div>
                                                <p class="mt-2 text-center text-xs font-bold text-teal-900">{{ $themes[1] }}</p>
                                            </div>
                                            <div class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-2xs hover:border-teal-400 transition-colors">
                                                <span class="mx-auto mb-2 block h-2 w-8 bg-slate-800 rounded"></span>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <span class="aspect-[4/5] rounded bg-rose-100"></span>
                                                    <span class="aspect-[4/5] rounded bg-slate-200"></span>
                                                </div>
                                                <p class="mt-2 text-center text-xs font-bold text-slate-700">{{ $themes[2] }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-5 flex items-center justify-between rounded-xl bg-white p-3 border border-slate-200/80">
                                            <span class="text-xs font-semibold text-slate-600">Store color palette:</span>
                                            <div class="flex items-center gap-1.5">
                                                @foreach (['bg-teal-700', 'bg-rose-600', 'bg-amber-500', 'bg-sky-600', 'bg-violet-600', 'bg-slate-900'] as $swatch)
                                                    <span class="h-5 w-5 rounded-full {{ $swatch }} {{ $loop->first ? 'ring-2 ring-teal-700 ring-offset-2' : '' }}"></span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @break

                                    @case(1)
                                        {{-- Photos and variations --}}
                                        <div class="rounded-xl bg-white p-4 border border-slate-200/80 shadow-2xs">
                                            <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">{{ $t('mock.photos') }}</p>
                                            <div class="mt-2.5 grid grid-cols-5 gap-2">
                                                <span class="col-span-2 row-span-2 aspect-square rounded-lg bg-teal-100 shadow-2xs"></span>
                                                <span class="aspect-square rounded-lg bg-amber-100"></span>
                                                <span class="aspect-square rounded-lg bg-rose-100"></span>
                                                <span class="aspect-square rounded-lg bg-sky-100"></span>
                                                <span class="aspect-square rounded-lg bg-slate-200"></span>
                                                <span class="aspect-square rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 font-bold">+</span>
                                                <span class="aspect-square rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400 font-bold">+</span>
                                            </div>

                                            <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-3">
                                                <div>
                                                    <p class="text-xs font-bold text-slate-700 uppercase">{{ $t('mock.size') }}</p>
                                                    <div class="mt-1.5 flex gap-1">
                                                        @foreach (['S', 'M', 'L', 'XL'] as $size)
                                                            <span class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-xs font-semibold {{ $size === 'M' ? 'border-teal-700 bg-teal-50 text-teal-800' : 'bg-slate-50' }}">{{ $size }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-700 uppercase">{{ $t('mock.colour') }}</p>
                                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                                        @foreach ($t('mock.colours') as $colour)
                                                            <span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium">{{ $colour }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between rounded-lg bg-teal-50/70 p-2.5 border border-teal-100 text-xs">
                                                <span class="font-bold text-teal-900">{{ $t('mock.variations') }}</span>
                                                <span class="font-bold text-teal-700">{{ $t('mock.sale') }}</span>
                                            </div>
                                        </div>
                                    @break

                                    @case(2)
                                        {{-- Order list --}}
                                        @php $head = $t('mock.orders_head'); @endphp
                                        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-2xs">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                                                    <tr>
                                                        <th class="py-2.5 px-3 font-bold">{{ $head[0] }}</th>
                                                        <th class="py-2.5 px-3 font-bold">{{ $head[1] }}</th>
                                                        <th class="py-2.5 px-3 font-bold">{{ $head[2] }}</th>
                                                        <th class="py-2.5 px-3 font-bold">{{ $head[3] }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach ($t('mock.orders') as $row)
                                                        <tr class="hover:bg-slate-50/50">
                                                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $row[0] }} <span class="font-normal text-slate-500">{{ $row[1] }}</span></td>
                                                            <td class="py-2.5 px-3 text-slate-500">{{ $row[2] }}</td>
                                                            <td class="py-2.5 px-3 font-semibold text-slate-900 tabular-nums">{{ $row[3] }}</td>
                                                            <td class="py-2.5 px-3">
                                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $loop->first ? 'bg-amber-100 text-amber-900' : ($loop->last ? 'bg-sky-100 text-sky-900' : 'bg-teal-100 text-teal-900') }}">
                                                                    {{ $row[4] }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3.5 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-xs font-medium text-amber-900">
                                            <svg class="h-4 w-4 shrink-0 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $t('mock.warn') }}</span>
                                        </div>
                                    @break

                                    @case(3)
                                        {{-- Courier tracking card --}}
                                        <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-2xs">
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                                <div>
                                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $t('mock.courier_order') }}</p>
                                                    <p class="font-mono text-base font-bold text-slate-900">{{ $t('mock.tracking_code') }}</p>
                                                </div>
                                                <span class="rounded-lg bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800 border border-sky-200">Steadfast Courier</span>
                                            </div>

                                            @php $steps = $t('mock.steps'); @endphp
                                            <ol class="mt-5 grid grid-cols-3 gap-2">
                                                @foreach ($steps as $k => $step)
                                                    <li class="text-center">
                                                        <span @class([
                                                            'mx-auto flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all',
                                                            'bg-teal-700 text-white shadow-sm shadow-teal-700/20' => $k <= 1,
                                                            'border-2 border-slate-200 text-slate-400 bg-white' => $k > 1,
                                                        ])>{{ $k <= 1 ? '✓' : ($k + 1) }}</span>
                                                        <p class="mt-2 text-xs {{ $k <= 1 ? 'font-bold text-slate-800' : 'text-slate-400' }}">{{ $step }}</p>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @break

                                    @case(4)
                                        {{-- SEO and tracking --}}
                                        <div class="space-y-3">
                                            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    <span>{{ $t('demo.url') }}</span>
                                                </div>
                                                <p class="mt-1 text-sm font-bold text-teal-800">{{ $t('mock.seo_title') }}</p>
                                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-600">{{ $t('mock.seo_desc') }}</p>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2.5">
                                                @foreach ([$t('mock.pixel'), $t('mock.analytics')] as $tool)
                                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-2xs">
                                                        <span class="text-xs font-bold text-slate-800">{{ $tool }}</span>
                                                        <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Active
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @break

                                    @case(5)
                                        {{-- Team Roles --}}
                                        @php $roleHead = $t('mock.roles_head'); @endphp
                                        <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-2xs">
                                            <table class="w-full text-xs">
                                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                                                    <tr>
                                                        <th class="py-2 px-3 text-left font-bold">&nbsp;</th>
                                                        @foreach ($roleHead as $role)
                                                            <th class="py-2 px-3 text-center font-bold">{{ $role }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach ($t('mock.roles') as $row)
                                                        <tr>
                                                            <th class="py-2.5 px-3 text-left font-semibold text-slate-800">{{ $row[0] }}</th>
                                                            @foreach ([1, 2, 3] as $col)
                                                                <td class="py-2.5 px-3 text-center">
                                                                    @if ($row[$col])
                                                                        <span class="font-bold text-teal-700 text-sm">✓</span>
                                                                    @else
                                                                        <span class="text-slate-300">–</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @break

                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- All other features list --}}
                <div class="mt-20 rounded-3xl border border-slate-200/80 bg-white p-8 sm:p-12 shadow-sm">
                    <h3 class="display text-2xl leading-snug sm:text-3xl text-slate-900">{{ $t('more.title') }}</h3>
                    <ul class="mt-8 grid gap-x-10 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($t('more.items') as $item)
                            <li class="flex items-start gap-3 text-sm sm:text-base text-slate-700 leading-relaxed">
                                {!! $tick !!}
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        {{-- 3-Step Setup Process --}}
        <section class="border-b border-slate-200/80 bg-white py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="rounded-full bg-teal-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800">Easy Onboarding</span>
                    <h2 class="display mt-4 text-3xl leading-snug sm:text-4xl text-slate-900">
                        {{ $t('steps_title') }}
                    </h2>
                </div>

                <ol class="mt-16 grid gap-8 md:grid-cols-3 md:gap-8">
                    @foreach ($t('steps') as $i => $step)
                        <li class="relative rounded-2xl border border-slate-200/80 bg-slate-50/50 p-6 sm:p-8 hover:bg-slate-50 hover:shadow-md transition-all">
                            <span class="display flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-600 to-teal-800 text-lg font-bold text-white shadow-md shadow-teal-700/20">
                                {{ $isBn ? $bnDigits[$i] : $i + 1 }}
                            </span>

                            <h3 class="mt-5 text-xl font-bold leading-snug text-slate-900">{{ $step['title'] }}</h3>
                            <p class="mt-2.5 text-slate-600 leading-relaxed text-sm sm:text-base">{{ $step['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Pricing Section --}}
        <section id="pricing" class="scroll-mt-6 bg-wash py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="rounded-full bg-teal-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800">Simple Transparent Pricing</span>
                    <h2 class="display mt-4 text-3xl leading-snug sm:text-4xl text-slate-900">
                        {{ $t('pricing.title') }}
                    </h2>
                    <p class="mt-3 text-lg leading-relaxed text-slate-600">
                        {{ $t('pricing.text') }}
                    </p>
                </div>

                <div class="mx-auto mt-14 max-w-4xl grid gap-8 md:grid-cols-2">
                    {{-- Free trial tier card --}}
                    <div class="relative flex flex-col justify-between rounded-3xl border-2 border-teal-700 bg-white p-7 sm:p-9 shadow-xl ring-4 ring-teal-600/10">
                        <span class="absolute -top-3.5 left-8 rounded-full bg-marigold px-4 py-1 text-xs font-extrabold uppercase tracking-wider shadow-sm">
                            {{ $t('pricing.trial_tag') }}
                        </span>

                        <div>
                            <p class="text-xl font-bold text-slate-900">{{ $t('pricing.trial_name') }}</p>
                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="display text-5xl font-black text-slate-900">{{ $t('pricing.trial_price') }}</span>
                                <span class="text-sm font-semibold text-slate-500">/ {{ $t('pricing.trial_period') }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $t('pricing.trial_text') }}</p>
                        </div>

                        <div class="mt-8 border-t border-slate-100 pt-6">
                            <a href="{{ $registerUrl }}"
                               class="flex w-full items-center justify-center rounded-xl bg-teal-700 py-3.5 text-base font-bold text-white shadow-md shadow-teal-700/25 hover:bg-teal-800 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2">
                                {{ $t('pricing.trial_cta') }}
                            </a>
                        </div>
                    </div>

                    {{-- Regular monthly tier card --}}
                    <div class="flex flex-col justify-between rounded-3xl border border-slate-200/90 bg-white p-7 sm:p-9 shadow-sm hover:shadow-md transition-shadow">
                        <div>
                            <p class="text-xl font-bold text-slate-900">{{ $t('pricing.monthly_name') }}</p>
                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="display text-5xl font-black text-slate-900">{{ $t('pricing.monthly_price') }}</span>
                                <span class="text-sm font-semibold text-slate-500">/ {{ $t('pricing.monthly_period') }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $t('pricing.monthly_text') }}</p>
                        </div>

                        <div class="mt-8 border-t border-slate-100 pt-6">
                            <p class="text-center text-xs font-semibold text-slate-500">{{ $t('pricing.monthly_note') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Included features --}}
                <div class="mx-auto mt-16 max-w-4xl rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-slate-900">{{ $t('pricing.included_title') }}</h3>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($t('pricing.included') as $item)
                            <li class="flex items-center gap-2.5 text-sm text-slate-700">
                                {!! $tick !!}
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $t('soon.title') }}</h4>
                        <ul class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-xs font-medium text-slate-500">
                            @foreach ($t('soon.items') as $item)
                                <li class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ Section --}}
        <section id="faq" class="scroll-mt-6 border-b border-slate-200/80 bg-white py-20 sm:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="rounded-full bg-teal-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-teal-800">FAQ</span>
                    <h2 class="display mt-4 text-3xl leading-snug sm:text-4xl text-slate-900">
                        {{ $t('faq_title') }}
                    </h2>
                </div>

                <div class="mt-12 divide-y divide-slate-200/80 rounded-2xl border border-slate-200/80 bg-slate-50/40 p-4 sm:p-6">
                    @foreach ($t('faq') as $item)
                        <details class="group py-4 first:pt-2 last:pb-2">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-base sm:text-lg font-bold text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 rounded-lg">
                                <span>{{ $item['q'] }}</span>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 transition-transform duration-200 group-open:rotate-180">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </summary>
                            <p class="mt-3 leading-relaxed text-slate-600 text-sm sm:text-base">{{ $fill($item['a']) }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Final CTA Section --}}
        <section class="relative overflow-hidden bg-pond py-20 sm:py-28 text-white">
            <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-teal-500/10 blur-3xl"></div>
            
            <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1 text-xs font-semibold text-teal-200 border border-white/10 backdrop-blur-xs">
                        Ready to accelerate your store?
                    </span>
                    <h2 class="display mt-4 text-3xl leading-tight sm:text-5xl text-white">{{ $t('final.title') }}</h2>
                    <p class="mt-4 text-base sm:text-lg text-teal-100/90 leading-relaxed">{{ $t('final.text') }}</p>
                </div>

                <div class="shrink-0">
                    <a href="{{ $registerUrl }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-teal-950 shadow-xl hover:bg-teal-50 active:scale-[0.98] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                        <span>{{ $t('final.cta') }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- Modern Footer --}}
    <footer class="border-t border-slate-200 bg-white py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8 text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-teal-700 text-white font-bold text-xs">
                    ⚡
                </div>
                <span>© {{ date('Y') }} {{ $brand }}. {{ $t('footer.rights') }}</span>
            </div>

            <nav class="flex flex-wrap items-center gap-6" aria-label="Footer">
                <a href="#features" class="hover:text-slate-900 transition-colors">{{ $t('nav.features') }}</a>
                <a href="#pricing" class="hover:text-slate-900 transition-colors">{{ $t('nav.pricing') }}</a>
                <a href="#faq" class="hover:text-slate-900 transition-colors">{{ $t('nav.faq') }}</a>
                <a href="{{ $loginUrl }}" class="hover:text-slate-900 transition-colors">{{ $t('nav.login') }}</a>
            </nav>
        </div>
    </footer>

    {{-- Mobile Floating Bottom CTA Bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/90 bg-white/95 p-3.5 backdrop-blur-md md:hidden"
         style="padding-bottom: calc(0.85rem + env(safe-area-inset-bottom));">
        <a href="{{ $registerUrl }}"
           class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-700 py-3 text-base font-bold text-white shadow-md shadow-teal-700/25 active:scale-[0.98] transition-all">
            <span>{{ $t('nav.cta') }}</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
        </a>
    </div>

</body>
</html>
