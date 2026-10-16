@php
    // Shared listing body for "all products", category, brand and search pages.
    $category  = $category ?? null;
    $brand     = $brand ?? null;

    $pageTitle     = $category?->name ?? ($brand ? $brand->name : null);
    $pageDesc      = $category?->short_description ?? $brand?->short_description;
    $bannerUrl     = $category?->banner_url ?? $brand?->banner_url;
    $sortBarTitle  = $pageTitle ?? (request('q') ? 'Results for "' . request('q') . '"' : 'All Products');
@endphp

{{-- ===== Mobile Filter Drawer (hidden on lg+) ===== --}}
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="max-width:300px;">
    <div class="offcanvas-header" style="background:var(--primary-gradient);color:#fff;">
        <h5 class="offcanvas-title fw-bold" id="filterOffcanvasLabel">
            <i class="fas fa-filter me-2"></i>Filter Products
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @include('frontend.partials.shop-filters')
    </div>
</div>

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                @if($category)
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                @elseif($brand)
                    <li class="breadcrumb-item"><a href="{{ route('shop.products') }}">Products</a></li>
                    <li class="breadcrumb-item active">{{ $brand->name }}</li>
                @else
                    <li class="breadcrumb-item active">All Products</li>
                @endif
            </ol>
        </nav>
    </div>
</div>

{{-- Category / brand banner and heading --}}
@if($category || $brand)
    <section class="shop-page-header">
        @if($bannerUrl)
            <div class="shop-page-banner">
                <img src="{{ $bannerUrl }}" alt="{{ $pageTitle }}" loading="lazy"
                     onerror="this.closest('.shop-page-banner').style.display='none';">
            </div>
        @endif
        <div class="container">
            <div class="shop-page-header-inner">
                @if($brand && $brand->logo_url)
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }} logo" class="shop-page-logo"
                         onerror="this.style.display='none';">
                @endif
                <div>
                    <h1 class="shop-page-title">{{ $pageTitle }}</h1>
                    @if($pageDesc)
                        <p class="shop-page-desc">{{ $pageDesc }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

<section class="section-block">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar Filter -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-sidebar">
                    @include('frontend.partials.shop-filters')
                </div>
            </div>

            <!-- Products Area -->
            <div class="col-lg-9">
                <!-- Sort Bar -->
                <div class="sort-bar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Mobile filter trigger (hidden on lg+) --}}
                        <button class="btn d-lg-none" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas"
                                aria-controls="filterOffcanvas"
                                style="background:var(--primary-gradient);color:#fff;font-size:13px;padding:6px 14px;border-radius:6px;font-weight:600;">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <div>
                            <strong>{{ $sortBarTitle }}</strong>
                            <span class="text-muted ms-2">({{ $products->total() }} products)</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-1" style="font-size:13px;">Sort by:</label>
                        <select onchange="window.location.href=this.value" style="font-size:13px; border:1px solid #ddd; padding:6px 12px; border-radius:6px;">
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'newest','page'=>null]) }}" {{ request('sort','newest')=='newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_asc','page'=>null]) }}" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_desc','page'=>null]) }}" {{ request('sort')=='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'popular','page'=>null]) }}" {{ request('sort')=='popular' ? 'selected' : '' }}>Popular</option>
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
                        <i class="fas fa-search"></i>
                        <h5>No products found</h5>
                        <p class="text-muted">Try a different search term or change the filters.</p>
                        <a href="{{ route('shop.products') }}" class="btn mt-3" style="background:var(--primary-gradient);color:#fff;padding:10px 24px;border-radius:6px;">View All Products</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
