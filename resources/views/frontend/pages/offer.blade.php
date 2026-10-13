@extends('frontend.layouts.app')

@section('title', 'Special Offers & Discounts — Open Se Same')
@section('meta_description', 'Shop the best discounted products at Open Se Same. Huge savings on preserved roses, lingerie & fashion. Limited time offers with fast delivery across Bangladesh.')
@section('og_title', 'Special Offers & Discounts — Open Se Same')
@section('og_description', 'Shop the best discounted products at Open Se Same. Huge savings on preserved roses, lingerie & fashion.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::collectionPage()
    ->name('Special Offers & Discounts — Open Se Same')
    ->description('Shop the best discounted products at Open Se Same.')
    ->url(route('shop.offer'))
    ->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name('Special Offers')->item(route('shop.offer')),
])->toScript();
@endphp
@endsection

@section('content')

{{-- Mobile Filter Offcanvas --}}
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="max-width:300px;">
    <div class="offcanvas-header" style="background:var(--primary-gradient);color:#fff;">
        <h5 class="offcanvas-title fw-bold" id="filterOffcanvasLabel">
            <i class="fas fa-filter me-2"></i>Filter Products
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <h6 class="filter-title border-bottom pb-2 mb-3">📂 Categories</h6>
        @foreach($allCategories as $cat)
            <div class="filter-item">
                <a href="{{ route('shop.offer') }}?category={{ $cat->id }}"
                   class="d-flex justify-content-between align-items-center text-decoration-none text-dark">
                    <span>{{ $cat->name }}</span>
                    <small class="text-muted">({{ $cat->products_count }})</small>
                </a>
            </div>
        @endforeach
        <hr>
        <h6 class="filter-title pt-2">💰 Filter by Price</h6>
        <form action="{{ route('shop.offer') }}" method="GET">
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            <div class="row g-2 mt-1">
                <div class="col-6">
                    <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ৳" value="{{ request('min_price') }}">
                </div>
                <div class="col-6">
                    <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ৳" value="{{ request('max_price') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm w-100" style="background:var(--primary-gradient);color:#fff;">Apply Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== Hero Banner ===== --}}
<div style="background: var(--hero-gradient); padding: 36px 0 30px; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.04\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');opacity:.5;"></div>
    <div class="container" style="position:relative;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:0.5px;">
                        🔥 HOT DEALS
                    </span>
                    <span style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:0.5px;">
                        {{ $products->total() }} Products
                    </span>
                </div>
                <h1 style="color:#fff;font-size:2rem;font-weight:800;margin:0 0 6px;letter-spacing:-0.5px;">
                    Special Offers & <span style="color:#ffd6e7;">Discounts</span>
                </h1>
                <p style="color:rgba(255,255,255,0.7);margin:0;font-size:14px;">
                    All products with active sale prices — grab them before they're gone!
                </p>
            </div>

            {{-- Flash Sale Countdown (if running) --}}
            @if($flashSale)
            <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);border-radius:12px;padding:14px 20px;text-align:center;min-width:240px;">
                <div style="color:rgba(255,255,255,0.7);font-size:11px;font-weight:600;letter-spacing:0.5px;margin-bottom:6px;">
                    ⚡ FLASH SALE ENDS IN
                </div>
                <div class="d-flex align-items-center justify-content-center gap-2" id="offerCountdown" data-ends="{{ $flashSale->ends_at->toISOString() }}">
                    <div style="text-align:center;">
                        <span style="display:block;font-size:1.5rem;font-weight:800;color:#fff;line-height:1;" id="ocd-d">00</span>
                        <span style="display:block;font-size:9px;color:rgba(255,255,255,0.6);letter-spacing:1px;margin-top:2px;">DAY</span>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:#ffd6e7;margin-bottom:14px;">:</span>
                    <div style="text-align:center;">
                        <span style="display:block;font-size:1.5rem;font-weight:800;color:#fff;line-height:1;" id="ocd-h">00</span>
                        <span style="display:block;font-size:9px;color:rgba(255,255,255,0.6);letter-spacing:1px;margin-top:2px;">HRS</span>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:#ffd6e7;margin-bottom:14px;">:</span>
                    <div style="text-align:center;">
                        <span style="display:block;font-size:1.5rem;font-weight:800;color:#fff;line-height:1;" id="ocd-m">00</span>
                        <span style="display:block;font-size:9px;color:rgba(255,255,255,0.6);letter-spacing:1px;margin-top:2px;">MIN</span>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:#ffd6e7;margin-bottom:14px;">:</span>
                    <div style="text-align:center;">
                        <span style="display:block;font-size:1.5rem;font-weight:800;color:#fff;line-height:1;" id="ocd-s">00</span>
                        <span style="display:block;font-size:9px;color:rgba(255,255,255,0.6);letter-spacing:1px;margin-top:2px;">SEC</span>
                    </div>
                </div>
                <div style="color:rgba(255,255,255,0.6);font-size:11px;margin-top:6px;">{{ $flashSale->name }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Breadcrumb --}}
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item active">Special Offers</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section-block">
    <div class="container">
        <div class="row g-4">

            {{-- Sidebar --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-sidebar">
                    <h6 class="filter-title border-bottom pb-2 mb-3">📂 Shop by Category</h6>
                    <div class="filter-item">
                        <a href="{{ route('shop.offer') }}"
                           class="d-flex justify-content-between align-items-center text-decoration-none {{ !request('category') ? 'fw-bold' : 'text-dark' }}"
                           style="{{ !request('category') ? 'color:var(--primary-dark)' : '' }}">
                            <span>All Offers</span>
                            <small class="text-muted">({{ $products->total() }})</small>
                        </a>
                    </div>
                    @foreach($allCategories as $cat)
                        <div class="filter-item">
                            <a href="{{ route('shop.offer') }}?category={{ $cat->id }}{{ request('sort') ? '&sort='.request('sort') : '' }}"
                               class="d-flex justify-content-between align-items-center text-decoration-none {{ request('category') == $cat->id ? 'fw-bold' : 'text-dark' }}"
                               style="{{ request('category') == $cat->id ? 'color:var(--primary-dark)' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <small class="text-muted">({{ $cat->products_count }})</small>
                            </a>
                        </div>
                    @endforeach

                    <hr>
                    <h6 class="filter-title pt-2">💰 Filter by Price</h6>
                    <form action="{{ route('shop.offer') }}" method="GET">
                        @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ৳" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ৳" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm w-100" style="background:var(--primary-gradient);color:#fff;">Apply Filter</button>
                            </div>
                        </div>
                    </form>

                    @if(request('min_price') || request('max_price') || request('category'))
                    <div class="mt-2">
                        <a href="{{ route('shop.offer') }}" class="text-muted" style="font-size:12px;">
                            <i class="fas fa-times me-1"></i>Clear filters
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Products --}}
            <div class="col-lg-9">
                {{-- Sort bar --}}
                <div class="sort-bar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button class="btn d-lg-none" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas"
                                style="background:var(--primary-gradient);color:#fff;font-size:13px;padding:6px 14px;border-radius:6px;font-weight:600;">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <div>
                            <strong>🔥 Special Offers</strong>
                            <span class="text-muted ms-2">({{ $products->total() }} products)</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-1" style="font-size:13px;">Sort by:</label>
                        <select onchange="window.location.href=this.value" style="font-size:13px;border:1px solid #ddd;padding:6px 12px;border-radius:6px;">
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'discount']) }}"  {{ request('sort','discount')=='discount' ? 'selected' : '' }}>Biggest Discount</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}"    {{ request('sort')=='newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_asc']) }}" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_desc']) }}"{{ request('sort')=='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'popular']) }}"   {{ request('sort')=='popular' ? 'selected' : '' }}>Popular</option>
                        </select>
                    </div>
                </div>

                @if($products->count() > 0)
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xxl-4 g-3">
                        @foreach($products as $product)
                            @include('frontend.components.product-card', ['product' => $product])
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @else
                    <div class="no-products">
                        <i class="fas fa-tags"></i>
                        <h5>No discounted products found</h5>
                        <p class="text-muted">Try removing the filters or browse all products.</p>
                        <a href="{{ route('shop.offer') }}" class="btn mt-2 me-2" style="background:var(--primary-gradient);color:#fff;padding:10px 24px;border-radius:6px;">Clear Filters</a>
                        <a href="{{ route('shop.products') }}" class="btn mt-2" style="border:2px solid var(--primary);color:var(--primary-dark);padding:10px 24px;border-radius:6px;">All Products</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('extra_js')
<script>
(function () {
    var el = document.getElementById('offerCountdown');
    if (!el) return;
    var ends = new Date(el.dataset.ends).getTime();
    function tick() {
        var diff = ends - Date.now();
        if (diff <= 0) { el.closest('[data-ends]') && (el.style.display = 'none'); return; }
        var d = Math.floor(diff / 86400000);
        var h = Math.floor((diff % 86400000) / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.getElementById('ocd-d').textContent = String(d).padStart(2, '0');
        document.getElementById('ocd-h').textContent = String(h).padStart(2, '0');
        document.getElementById('ocd-m').textContent = String(m).padStart(2, '0');
        document.getElementById('ocd-s').textContent = String(s).padStart(2, '0');
    }
    tick(); setInterval(tick, 1000);
})();
</script>
@endsection
