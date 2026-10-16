@php use Illuminate\Support\Str; use App\Models\User; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Open Se Same - Your Online Fashion Store')</title>
    <meta name="title" content="@yield('title', 'Open Se Same - Your Online Fashion Store')">
    <meta name="description"
          content="@yield('meta_description', 'Shop the latest fashion, accessories & more at Open Se Same. Fast delivery across Bangladesh.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="Open Se Same">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', 'Open Se Same - Your Online Fashion Store')">
    <meta property="og:description"
          content="@yield('og_description', 'Shop the latest fashion, accessories & more at Open Se Same. Fast delivery across Bangladesh.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og.webp'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Open Se Same - Your Online Fashion Store')">
    <meta name="twitter:description"
          content="@yield('og_description', 'Shop the latest fashion, accessories & more at Open Se Same. Fast delivery across Bangladesh.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og.webp'))">

    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">

    <!-- DNS prefetch for remaining CDNs (Font Awesome) -->
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- Preload shop CSS so browser fetches it early even though it's non-blocking -->
    <link rel="preload" href="{{ minified_asset('frontend/css/shop.css') }}" as="style">

    <!-- Google Fonts: fully deferred + display=optional (zero CLS, zero FCP block) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=optional" rel="stylesheet" media="print"
          onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=optional" rel="stylesheet">
    </noscript>

    <!-- Bootstrap 5 (critical — grid/utilities needed for layout, self-hosted) -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome (deferred — icons not needed for initial paint) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print"
          onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </noscript>

    <!-- Swiper (deferred, self-hosted) -->
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    </noscript>

    <!-- Shop CSS: deferred (critical above-fold styles inlined below) -->
    <link rel="stylesheet" href="{{ minified_asset('frontend/css/shop.css') }}" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="{{ minified_asset('frontend/css/shop.css') }}">
    </noscript>

    {{-- ═══ CRITICAL CSS (above-the-fold) ═════════════════════════════════
         Covers header + category nav + hero so the page renders correctly
         before shop.css arrives. Keep in sync with shop.css if those
         selectors ever change.
    ══════════════════════════════════════════════════════════════════════ --}}
    <style>
        :root {
            --primary: #027B50;
            --primary-dark: #015C3C;
            --primary-mid: #039C63;
            --primary-light: #04A871;
            --primary-gradient: linear-gradient(135deg, #015C3C 0%, #027B50 55%, #04A871 100%);
            --primary-gradient-rev: linear-gradient(135deg, #04A871 0%, #027B50 55%, #015C3C 100%);
            --hero-gradient: linear-gradient(135deg, #012B1C 0%, #015C3C 40%, #027B50 75%, #2ECC87 100%);
            --secondary: #FFCF2F;
            --text-dark: #10211B;
            --text-body: #33413C;
            --text-muted: #767676;
            --bg-light: #F3FAF6;
            --bg-white: #fff;
            --border: #D8EDE3;
            --shadow: 0 2px 14px rgba(1, 92, 60, .08);
            --radius: 10px;
            --transition: all .25s ease
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            color: #33413C;
            background: #F3FAF6;
            overflow-x: hidden
        }

        a {
            text-decoration: none;
            color: inherit
        }

        a:visited {
            color: inherit
        }

        img {
            max-width: 100%
        }

        .top-notice-bar {
            background: #015C3C;
            color: rgba(255, 255, 255, .9);
            padding: 7px 0;
            font-size: 12.5px;
            letter-spacing: .2px
        }

        .top-notice-bar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px
        }

        .top-bar-left, .top-bar-right {
            display: flex;
            align-items: center;
            gap: 14px;
            white-space: nowrap
        }

        .top-notice-bar a {
            color: rgba(255, 255, 255, .9)
        }

        .top-notice-bar a:hover {
            color: #FFCF2F
        }

        .top-notice-bar i {
            color: #FFCF2F;
            margin-right: 3px
        }

        .top-bar-sep {
            color: rgba(255, 255, 255, .3)
        }

        .top-bar-social {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .top-bar-social a {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            font-size: 11px
        }

        .top-bar-social a i {
            margin-right: 0;
            color: #fff
        }

        .top-bar-social a:hover {
            background: #FFCF2F
        }

        .top-bar-social a:hover i {
            color: #015C3C
        }

        .main-header {
            background: #fff;
            box-shadow: 0 3px 24px rgba(1, 92, 60, .12);
            z-index: 1000;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(135deg, #015C3C 0%, #027B50 55%, #04A871 100%) 1
        }

        .header-logo {
            width: 175px;
            height: auto;
            aspect-ratio: 175/78;
            max-width: 100%;
            object-fit: contain
        }

        .footer-logo {
            max-height: 50px;
            width: auto
        }

        .search-form .form-control.search-input {
            border: 2px solid #027B50;
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
            box-shadow: none
        }

        .search-form .form-control.search-input:focus {
            border-color: #015C3C;
            box-shadow: none
        }

        .search-form .btn-search {
            background: linear-gradient(135deg, #015C3C 0%, #027B50 55%, #04A871 100%);
            color: #fff;
            border: 2px solid #027B50;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer
        }

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px
        }

        .cart-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #015C3C 0%, #027B50 55%, #04A871 100%);
            color: #fff!important;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            gap: 4px
        }

        .cart-badge {
            background: #fff;
            color: #015C3C;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 2px
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #25d366;
            color: #fff;
            padding: 8px 14px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600
        }

        .mobile-menu-btn {
            background: none;
            border: none;
            color: #015C3C;
            font-size: 20px;
            cursor: pointer;
            padding: 4px 6px
        }

        .category-nav {
            background: linear-gradient(135deg, #012B1C 0%, #015C3C 40%, #027B50 75%, #2ECC87 100%);
            border-top: 1px solid rgba(255, 255, 255, .12)
        }

        .category-nav-inner {
            display: flex;
            align-items: center;
            overflow-x: auto;
            gap: 0;
            scrollbar-width: none;
            padding: 0
        }

        .category-nav-inner::-webkit-scrollbar {
            display: none
        }

        .category-nav-link {
            color: #fff;
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: all .25s ease
        }

        .category-nav-link:hover, .category-nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .15);
            border-bottom-color: #FFCF2F
        }

        /* Browsers give :visited links extra specificity over the base
           `a:visited { color: inherit }` rule, which otherwise makes
           already-visited menu links render in the dark body text color
           (invisible against the green nav background). Force white back. */
        .category-nav-link:visited {
            color: #fff
        }

        .category-nav-item {
            position: relative;
            display: flex;
            align-items: stretch
        }

        .category-nav-caret {
            font-size: 10px;
            margin-left: 4px;
            transition: transform .25s ease
        }

        .category-nav-item:hover .category-nav-caret,
        .category-nav-item:focus-within .category-nav-caret {
            transform: rotate(180deg)
        }

        .category-nav-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 210px;
            padding: 6px 0;
            background: #fff;
            border-radius: 0 0 var(--radius, 10px) var(--radius, 10px);
            box-shadow: 0 14px 30px rgba(1, 92, 60, .2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: opacity .2s ease, transform .2s ease, visibility .2s;
            z-index: 1200
        }

        /* Opens on hover (and on keyboard focus), never on click. */
        .category-nav-item:hover .category-nav-dropdown,
        .category-nav-item:focus-within .category-nav-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0)
        }

        .category-nav-dropdown-link {
            display: block;
            padding: 9px 18px;
            color: #10211B;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap
        }

        .category-nav-dropdown-link:hover,
        .category-nav-dropdown-link.active {
            background: var(--bg-light, #F3FAF6);
            color: var(--primary-dark, #015C3C)
        }

        .category-nav-dropdown-link:visited {
            color: #10211B
        }

        /* An absolutely positioned dropdown is clipped by a scrolling parent,
           so on the desktop strip the categories wrap instead of scrolling. */
        @media (min-width: 768px) {
            .category-nav-inner {
                overflow: visible;
                flex-wrap: wrap
            }
        }

        /* Touch layouts cannot hover: the sub categories are always shown. */
        @media (max-width: 767px) {
            .category-nav-item {
                display: block
            }

            .category-nav-dropdown {
                position: static;
                min-width: 0;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
                background: rgba(255, 255, 255, .07);
                opacity: 1;
                visibility: visible;
                transform: none
            }

            .category-nav-dropdown-link {
                color: #fff;
                padding: 10px 20px 10px 36px
            }

            .category-nav-dropdown-link:hover,
            .category-nav-dropdown-link.active {
                background: rgba(255, 255, 255, .14);
                color: #fff
            }

            .category-nav-dropdown-link:visited {
                color: #fff
            }

            .category-nav-item:last-child .category-nav-link {
                border-bottom: none
            }
        }

        .hero-swiper {
            width: 100%;
            background: linear-gradient(135deg, #012B1C 0%, #015C3C 40%, #027B50 75%, #2ECC87 100%)
        }

        .hero-swiper .swiper-slide {
            height: auto
        }

        .hero-swiper .swiper-slide img {
            width: 100%;
            height: auto;
            display: block
        }

        /* ── Mobile navigation drawer ─────────────────────────────── */
        .mobile-nav-offcanvas {
            max-width: 320px
        }

        .mobile-nav-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .mobile-nav-header .offcanvas-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #fff
        }

        .mobile-nav-body {
            padding: 0;
            display: flex;
            flex-direction: column
        }

        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 600;
            color: #10211B;
            border-bottom: 1px solid #EEF3F0
        }

        .mobile-nav-link:visited {
            color: #10211B
        }

        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: var(--bg-light, #F3FAF6);
            color: var(--primary-dark, #015C3C)
        }

        .mobile-nav-link i {
            width: 18px;
            color: var(--primary, #027B50)
        }

        .mobile-nav-group-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 600;
            color: #10211B;
            background: none;
            border: none;
            border-bottom: 1px solid #EEF3F0;
            text-align: left;
            cursor: pointer
        }

        .mobile-nav-group-title i.mobile-nav-icon {
            width: 18px;
            color: var(--primary, #027B50)
        }

        .mobile-nav-group-label {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .mobile-nav-sub {
            background: #F7FBF9
        }

        .mobile-nav-sub a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 20px 11px 38px;
            font-size: 14px;
            color: #33443E;
            border-bottom: 1px solid #EDF4F0
        }

        .mobile-nav-sub a:visited {
            color: #33443E
        }

        .mobile-nav-sub a:hover, .mobile-nav-sub a.active {
            color: var(--primary-dark, #015C3C);
            background: #EDF7F1
        }

        .mobile-nav-caret {
            font-size: 11px;
            color: #7A8B85;
            transition: transform .25s ease
        }

        .mobile-nav-group-title.open .mobile-nav-caret {
            transform: rotate(180deg)
        }

        /* ── Filter accordion + variation filters ─────────────────── */
        .filter-accordion {
            margin-bottom: 6px
        }

        .filter-category {
            border-bottom: 1px solid #F0F4F2
        }

        .filter-category-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px
        }

        .filter-category-link {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 9px 0;
            font-size: 14px;
            color: #10211B;
            text-decoration: none
        }

        .filter-category-link:visited {
            color: #10211B
        }

        .filter-category-link small, .filter-subcategory-link small {
            color: #94A3A0;
            font-size: 12px
        }

        .filter-category-link.active {
            color: var(--primary-dark, #015C3C);
            font-weight: 700
        }

        .filter-category-toggle {
            background: none;
            border: none;
            padding: 6px 4px;
            color: #7A8B85;
            cursor: pointer
        }

        .filter-category-toggle i {
            transition: transform .25s ease
        }

        .filter-category-toggle.open i {
            transform: rotate(180deg)
        }

        .filter-subcategory-list {
            padding: 2px 0 8px
        }

        .filter-subcategory-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 7px 0 7px 14px;
            font-size: 13.5px;
            color: #4A5A55;
            text-decoration: none;
            border-left: 2px solid #E4EEE9
        }

        .filter-subcategory-link:hover, .filter-subcategory-link.active {
            color: var(--primary-dark, #015C3C);
            border-left-color: var(--primary, #027B50)
        }

        .filter-subcategory-link.active {
            font-weight: 700
        }

        .filter-variation-group {
            border-bottom: 1px solid #F0F4F2
        }

        .filter-variation-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: none;
            border: none;
            padding: 10px 0;
            font-size: 14px;
            font-weight: 600;
            color: #10211B;
            cursor: pointer
        }

        .filter-variation-toggle i {
            font-size: 11px;
            color: #7A8B85;
            transition: transform .25s ease
        }

        .filter-variation-toggle.open i {
            transform: rotate(180deg)
        }

        .filter-variation-values {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding: 0 0 8px
        }

        .filter-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 4px 0;
            font-size: 13.5px;
            color: #37474F;
            cursor: pointer
        }

        .filter-check input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--primary-dark, #015C3C)
        }

        /* ── Category / brand page header ─────────────────────────── */
        .shop-page-header {
            background: #fff;
            padding-bottom: 18px
        }

        .shop-page-banner {
            width: 100%;
            overflow: hidden;
            margin-bottom: 18px
        }

        .shop-page-banner img {
            width: 100%;
            height: auto;
            display: block
        }

        .shop-page-header-inner {
            display: flex;
            align-items: center;
            gap: 16px
        }

        .shop-page-logo {
            width: 74px;
            height: 74px;
            padding: 6px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #E7EFEA;
            border-radius: 10px
        }

        .shop-page-title {
            margin: 0;
            font-size: clamp(20px, 2.6vw, 26px);
            font-weight: 800;
            color: var(--primary-dark, #015C3C)
        }

        .shop-page-desc {
            margin: 4px 0 0;
            font-size: 14px;
            color: #6B7C76
        }

        /* ── Homepage category block banner ───────────────────────── */
        .home-category-banner {
            display: block;
            margin-bottom: 20px;
            border-radius: var(--radius, 10px);
            overflow: hidden
        }

        .home-category-banner img {
            width: 100%;
            height: auto;
            display: block
        }

        /* ── Product detail brand chip ────────────────────────────── */
        .product-brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 5px 12px 5px 5px;
            background: #fff;
            border: 1px solid #E7EFEA;
            border-radius: 30px;
            text-decoration: none
        }

        .product-brand-chip img {
            width: 28px;
            height: 28px;
            object-fit: contain
        }

        .product-brand-chip span {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-dark, #015C3C)
        }
    </style>

    @yield('extra_css')

    {{-- ── Schema.org JSON-LD (sitewide: Organization + WebSite) ───────── --}}
    @php
        use Spatie\SchemaOrg\Schema;
        echo Schema::organization()
            ->name('Open Se Same')
            ->url(url('/'))
            ->logo(Schema::imageObject()->url(asset('images/logo-web.png'))->width(175)->height(78))
            ->telephone('+8801886870406')
            ->contactPoint(
                Schema::contactPoint()
                    ->telephone('+8801886870406')
                    ->contactType('customer service')
                    ->availableLanguage(['Bengali', 'English'])
                    ->areaServed('BD')
            )
            ->toScript();
        echo Schema::webSite()
            ->name('Open Se Same')
            ->url(url('/'))
            ->potentialAction(
                Schema::searchAction()
                    ->target(url('/search?q={search_term_string}'))
                    ->setProperty('query-input', 'required name=search_term_string')
            )
            ->toScript();
    @endphp
    @yield('schema')

@php
    $pixelIp = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
    $pixelUa = request()->userAgent() ?? '';
@endphp
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '26065580893095421', {
    client_ip_address: {!! json_encode($pixelIp) !!},
    client_user_agent: {!! json_encode($pixelUa) !!}
});
@if(!request()->routeIs('quick-order'))
fbq('track', 'PageView');
@endif
</script>
@if(!request()->routeIs('quick-order'))
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=26065580893095421&ev=PageView&noscript=1"
/></noscript>
@endif
<!-- End Meta Pixel Code -->

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-0C0K5XZEQF"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-0C0K5XZEQF');
</script>
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "vqji8l47ab");
</script>
</head>
<body>

