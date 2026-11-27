@php
    $pageTitle = $seo['title'] ?? ($__env->yieldContent('title') ?: $store->name);
    $pageDescription = $seo['description'] ?? ($__env->yieldContent('description') ?: $siteSeo->homeDescription());
    $pageImage = $seo['image'] ?? $siteSeo->defaultImageUrl();
    $pageRobots = $siteSeo->robots($seo['robots'] ?? ($__env->yieldContent('robots') ?: null));
@endphp

<meta name="robots" content="{{ $pageRobots }}">
<meta name="description" content="{{ $pageDescription }}">

@if (! empty($seo['canonical']))
    <link rel="canonical" href="{{ $seo['canonical'] }}">
@endif

<meta property="og:site_name" content="{{ $store->name }}">
<meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
@if (! empty($seo['canonical']))
    <meta property="og:url" content="{{ $seo['canonical'] }}">
@endif
@if ($pageImage)
    <meta property="og:image" content="{{ $pageImage }}">
@endif

<meta name="twitter:card" content="{{ $pageImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
@if ($pageImage)
    <meta name="twitter:image" content="{{ $pageImage }}">
@endif

@if ($siteSeo->googleVerification())
    <meta name="google-site-verification" content="{{ $siteSeo->googleVerification() }}">
@endif
@if ($siteSeo->bingVerification())
    <meta name="msvalidate.01" content="{{ $siteSeo->bingVerification() }}">
@endif

@foreach (($seo['jsonld'] ?? []) as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endforeach