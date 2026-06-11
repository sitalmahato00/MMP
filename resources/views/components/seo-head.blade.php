{{--
    SEO Head Component
    Usage: <x-seo-head :seo="$seo" />
    $seo is built via App\Services\SeoService::build([...])
--}}
@props(['seo' => []])

@php
    $title       = $seo['title']       ?? config('seo.default_title');
    $description = $seo['description'] ?? config('seo.default_description');
    $keywords    = $seo['keywords']    ?? config('seo.default_keywords');
    $robots      = $seo['robots']      ?? config('seo.default_robots');
    $canonical   = $seo['canonical']   ?? url()->current();

    $ogTitle       = $seo['og_title']       ?? $title;
    $ogDescription = $seo['og_description'] ?? $description;
    $ogType        = $seo['og_type']        ?? 'website';
    $ogImage       = $seo['og_image']       ?? asset('images/seo-og-default.jpg');
    $ogUrl         = $seo['og_url']         ?? $canonical;
    $ogSiteName    = $seo['og_site_name']   ?? config('seo.site_name');
    $ogLocale      = $seo['og_locale']      ?? 'en_US';
    $ogImageWidth  = $seo['og_image_width']  ?? 1200;
    $ogImageHeight = $seo['og_image_height'] ?? 630;

    $twitterCard        = $seo['twitter_card']        ?? 'summary_large_image';
    $twitterSite        = $seo['twitter_site']        ?? config('seo.twitter_site',    '');
    $twitterCreator     = $seo['twitter_creator']     ?? config('seo.twitter_creator', '');
    $twitterTitle       = $seo['twitter_title']       ?? $ogTitle;
    $twitterDescription = $seo['twitter_description'] ?? $ogDescription;
    $twitterImage       = $seo['twitter_image']       ?? $ogImage;

    $googleVerify = $seo['google_verification'] ?? config('seo.google_verification', '');
    $bingVerify   = $seo['bing_verification']   ?? config('seo.bing_verification',   '');

    $author = config('seo.default_author', 'Manmohan Memorial Polytechnic');
@endphp

{{-- ── Primary Meta ──────────────────────────────────────────────── --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="{{ $robots }}">
<meta name="author" content="{{ $author }}">
<meta name="googlebot" content="{{ $robots }}">
<meta name="bingbot" content="{{ $robots }}">
<meta name="revisit-after" content="7 days">
<meta name="rating" content="general">
<meta name="language" content="{{ config('seo.language', 'en') }}">

{{-- ── Canonical ─────────────────────────────────────────────────── --}}
<link rel="canonical" href="{{ $canonical }}">

{{-- ── Open Graph ───────────────────────────────────────────────── --}}
<meta property="og:type"        content="{{ $ogType }}">
<meta property="og:title"       content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url"         content="{{ $ogUrl }}">
<meta property="og:site_name"   content="{{ $ogSiteName }}">
<meta property="og:locale"      content="{{ $ogLocale }}">
<meta property="og:image"             content="{{ $ogImage }}">
<meta property="og:image:width"       content="{{ $ogImageWidth }}">
<meta property="og:image:height"      content="{{ $ogImageHeight }}">
<meta property="og:image:alt"         content="{{ $ogTitle }}">
<meta property="og:image:type"        content="image/jpeg">

{{-- ── Twitter Card ─────────────────────────────────────────────── --}}
<meta name="twitter:card"        content="{{ $twitterCard }}">
<meta name="twitter:title"       content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
<meta name="twitter:image"       content="{{ $twitterImage }}">
<meta name="twitter:image:alt"   content="{{ $twitterTitle }}">
@if($twitterSite)
<meta name="twitter:site"        content="{{ $twitterSite }}">
@endif
@if($twitterCreator)
<meta name="twitter:creator"     content="{{ $twitterCreator }}">
@endif

{{-- ── Search Engine Verification ──────────────────────────────── --}}
@if($googleVerify)
<meta name="google-site-verification" content="{{ $googleVerify }}">
@endif
@if($bingVerify)
<meta name="msvalidate.01" content="{{ $bingVerify }}">
@endif

{{-- ── JSON-LD Structured Data ─────────────────────────────────── --}}
{!! \App\Services\SeoService::jsonLd($seo) !!}
