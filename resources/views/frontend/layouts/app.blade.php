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
            color: #fff;
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
            color: rgba(255, 255, 255, .85);
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
            transition: all .25s ease
        }

        .category-nav-link:hover, .category-nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border-bottom-color: rgba(255, 255, 255, .9)
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
                <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Menu">
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
                    <a href="{{ route('shop.category', $cat->slug) }}"
                       class="category-nav-link {{ (request()->route('slug') == $cat->slug) ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
                <a href="{{ route('shop.products') }}" class="category-nav-link">
                    <i class="fas fa-th"></i> All Products
                </a>
            </div>
        </div>
    </nav>
</header>

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

    // ─── Mobile menu toggle ─────────────────────────────────────────
    var mobileMenuBtn = document.getElementById('mobileMenuToggle');
    var categoryNav = document.querySelector('.category-nav');
    if (mobileMenuBtn && categoryNav) {
        mobileMenuBtn.addEventListener('click', function () {
            categoryNav.classList.toggle('mobile-open');
            var icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
        // close nav when a link is tapped
        categoryNav.querySelectorAll('.category-nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                categoryNav.classList.remove('mobile-open');
                var icon = mobileMenuBtn.querySelector('i');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            });
        });
    }

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

    // ─── Free Delivery Promo Banner ───────────────────────────────────
    function showFreeDeliveryPromo() {
        var existing = document.getElementById('free-delivery-promo-banner');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var banner = document.createElement('div');
        banner.id = 'free-delivery-promo-banner';
        banner.innerHTML =
            '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">'
            + '<span style="font-size:22px;">🚚</span>'
            + '<div>'
            + '<div style="font-weight:800;font-size:14px;letter-spacing:0.3px;">ফ্রি ডেলিভারি অফার!</div>'
            + '<div style="font-size:12.5px;margin-top:2px;opacity:0.92;">৩টি প্রোডাক্ট অর্ডার করলে ডেলিভারি চার্জ সম্পূর্ণ <strong>ফ্রী</strong>!</div>'
            + '</div>'
            + '<button onclick="this.closest(\'#free-delivery-promo-banner\').remove()" style="margin-left:auto;background:rgba(255,255,255,0.25);border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:14px;color:#fff;line-height:1;flex-shrink:0;">×</button>'
            + '</div>';
        Object.assign(banner.style, {
            position: 'fixed',
            bottom: '80px',
            left: '50%',
            transform: 'translateX(-50%) translateY(20px)',
            zIndex: '99999',
            background: 'linear-gradient(135deg, #015C3C, #027B50)',
            color: '#fff',
            padding: '14px 20px',
            borderRadius: '14px',
            boxShadow: '0 8px 32px rgba(1,92,60,0.35)',
            maxWidth: '420px',
            width: 'calc(100% - 32px)',
            opacity: '0',
            transition: 'opacity 0.4s, transform 0.4s',
            border: '2px solid rgba(255,255,255,0.25)',
        });
        document.body.appendChild(banner);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                banner.style.opacity = '1';
                banner.style.transform = 'translateX(-50%) translateY(0)';
            });
        });
        setTimeout(function () {
            banner.style.opacity = '0';
            banner.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(function () {
                if (banner.parentNode) banner.parentNode.removeChild(banner);
            }, 500);
        }, 6000);
    }

    function showFreeDeliveryUnlocked() {
        var existing = document.getElementById('free-delivery-promo-banner');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var banner = document.createElement('div');
        banner.id = 'free-delivery-promo-banner';
        banner.innerHTML =
            '<div style="display:flex;align-items:center;gap:12px;">'
            + '<span style="font-size:30px;flex-shrink:0;">🎉</span>'
            + '<div>'
            + '<div style="font-weight:800;font-size:15px;letter-spacing:0.3px;">ফ্রি ডেলিভারি আনলক!</div>'
            + '<div style="font-size:13px;margin-top:3px;opacity:0.95;">৩টি প্রোডাক্ট অর্ডার করার জন্য আপনি পাচ্ছেন <strong>ফ্রি ডেলিভারি</strong> 🚚</div>'
            + '</div>'
            + '<button onclick="this.closest(\'#free-delivery-promo-banner\').remove()" style="margin-left:auto;background:rgba(255,255,255,0.25);border:none;border-radius:50%;width:26px;height:26px;cursor:pointer;font-size:15px;color:#fff;line-height:1;flex-shrink:0;">×</button>'
            + '</div>';
        Object.assign(banner.style, {
            position: 'fixed',
            bottom: '80px',
            left: '50%',
            transform: 'translateX(-50%) translateY(20px)',
            zIndex: '99999',
            background: 'linear-gradient(135deg, #015C3C, #027B50)',
            color: '#fff',
            padding: '16px 22px',
            borderRadius: '16px',
            boxShadow: '0 10px 40px rgba(1,92,60,0.45)',
            maxWidth: '440px',
            width: 'calc(100% - 32px)',
            opacity: '0',
            transition: 'opacity 0.4s, transform 0.4s',
            border: '2px solid rgba(255,255,255,0.3)',
        });
        document.body.appendChild(banner);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                banner.style.opacity = '1';
                banner.style.transform = 'translateX(-50%) translateY(0)';
            });
        });
        setTimeout(function () {
            banner.style.opacity = '0';
            banner.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(function () {
                if (banner.parentNode) banner.parentNode.removeChild(banner);
            }, 500);
        }, 7000);
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
                    setTimeout(function () {
                        if (data.cart_qty >= 3) {
                            showFreeDeliveryUnlocked();
                        } else {
                            showFreeDeliveryPromo();
                        }
                    }, 800);
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

