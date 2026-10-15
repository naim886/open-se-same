@extends('frontend.layouts.app')

@section('title', 'Open Se Same | Best treasures  & miniature car in Bangladesh')
@section('meta_description', 'Shop the Unlock hidden treasures& hot miniature car at Open Se Same. Real roses from ৳499, sexy miniature car from ৳499. Fast delivery across Bangladesh.')
@section('og_title', 'Open Se Same | Best treasures  & miniature car in Bangladesh')
@section('og_description', 'Shop the Unlock hidden treasures & hot miniature car at Open Se Same. Real roses from ৳499, sexy miniature car from ৳499. Fast delivery across Bangladesh.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::webPage()
    ->name('Open Se Same | Best treasures  & miniature car in Bangladesh')
    ->description('Shop the Unlock hidden treasures & hot miniature car at Open Se Same. Real roses from ৳499, sexy miniature car from ৳499. Fast delivery across Bangladesh.')
    ->url(route('shop.home'))
    ->breadcrumb(Schema::breadcrumbList()->itemListElement([
        Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    ]))
    ->toScript();
if (isset($featuredProducts) && $featuredProducts->count() > 0) {
    $schemaListItems = $featuredProducts->take(10)->values()->map(fn($p, $i) =>
        Schema::listItem()
            ->position($i + 1)
            ->item(
                Schema::product()
                    ->name($p->name)
                    ->url(route('shop.product', $p->slug))
                    ->image($p->primary_photo ? asset('storage/'.$p->primary_photo) : null)
                    ->offers(
                        Schema::offer()
                            ->price((float)($p->sale_price ?? $p->price ?? 0))
                            ->priceCurrency('BDT')
                    )
            )
    )->toArray();
    echo Schema::itemList()
        ->name('Top Selling Products at Open Se Same')
        ->itemListElement($schemaListItems)
        ->toScript();
}
@endphp
@endsection

@section('extra_css')
@if(isset($banners) && $banners->count() > 0 && $banners->first()->image)
<link rel="preload" as="image" href="{{ asset('storage/'.$banners->first()->image) }}" fetchpriority="high">
@endif
@endsection

@section('content')

<!-- Hero Slider -->
<section class="hero-section">
    @if($banners->count() > 0)
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @foreach($banners as $banner)
                    <div class="swiper-slide">
                        @if($banner->button_url)<a href="{{ $banner->button_url }}">@endif
                        <img src="{{ asset('storage/'.$banner->image) }}" alt="{{ $banner->title }}" @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
                        @if($banner->button_url)</a>@endif
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    @else
        <div class="hero-swiper" style="background: var(--primary-gradient); display:flex; align-items:center; justify-content:center; color:#fff; text-align:center;">
            <div>
                <h1 style="font-size:32px; font-weight:800;">Welcome to Open Se Same</h1>
                <p style="font-size:16px; opacity:0.85; margin-top:10px;">Quality products at the best prices</p>
                <a href="{{ route('shop.products') }}" class="btn-buy-now mt-4 d-inline-block">Shop Now</a>
            </div>
        </div>
    @endif
</section>

{{-- SEO H1 (visually subtle, full semantic weight) --}}
<div class="container" style="padding-top:20px; padding-bottom:4px;">
    <h1 style="font-size:clamp(22px, 3vw, 27px); font-weight:700; color:var(--primary-dark); text-align:center; letter-spacing:-0.3px; line-height:1.4; margin:0;">
        Unlock hidden treasures &amp; Premium miniature car &mdash; Open Se Same
    </h1>
</div>

<!-- Top Selling Products -->
@if(isset($topSellingProducts) && $topSellingProducts->count() > 0)
<section class="top-selling-strip">
    <div class="container">
        <div class="top-selling-header">
            <div class="top-selling-label"><i class="fas fa-fire me-1"></i> Top Selling</div>
            <a href="{{ route('shop.products') }}" class="section-view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 g-3">
            @foreach($topSellingProducts->take(10) as $product)
                @include('frontend.components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Shop by Category -->
@if($categories->count() > 0)
<section class="category-section">
    <div class="container">
        <div class="section-title-block text-center mb-4">
            <span class="section-subtitle-tag">Explore</span>
            <h2 class="section-title-main">Shop by <span>Category</span></h2>
            <p class="section-tagline">Discover our curated collections just for you</p>
        </div>
        <div class="category-grid">
            @foreach($categories as $category)
                <a href="{{ route('shop.category', $category->slug) }}" class="cat-card-new">
                    <div class="cat-card-inner">
                        @if($category->getRawOriginal('icon'))
                            <img src="{{ $category->icon }}" alt="{{ $category->name }}">
                        @else
                            <div class="cat-card-no-img">
                                <i class="fas fa-tag"></i>
                            </div>
                        @endif
                        <div class="cat-card-overlay">
                            <span class="cat-card-name-new">{{ $category->name }}</span>
                            <span class="cat-card-shop-link">Shop Now <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('shop.products') }}" class="btn-view-all-cats">Browse All Products <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>