<!-- Top Notice Bar -->
<div class="top-notice-bar d-none d-lg-block">
    <div class="container">
        <div class="top-bar-left">
            <a href="tel:+8801886870406"><i class="fas fa-phone-alt" aria-hidden="true"></i> 01886870406</a>
            <span class="top-bar-sep">|</span>
            <span><i class="fas fa-truck" aria-hidden="true"></i> Islandwide Delivery Across Bangladesh</span>
        </div>
        <div class="top-bar-right">
            <div class="top-bar-social">
                <a href="https://www.facebook.com/opensesame1" target="_blank" rel="noopener" aria-label="Follow us on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="https://wa.me/+8801886870406" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                <a href="https://m.me/opensesame1" target="_blank" rel="noopener" aria-label="Message us on Messenger"><i class="fab fa-facebook-messenger" aria-hidden="true"></i></a>
            </div>
            <span class="top-bar-sep">|</span>
            <a href="{{ route('shop.track-order') }}"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Track Order</a>
            <span class="top-bar-sep">|</span>
            @auth
                @if(auth()->user()->hasRole('Registered Customer'))
                    <a href="{{ route('customer.dashboard') }}">
                        @if(auth()->user()->photo)
                            <img src="{{ asset('storage' . \App\Models\User::IMAGE_PATH . auth()->user()->photo) }}"
                                 style="width:16px;height:16px;border-radius:50%;object-fit:cover;vertical-align:-2px;margin-right:4px;"
                                 alt="">
                        @else
                            <i class="fas fa-user-circle" aria-hidden="true"></i>
                        @endif
                        {{ Str::words(auth()->user()->name, 1, '') }}
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"><i class="fas fa-user" aria-hidden="true"></i> Login</a>
                <span class="top-bar-sep">/</span>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="main-header sticky-top">
    <div class="container">
        <div class="row align-items-center py-3">
            <!-- Logo -->
            <div class="col-4 col-md-2 col-lg-2">
                <a href="{{ route('shop.home') }}" class="logo-link">
                    <img src="{{ asset('images/logo-web.png') }}" alt="Open Se Same" class="header-logo" width="175" height="78">
                </a>
            </div>

            <!-- Search Bar (Desktop) -->
            <div class="col-md-8 col-lg-7 d-none d-md-block">
                <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="q" value="{{ request('q') }}"
                               placeholder="Search for products...">
                        <button class="btn btn-search" type="submit" aria-label="Search products">
                            <i class="fas fa-search" aria-hidden="true"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Header Right -->
            <div class="col-8 col-md-2 col-lg-3 text-end">
                <div class="header-actions">
                    <a href="{{ route('shop.cart') }}" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cart-count">{{ count(session('cart', [])) }}</span>
                        <span class="d-none d-lg-inline ms-1">Cart</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Search + Menu Toggle -->
        <div class="row d-md-none pb-2 align-items-center g-2">
            <div class="col-auto">
                <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas"
                        aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="col">
                <form action="{{ route('shop.search') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control search-input" name="q" value="{{ request('q') }}"
                               placeholder="Search products...">
                        <button class="btn btn-search" type="submit" aria-label="Search products">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Navigation -->
    <nav class="category-nav">
        <div class="container">
            <div class="category-nav-inner">
                <a href="{{ route('shop.home') }}" class="category-nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> Home
                </a>
                @foreach($navCategories as $cat)
                    @php
                        $currentSlug = request()->route('slug');
                        $hasChildren = $cat->children->isNotEmpty();
                        $isActive    = $currentSlug == $cat->slug
                                    || ($hasChildren && $cat->children->contains('slug', $currentSlug));
                    @endphp
                    @if($hasChildren)
                        <div class="category-nav-item has-dropdown">
                            <a href="{{ route('shop.category', $cat->slug) }}"
                               class="category-nav-link {{ $isActive ? 'active' : '' }}">
                                {{ $cat->name }}<i class="fas fa-chevron-down category-nav-caret"></i>
                            </a>
                            <div class="category-nav-dropdown">
                                @foreach($cat->children as $child)
                                    <a href="{{ route('shop.category', $child->slug) }}"
                                       class="category-nav-dropdown-link {{ $currentSlug == $child->slug ? 'active' : '' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('shop.category', $cat->slug) }}"
                           class="category-nav-link {{ $isActive ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach
                <a href="{{ route('shop.products') }}" class="category-nav-link">
                    <i class="fas fa-th"></i> All Products
                </a>
            </div>
        </div>
    </nav>