<!-- ===== Popup Lead Capture ===== -->
<div id="leadPopupOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px 28px 24px;max-width:420px;width:90%;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.25);">
        <!-- Close button -->
        <button onclick="closeLeadPopup()" aria-label="Close"
                style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:22px;cursor:pointer;line-height:1;color:#888;">&times;</button>

        <!-- Heading -->
        <div style="text-align:center;margin-bottom:18px;">
            <div style="font-size:32px;margin-bottom:6px;">🚀</div>
            <h5 style="margin:0;font-weight:700;color:var(--primary-dark,#015C3C);font-size:1.1rem;">
                দ্রুত ডেলিভারির জন্য ফর্মটি পূরণ করুন
            </h5>
            <p style="margin:6px 0 0;font-size:.85rem;color:#666;">
                For faster delivery, please enter your name, phone &amp; address.
            </p>
        </div>

        <!-- Form -->
        <form id="leadPopupForm">
            @csrf
            <div style="margin-bottom:12px;">
                <input type="text" id="lpName" name="name" placeholder="Your Name *" required
                       style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:.95rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:12px;">
                <input type="tel" id="lpPhone" name="phone" placeholder="Phone Number *" required
                       style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:.95rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:16px;">
                <textarea id="lpAddress" name="address" placeholder="Delivery Address *" required rows="2"
                          style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:.95rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div id="lpMsg" style="display:none;margin-bottom:10px;padding:8px 12px;border-radius:8px;font-size:.88rem;"></div>
            <button type="submit" id="lpBtn"
                    style="width:100%;padding:11px;background:var(--primary-gradient,linear-gradient(135deg,#015C3C,#027B50));color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;letter-spacing:.3px;">
                Submit &rarr;
            </button>
        </form>
        <p style="text-align:center;margin:12px 0 0;font-size:.8rem;color:#aaa;">
            <a href="#" onclick="closeLeadPopup();return false;" style="color:#aaa;">No thanks, skip</a>
        </p>
    </div>
</div>

<script>
(function () {
    var POPUP_KEY   = 'lead_popup_shown';
    var DELAY_MS    = 3000; // 3 seconds

    function showLeadPopup() {
        var overlay = document.getElementById('leadPopupOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLeadPopup() {
        var overlay = document.getElementById('leadPopupOverlay');
        if (overlay) overlay.style.display = 'none';
        document.body.style.overflow = '';
        sessionStorage.setItem(POPUP_KEY, '1');
    }
    window.closeLeadPopup = closeLeadPopup;

    // Show only once per browser session
    if (!sessionStorage.getItem(POPUP_KEY)) {
        setTimeout(showLeadPopup, DELAY_MS);
    }

    // Close on overlay click
    document.getElementById('leadPopupOverlay').addEventListener('click', function (e) {
        if (e.target === this) closeLeadPopup();
    });

    // Form submit
    document.getElementById('leadPopupForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('lpBtn');
        var msg = document.getElementById('lpMsg');
        btn.disabled = true;
        btn.textContent = 'Sending…';

        var formData = new FormData(this);

        fetch('{{ route("shop.popup-lead.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                       'Accept': 'application/json' },
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                msg.style.background = '#d4edda';
                msg.style.color      = '#155724';
                msg.style.border     = '1px solid #c3e6cb';
                msg.textContent      = data.message || 'Thank you!';
                msg.style.display    = 'block';
                sessionStorage.setItem(POPUP_KEY, '1');
                // Save lead data so order forms can be pre-filled
                try {
                    localStorage.setItem('mc_lead', JSON.stringify({
                        name:    document.getElementById('lpName').value.trim(),
                        phone:   document.getElementById('lpPhone').value.trim(),
                        address: document.getElementById('lpAddress').value.trim()
                    }));
                } catch(e) {}
                fillLeadData();
                closeLeadPopup();
            } else {
                msg.style.background = '#f8d7da';
                msg.style.color      = '#721c24';
                msg.style.border     = '1px solid #f5c6cb';
                msg.textContent      = data.message || 'Something went wrong.';
                msg.style.display    = 'block';
                btn.disabled         = false;
                btn.textContent      = 'Submit →';
            }
        })
        .catch(function () {
            msg.style.background = '#f8d7da';
            msg.style.color      = '#721c24';
            msg.style.border     = '1px solid #f5c6cb';
            msg.textContent      = 'Something went wrong. Please try again.';
            msg.style.display    = 'block';
            btn.disabled         = false;
            btn.textContent      = 'Submit →';
        });
    });
})();

// ── Auto-fill order forms from popup lead data ──────────────────────
function fillLeadData() {
    try {
        var lead = JSON.parse(localStorage.getItem('mc_lead') || 'null');
        if (!lead) return;
        function fill(selector, value) {
            var el = document.querySelector(selector);
            if (el && !el.value.trim() && value) el.value = value;
        }
        fill('[name="customer_name"]',    lead.name);
        fill('[name="customer_phone"]',   lead.phone);
        fill('[name="customer_address"]', lead.address);
    } catch(e) {}
}
document.addEventListener('DOMContentLoaded', fillLeadData);
window.fillLeadData = fillLeadData;
</script>
@stack('scripts')
</body>
</html>