@endif

<!-- Featured Products -->
@if($featuredProducts->count() > 0)
<section class="section-block" style="background:#fff; padding-top:30px; padding-bottom:30px;">
    <div class="container">
        <div class="section-title-row">
            <h2 class="section-title">🔥 <span>Popular</span> Products</h2>
            <a href="{{ route('shop.products') }}" class="section-view-all">All Products <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 g-3">
            @foreach($featuredProducts as $product)
                @include('frontend.components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Flash Sale -->
@if(isset($flashSale) && $flashSale)
<section class="section-block" style="background: var(--hero-gradient); padding-top:36px; padding-bottom:36px;">
    <style>
        .flash-sale-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:24px; }
        .flash-sale-title { color:#fff; font-size:1.6rem; font-weight:800; margin:0 0 4px; letter-spacing:-0.5px; }
        .flash-sale-title span { color:#FFE066; }
        .flash-sale-sub { color:rgba(255,255,255,0.65); font-size:13px; margin:0; }
        .flash-cd { display:flex; align-items:center; gap:6px; }
        .flash-cd-box { background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.25); border-radius:8px; padding:6px 12px; text-align:center; min-width:54px; backdrop-filter:blur(6px); }
        .flash-cd-val { display:block; font-size:1.4rem; font-weight:800; color:#fff; line-height:1; }
        .flash-cd-lbl { display:block; font-size:9px; font-weight:600; color:rgba(255,255,255,0.6); letter-spacing:1px; margin-top:2px; }
        .flash-cd-sep { font-size:1.4rem; font-weight:800; color:#FFE066; margin-bottom:14px; }
        .flash-sale-badge { display:inline-flex; align-items:center; gap:6px; background:var(--primary-dark); color:#fff; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:0.5px; border:1px solid rgba(255,255,255,0.3); animation:flashPulse 1.4s ease-in-out infinite; }
        @keyframes flashPulse { 0%,100%{opacity:1} 50%{opacity:.7} }
        .flash-view-all { color:#fff; font-size:13px; font-weight:600; text-decoration:none; border:1px solid rgba(255,255,255,0.45); padding:6px 16px; border-radius:20px; transition:all .2s; }
        .flash-view-all:hover { background:rgba(255,255,255,0.18); color:#fff; }
    </style>
    <div class="container">
        <div class="flash-sale-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="flash-sale-badge">⚡ LIVE NOW</span>
                </div>
                <h2 class="flash-sale-title">Flash Sale — <span>{{ $flashSale->name }}</span></h2>
                <p class="flash-sale-sub">Hurry up! Offer ends in:</p>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="flash-cd" id="flashCountdown" data-ends="{{ $flashSale->ends_at->toISOString() }}">
                    <div class="flash-cd-box"><span class="flash-cd-val" id="fcd-d">00</span><span class="flash-cd-lbl">DAY</span></div>
                    <div class="flash-cd-sep">:</div>
                    <div class="flash-cd-box"><span class="flash-cd-val" id="fcd-h">00</span><span class="flash-cd-lbl">HRS</span></div>
                    <div class="flash-cd-sep">:</div>
                    <div class="flash-cd-box"><span class="flash-cd-val" id="fcd-m">00</span><span class="flash-cd-lbl">MIN</span></div>
                    <div class="flash-cd-sep">:</div>
                    <div class="flash-cd-box"><span class="flash-cd-val" id="fcd-s">00</span><span class="flash-cd-lbl">SEC</span></div>
                </div>
                <a href="{{ route('shop.offer') }}" class="flash-view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
            @foreach($flashSale->products->take(10) as $product)
                @php $product->sale_price = $product->pivot->sale_price ?? $product->sale_price; @endphp
                @include('frontend.components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
<script>
(function(){
    var el = document.getElementById('flashCountdown');
    if (!el) return;
    var ends = new Date(el.dataset.ends).getTime();
    function tick(){
        var diff = ends - Date.now();
        if (diff <= 0) { el.closest('section').style.display='none'; return; }
        var d = Math.floor(diff/86400000);
        var h = Math.floor((diff%86400000)/3600000);
        var m = Math.floor((diff%3600000)/60000);
        var s = Math.floor((diff%60000)/1000);
        document.getElementById('fcd-d').textContent = String(d).padStart(2,'0');
        document.getElementById('fcd-h').textContent = String(h).padStart(2,'0');
        document.getElementById('fcd-m').textContent = String(m).padStart(2,'0');
        document.getElementById('fcd-s').textContent = String(s).padStart(2,'0');
    }
    tick(); setInterval(tick, 1000);
})();
</script>
@endif

<!-- Category-wise Products -->
@foreach($categoryWiseProducts as $catId => $data)
    @if(isset($data['products']) && $data['products']->count() > 0)
    <section class="section-block" style="{{ $loop->even ? 'background:#fff;' : '' }} padding-top:30px; padding-bottom:30px;">
        <div class="container">
            <div class="section-title-row">
                <h2 class="section-title">{{ $data['category']->name }}</h2>
                <a href="{{ route('shop.category', $data['category']->slug) }}" class="section-view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
                @foreach($data['products'] as $product)
                    @include('frontend.components.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endforeach

<!-- New Arrivals -->
@if($newProducts->count() > 0)
<section class="section-block">
    <div class="container">
        <div class="section-title-row">
            <h2 class="section-title">🆕 New <span>Arrivals</span></h2>
            <a href="{{ route('shop.products', ['sort'=>'newest']) }}" class="section-view-all">View More <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
            @foreach($newProducts as $product)
                @include('frontend.components.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Home Page Content (from Site Content) -->
@if(!empty($homePageContent))
<section class="section-block" style="background:#fff; padding:48px 0;">
    <div class="container">
        <div class="home-page-content">{!! $homePageContent !!}</div>
    </div>
</section>
@endif

<!-- Why Choose Us -->
<section class="section-block why-choose-us">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size:26px; font-weight:800; color:#fff; letter-spacing:0.3px;">Why Shop with Us?</h2>
            <p style="color:rgba(255,255,255,0.7); font-size:14px; margin-top:6px;">We deliver the best experience every time</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-md-3 text-center">
                <div class="why-card">
                    <i class="fas fa-medal"></i>
                    <h3>Premium Quality</h3>
                    <p>Every product goes through strict quality control.</p>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="why-card">
                    <i class="fas fa-tags"></i>
                    <h3>Best Prices</h3>
                    <p>Direct sourcing means lowest prices for you.</p>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="why-card">
                    <i class="fas fa-truck-fast"></i>
                    <h3>Fast Delivery</h3>
                    <p>Inside Dhaka ৳70, Outside Dhaka ৳120 — nationwide coverage.</p>
                </div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <div class="why-card">
                    <i class="fas fa-headset"></i>
                    <h3>Dedicated Support</h3>
                    <p>We are always here to help you, 7 days a week.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('extra_js')
<script>
// Only init Swiper when real swiper slides exist
var heroEl = document.querySelector('.hero-swiper .swiper-wrapper');
if (heroEl) {
    var isMobile = window.innerWidth < 768;
    new Swiper('.hero-swiper', {
        loop: true,
        autoplay: { delay: 4500, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        autoHeight: isMobile,
        effect: 'fade',
        fadeEffect: { crossFade: true },
    });
}
</script>
@endsection