</header>

{{-- ===== Mobile navigation drawer ===== --}}
<div class="offcanvas offcanvas-start mobile-nav-offcanvas" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel">
    <div class="mobile-nav-header">
        <h5 class="offcanvas-title" id="mobileNavOffcanvasLabel">
            <i class="fas fa-bars me-2"></i>Menu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mobile-nav-body">
        <a href="{{ route('shop.home') }}" class="mobile-nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Home
        </a>

        @if($navCategories->isNotEmpty())
            <button class="mobile-nav-group-title" type="button">
                <span class="mobile-nav-group-label"><i class="fas fa-th-large mobile-nav-icon"></i> Categories</span>
                <i class="fas fa-chevron-down mobile-nav-caret" aria-hidden="true"></i>
            </button>
            <div class="mobile-nav-sub" style="display:none;">
                @foreach($navCategories as $navCat)
                    <a href="{{ route('shop.category', $navCat->slug) }}"
                       class="{{ request()->route('slug') === $navCat->slug ? 'active' : '' }}">
                        <span>{{ $navCat->name }}</span>
                    </a>
                    @foreach($navCat->children as $navChild)
                        <a href="{{ route('shop.category', $navChild->slug) }}" style="padding-left:54px;"
                           class="{{ request()->route('slug') === $navChild->slug ? 'active' : '' }}">
                            <span>{{ $navChild->name }}</span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        @endif

        @if(isset($navBrands) && $navBrands->isNotEmpty())
            <button class="mobile-nav-group-title" type="button">
                <span class="mobile-nav-group-label"><i class="fas fa-copyright mobile-nav-icon"></i> Shop by Brand</span>
                <i class="fas fa-chevron-down mobile-nav-caret" aria-hidden="true"></i>
            </button>
            <div class="mobile-nav-sub" style="display:none;">
                @foreach($navBrands as $navBrand)
                    <a href="{{ route('shop.brand', $navBrand->slug) }}"
                       class="{{ request()->routeIs('shop.brand') && request()->route('slug') === $navBrand->slug ? 'active' : '' }}">
                        <span>{{ $navBrand->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <a href="{{ route('shop.products') }}" class="mobile-nav-link {{ request()->routeIs('shop.products') ? 'active' : '' }}">
            <i class="fas fa-th"></i> All Products
        </a>
        <a href="{{ route('shop.offer') }}" class="mobile-nav-link {{ request()->routeIs('shop.offer') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Offers
        </a>
        <a href="{{ route('shop.track-order') }}" class="mobile-nav-link {{ request()->routeIs('shop.track-order') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Track Order
        </a>
        <a href="{{ route('shop.blogs') }}" class="mobile-nav-link {{ request()->routeIs('shop.blogs') ? 'active' : '' }}">
            <i class="fas fa-blog"></i> Blog
        </a>
        <a href="{{ route('shop.contact') }}" class="mobile-nav-link {{ request()->routeIs('shop.contact') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Contact Us
        </a>

        @auth
            @if(auth()->user()->hasRole('Registered Customer'))
                <a href="{{ route('customer.dashboard') }}" class="mobile-nav-link">
                    <i class="fas fa-user-circle"></i> My Account
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="mobile-nav-link"><i class="fas fa-user"></i> Login</a>
            <a href="{{ route('register') }}" class="mobile-nav-link"><i class="fas fa-user-plus"></i> Register</a>
        @endauth
    </div>
</div>

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert-floating alert-success-floating" id="flash-success">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert-floating alert-error-floating" id="flash-error">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    </div>
@endif

<!-- Page Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="main-footer">
    <div class="container">
        <div class="row g-4 footer-top">
            <!-- Brand & Contact -->
            <div class="col-lg-4 col-md-12">
                <img src="{{ asset('images/logo-web-white.png') }}" alt="Open Se Same" class="footer-logo mb-3" width="175" height="71"
                     loading="lazy" onerror="this.style.display='none'">
                <p class="footer-desc">Shop your favourite products at the best prices. Quality products & fast delivery is our promise.</p>
                <ul class="footer-contact-block">
                    <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <span>Dhaka, Bangladesh</span></li>
                    <li><i class="fas fa-headset" aria-hidden="true"></i> <a href="tel:+8801886870406">01886870406</a></li>
                    <li><i class="fas fa-envelope" aria-hidden="true"></i> <a href="mailto:info@opensesame.com">info@opensesame.com</a></li>
                    <li><i class="fas fa-clock" aria-hidden="true"></i> <span>Open: 24/7, 365 Days</span></li>
                </ul>
                <div class="footer-social">
                    <a href="https://www.facebook.com/opensesame1" target="_blank" rel="noopener" aria-label="Follow us on Facebook"><i
                            class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="https://wa.me/+8801886870406" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i
                            class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    <a href="https://m.me/opensesame1" target="_blank" rel="noopener" aria-label="Message us on Messenger"><i
                            class="fab fa-facebook-messenger" aria-hidden="true"></i></a>
                </div>
            </div>

            <!-- Company -->
            <div class="col-lg-2 col-md-4">
                <h4 class="footer-heading">Company</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('shop.home') }}">Home</a></li>
                    <li><a href="{{ route('shop.products') }}">All Products</a></li>
                    <li><a href="{{ route('shop.blogs') }}">Blog</a></li>
                    <li><a href="{{ route('shop.contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-3 col-md-4">
                <h4 class="footer-heading">Support</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('shop.track-order') }}">Track Order</a></li>
                    <li><a href="{{ route('shop.cart') }}">Shopping Cart</a></li>
                    <li><a href="{{ route('shop.checkout') }}">Checkout</a></li>
                    @forelse($footerPages as $fp)
                        <li><a href="{{ route('shop.page', $fp->slug) }}">{{ $fp->title }}</a></li>
                    @empty
                    @endforelse
                    @auth
                        @if(auth()->user()->hasRole('Registered Customer'))
                        <li><a href="{{ route('customer.dashboard') }}">My Account</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}">Login / Register</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-3 col-md-4">
                <h4 class="footer-heading">Categories</h4>
                <ul class="footer-links">
                    @foreach($navCategories->take(6) as $cat)
                        <li><a href="{{ route('shop.category', $cat->slug) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Trust Row -->
        <div class="footer-trust-row">
            <div class="footer-trust-item"><i class="fas fa-truck" aria-hidden="true"></i> Nationwide Delivery</div>
            <div class="footer-trust-item"><i class="fas fa-money-bill-wave" aria-hidden="true"></i> Cash on Delivery</div>
            <div class="footer-trust-item"><i class="fas fa-shield-alt" aria-hidden="true"></i> Secure Checkout</div>
            <div class="footer-trust-item"><i class="fas fa-medal" aria-hidden="true"></i> Authentic Products</div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p class="footer-copyright">© {{ date('Y') }} <strong>Open Se Same</strong>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button class="scroll-top-btn" id="scrollTop" aria-label="Scroll to top">
    <i class="fas fa-chevron-up" aria-hidden="true"></i>
