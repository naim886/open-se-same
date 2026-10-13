@extends('frontend.layouts.app')

@section('title', ($category ? $category->name.' - ' : 'All Products - ').'Open Se Same')
@section('meta_description', $category ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.' : 'Shop all products at Open Se Same. Preserved roses, lingerie & fashion with fast delivery across Bangladesh.')
@section('og_title', ($category ? $category->name.' - ' : 'All Products - ').'Open Se Same')
@section('og_description', $category ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.' : 'Shop all products at Open Se Same. Preserved roses, lingerie & fashion with fast delivery across Bangladesh.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
use Illuminate\Support\Str;
$schemaPageTitle = $category ? $category->name.' - Open Se Same' : 'All Products - Open Se Same';
$schemaPageDesc  = $category
    ? 'Shop '.$category->name.' at Open Se Same. Best quality products with fast delivery across Bangladesh.'
    : 'Shop all products at Open Se Same. Preserved roses, lingerie & fashion with fast delivery across Bangladesh.';
$schemaPageUrl   = $category ? route('shop.category', $category->slug) : route('shop.products');
echo Schema::collectionPage()
    ->name($schemaPageTitle)
    ->description($schemaPageDesc)
    ->url($schemaPageUrl)
    ->toScript();
$schemaBreadItems = [Schema::listItem()->position(1)->name('Home')->item(route('shop.home'))];
if ($category) {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name($category->name)->item(route('shop.category', $category->slug));
} else {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name('All Products')->item(route('shop.products'));
}
echo Schema::breadcrumbList()->itemListElement($schemaBreadItems)->toScript();
if ($products->count() > 0) {
    $schemaListItems = $products->take(20)->values()->map(fn($p, $i) =>
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
    echo Schema::itemList()->name($schemaPageTitle)->itemListElement($schemaListItems)->toScript();
}
@endphp
@endsection

@section('content')

{{-- ===== Mobile Filter Offcanvas (hidden on lg+) ===== --}}
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
                <a href="{{ route('shop.category', $cat->slug) }}"
                   class="d-flex justify-content-between align-items-center text-decoration-none
                   {{ ($category && $category->id == $cat->id) ? 'fw-bold' : 'text-dark' }}"
                   style="{{ ($category && $category->id == $cat->id) ? 'color:var(--primary-dark)' : '' }}">
                    <span>{{ $cat->name }}</span>
                    <small class="text-muted">({{ $cat->products_count ?? '' }})</small>
                </a>
            </div>
        @endforeach

        <hr>
        <h6 class="filter-title pt-2">💰 Filter by Price</h6>
        <form action="{{ request()->routeIs('shop.category') ? route('shop.category', $category->slug ?? 'all') : route('shop.products') }}" method="GET">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
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

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                @if($category)
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                @else
                    <li class="breadcrumb-item active">All Products</li>
                @endif
            </ol>
        </nav>
    </div>
</div>

<section class="section-block">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar Filter -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-sidebar">
                    <h6 class="filter-title border-bottom pb-2 mb-3">📂 Categories</h6>
                    @foreach($allCategories as $cat)
                        <div class="filter-item">
                            <a href="{{ route('shop.category', $cat->slug) }}"
                               class="d-flex justify-content-between align-items-center text-decoration-none
                               {{ ($category && $category->id == $cat->id) ? 'fw-bold' : 'text-dark' }}"
                               style="{{ ($category && $category->id == $cat->id) ? 'color:var(--primary-dark)' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <small class="text-muted">({{ $cat->products_count ?? '' }})</small>
                            </a>
                        </div>
                    @endforeach

                    <hr>
                    <h6 class="filter-title pt-2">💰 Filter by Price</h6>
                    <form action="{{ request()->routeIs('shop.category') ? route('shop.category', $category->slug ?? 'all') : route('shop.products') }}" method="GET">
                        @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
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
                        @if($category)
                            <strong>{{ $category->name }}</strong>
                        @elseif(request('q'))
                            Results for "<strong>{{ request('q') }}</strong>"
                        @else
                            <strong>All Products</strong>
                        @endif
                        <span class="text-muted ms-2">({{ $products->total() }} products)</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-1" style="font-size:13px;">Sort by:</label>
                        <select onchange="window.location.href=this.value" style="font-size:13px; border:1px solid #ddd; padding:6px 12px; border-radius:6px;">
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}" {{ request('sort','newest')=='newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_asc']) }}" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'price_desc']) }}" {{ request('sort')=='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort'=>'popular']) }}" {{ request('sort')=='popular' ? 'selected' : '' }}>Popular</option>
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
@endsection

@section('extra_js')
<script>
@if(request('q'))
// ── Meta Pixel: Search ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'Search', {
            search_string: {!! json_encode(request('q')) !!}
        });
    }
});
@endif
</script>
@endsection

