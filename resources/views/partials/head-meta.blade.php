{{-- SEO: title, description, keywords, Open Graph, Twitter, favicon, Google Analytics — lấy từ Cài đặt website --}}
@php
    $__pageTitle = trim($__env->yieldContent('title'));
    // Trang con: "Tiêu đề trang | Tên thương hiệu"; trang chủ dùng Meta Title trong Cài đặt
    $__title = $__pageTitle
        ? (str_contains($__pageTitle, $site->brand()) ? $__pageTitle : $__pageTitle.' | '.$site->brand())
        : $site->get('meta_title', $site->brand());
    $__desc = trim($__env->yieldContent('meta_description')) ?: $site->get('meta_description');
    $__ogTitle = $__pageTitle ?: $site->get('og_title', $__title);
    $__ogDesc = trim($__env->yieldContent('meta_description')) ?: $site->get('og_description', $__desc);
    $__ogImage = trim($__env->yieldContent('og_image')) ?: $site->ogImageUrl();
    $__ogType = trim($__env->yieldContent('og_type')) ?: 'website';
    $__ga = $site->get('google_analytics');
@endphp
<title>{{ $__title }}</title>
@if($__desc)<meta name="description" content="{{ $__desc }}">@endif
@if($site->get('meta_keywords'))<meta name="keywords" content="{{ $site->get('meta_keywords') }}">@endif
@php
    $__robots = trim($__env->yieldContent('robots')) ?: 'index,follow,max-image-preview:large,max-snippet:-1';
    $__page = (int) request()->query('page', 1);
    $__canonical = url()->current().($__page > 1 ? '?page='.$__page : '');
@endphp
<meta name="robots" content="{{ $__robots }}">
<link rel="canonical" href="{{ $__canonical }}">
<meta name="theme-color" content="#b45309">
<meta name="author" content="{{ $site->get('company_name', $site->brand()) }}">
@include('partials.favicon')

<meta property="og:type" content="{{ $__ogType }}">
<meta property="og:site_name" content="{{ $site->brand() }}">
<meta property="og:locale" content="vi_VN">
<meta property="og:url" content="{{ $__canonical }}">
<meta property="og:title" content="{{ $__ogTitle }}">
@if($__ogDesc)<meta property="og:description" content="{{ $__ogDesc }}">@endif
@if($__ogImage)<meta property="og:image" content="{{ $__ogImage }}"><meta property="og:image:alt" content="{{ $__ogTitle }}">@endif
<meta name="twitter:card" content="{{ $__ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $__ogTitle }}">
@if($__ogDesc)<meta name="twitter:description" content="{{ $__ogDesc }}">@endif
@if($__ogImage)<meta name="twitter:image" content="{{ $__ogImage }}">@endif

@stack('head')

{{-- Dữ liệu có cấu trúc (JSON-LD): thông tin tổ chức + website, luôn có trên mọi trang --}}
@php
    $__ld = [
        array_filter([
            '@context' => 'https://schema.org', '@type' => 'Organization',
            'name' => $site->get('company_name', $site->brand()), 'url' => url('/'), 'logo' => $site->logoUrl(),
            'email' => $site->get('email'), 'telephone' => $site->defaultPhone(),
            'address' => $site->get('address') ? ['@type' => 'PostalAddress', 'streetAddress' => $site->get('address'), 'addressCountry' => 'VN'] : null,
            'sameAs' => array_values(array_filter([$site->get('facebook_url'), $site->zaloUrl()])) ?: null,
        ]),
        ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $site->brand(), 'url' => url('/'), 'inLanguage' => 'vi'],
    ];
@endphp
@foreach($__ld as $__item)
<script type="application/ld+json">{!! json_encode($__item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
@stack('jsonld')

@if($__ga)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $__ga }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@js($__ga));</script>
@endif