</button>

<!-- Scripts (self-hosted — no CDN round-trip) -->
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>

<script>
    // ─── Cart count badge ───────────────────────────────────────────
    var cartCountEl = document.getElementById('cart-count');

    // ─── Flash message auto-hide ────────────────────────────────────
    setTimeout(function () {
        ['flash-success', 'flash-error'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(function () {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            }, 500);
        });
    }, 4000);

    // ─── Mobile navigation drawer ───────────────────────────────────
    // The drawer itself is a Bootstrap offcanvas; these handlers only expand
    // its collapsible category / brand groups.
    $(document).on('click', '.mobile-nav-group-title', function () {
        var sub = $(this).next('.mobile-nav-sub');
        sub.slideToggle(150);
        $(this).toggleClass('open', sub.is(':visible'));
    });

    // ─── Shop filter accordions (sidebar + mobile drawer) ───────────
    $(document).on('click', '.filter-category-toggle', function () {
        var list = $(this).closest('.filter-category').find('.filter-subcategory-list');
        list.slideToggle(150);
        $(this).toggleClass('open', list.is(':visible'))
            .attr('aria-expanded', list.is(':visible') ? 'true' : 'false');
    });

    $(document).on('click', '.filter-variation-toggle', function () {
        var values = $(this).siblings('.filter-variation-values');
        values.slideToggle(150);
        $(this).toggleClass('open', values.is(':visible'))
            .attr('aria-expanded', values.is(':visible') ? 'true' : 'false');
    });

    // Ticking a variation checkbox re-runs the filter immediately.
    $(document).on('change', '.filter-variation-input', function () {
        if (this.form) {
            this.form.submit();
        }
    });

    // ─── Scroll to top ──────────────────────────────────────────────
    var scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            scrollTopBtn.classList.toggle('visible', window.scrollY > 300);
        });
        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    }

    // ─── Show toast notification ────────────────────────────────────
    function showToast(msg, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'alert-floating alert-' + type + '-floating';
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + msg;
        document.body.appendChild(toast);
        setTimeout(function () {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(function () {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 500);
        }, 3000);
    }

    // ─── Add to cart via AJAX ────────────────────────────────────────
    function addToCart(productId, qty) {
        qty = qty || 1;
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            showToast('Page error, please refresh.', 'error');
            return;
        }
        fetch('{{ route("shop.cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify({product_id: productId, quantity: qty})
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (cartCountEl) cartCountEl.textContent = data.cart_count;
                    showToast('Item added to cart! 🛒', 'success');
                    // ── Meta Pixel: AddToCart ────────────────────────────
                    if (typeof fbq === 'function') {
                        fbq('track', 'AddToCart', {
                            content_ids:  [productId],
                            content_name: data.product_name || '',
                            content_type: 'product',
                            num_items:    qty,
                            value:        Math.round((data.product_price || 0) / 2 * 100) / 100,
                            currency:     'BDT'
                        }, {eventID: data.atc_event_id || ''});
                    }
                } else {
                    showToast(data.message || 'Could not add item.', 'error');
                }
            })
            .catch(function () {
                showToast('Something went wrong, please try again.', 'error');
            });
    }
</script>

@yield('extra_js')

@stack('scripts')
</body>
</html>
