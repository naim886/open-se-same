@extends('frontend.layouts.app')

@section('title', $product->name.' - Open Se Same')
@section('meta_description', $product->meta_description ?? $product->short_description)
@section('og_title',       $product->name.' - Open Se Same')
@section('og_description', $product->meta_description ?? $product->short_description ?? 'Shop '.$product->name.' at Open Se Same.')
@section('og_type',        'product')
@section('og_image',       $product->primary_photo ? asset('storage/'.$product->primary_photo) : asset('images/og.webp'))

@section('extra_css')
<style>
.gallery-thumb-active { border-color: var(--primary) !important; }

/* ── Variation Option Buttons ─────────────────────── */
.var-option-btn {
    padding: 6px 16px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    background: #fff;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    color: var(--text-dark);
}
.var-option-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.var-option-btn.selected {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}
.var-option-btn.unavailable {
    opacity: 0.38;
    cursor: not-allowed;
    text-decoration: line-through;
}

/* ── Share Section ─────────────────────────────────── */
.share-section {
    padding: 16px 0 4px;
    border-top: 1px solid var(--border);
}
.share-section-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.share-buttons-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.share-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
    transition: transform 0.2s ease;
}
.share-btn:hover { transform: translateY(-3px); }
.share-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transition: box-shadow 0.2s ease;
}
.share-btn:hover .share-icon { box-shadow: 0 6px 18px rgba(0,0,0,0.25); }
.share-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}
/* ── Key Benefits ── */
.key-benefits-section {
    background: rgba(1,92,60,0.04);
    border: 1px solid rgba(1,92,60,0.14);
    border-radius: 12px;
    padding: 14px 16px;
}
.key-benefits-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.key-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.key-benefits-list li {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-dark);
    background: #fff;
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 500;
}
.key-benefits-list li i {
    color: var(--primary);
    font-size: 11px;
    flex-shrink: 0;
}
/* ── Minimal Chat Row ── */
.chat-mini-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 8px 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-bottom: 12px;
}
.chat-mini-label {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}
.chat-mini-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #fff;
    text-decoration: none;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    transition: opacity 0.2s;
}
.chat-mini-btn:hover { opacity: 0.85; color: #fff; }
</style>
@endsection

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
$schemaPrice = (float)($product->sale_price ?? $product->price ?? 0);
$productSchema = Schema::product()
    ->name($product->name)
    ->description(strip_tags($product->meta_description ?? $product->short_description ?? ''))
    ->image($product->primary_photo ? asset('storage/'.$product->primary_photo) : asset('images/og.webp'))
    ->url(route('shop.product', $product->slug))
    ->sku($product->sku ?: (string) $product->id)
    ->brand(Schema::brand()->name($product->brand?->name ?? 'Open Se Same'))
    ->category($product->category?->name ?? '')
    ->offers(
        Schema::offer()
            ->price($schemaPrice)
            ->priceCurrency('BDT')
            ->availability($product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock')
            ->url(route('shop.product', $product->slug))
            ->seller(Schema::organization()->name('Open Se Same')->url(url('/')))
            ->priceValidUntil(now()->addYear()->format('Y-m-d'))
    );
echo $productSchema->toScript();
$schemaBreadItems = [
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
];
if ($product->category) {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name($product->category->name)->item(route('shop.category', $product->category->slug));
    $schemaBreadItems[] = Schema::listItem()->position(3)->name($product->name)->item(route('shop.product', $product->slug));
} else {
    $schemaBreadItems[] = Schema::listItem()->position(2)->name('Products')->item(route('shop.products'));
    $schemaBreadItems[] = Schema::listItem()->position(3)->name($product->name)->item(route('shop.product', $product->slug));
}
echo Schema::breadcrumbList()->itemListElement($schemaBreadItems)->toScript();
@endphp
@endsection

@section('content')

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('shop.category', $product->category->slug) }}">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ Str::limit($product->name, 40) }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Detail -->
<section class="product-detail-section">
    <div class="container">
        <div class="product-detail-card">
        <div class="row g-4">

            <!-- Images -->
            <div class="col-lg-5 col-md-6">
                <div class="position-relative">
                    @php
                        $mainImg = $product->primary_photo
                            ? asset('storage/'.$product->primary_photo)
                            : ($product->photos->first() ? asset('storage/'.$product->photos->first()->photo_path) : null);
                    @endphp
                    @if($mainImg)
                        <img src="{{ $mainImg }}" alt="{{ $product->name }}" class="product-detail-main-img" id="mainProductImg">
                    @else
                        <div class="product-detail-main-img d-flex align-items-center justify-content-center" style="background:#f5f5f5;">
                            <i class="fas fa-image" style="font-size:80px; color:#ddd;"></i>
                        </div>
                    @endif

                    @if($product->sale_price && $product->price > $product->sale_price)
                        @php $disc = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                        <span class="product-discount-badge" style="font-size:14px; padding:5px 12px;">-{{ $disc }}% OFF</span>
                    @endif
                </div>

                <!-- Thumbnails -->
                @if($product->photos->count() > 0 || $product->primary_photo)
                <div class="product-thumbnails">
                    @if($product->primary_photo)
                        <img src="{{ asset('storage/'.$product->primary_photo_thumb) }}"
                             class="product-thumb gallery-thumb-active"
                             onclick="changeMainImg(this, '{{ asset('storage/'.$product->primary_photo) }}')"
                             alt="{{ $product->name }}">
                    @endif
                    @foreach($product->photos as $photo)
                        <img src="{{ asset('storage/'.$photo->photo_thumb) }}"
                             class="product-thumb"
                             onclick="changeMainImg(this, '{{ asset('storage/'.$photo->photo_path) }}')"
                             alt="{{ $product->name }}">
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="col-lg-7 col-md-6">
                @if($product->category)
                    {{-- category badge hidden on detail page --}}
                @endif

                <h1 class="product-detail-title">{{ $product->name }}</h1>

                <!-- Price -->
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap" id="priceContainer">
                    @if($product->isVariantProduct() && ($priceRange = $product->priceRange))
                        @if($priceRange['min'] == $priceRange['max'])
                            <span class="product-detail-price">৳{{ number_format($priceRange['min'], 0) }}</span>
                        @else
                            <span class="product-detail-price">৳{{ number_format($priceRange['min'], 0) }}–{{ number_format($priceRange['max'], 0) }}</span>
                        @endif
                    @else
                        <span class="product-detail-price">৳{{ number_format($product->sale_price ?? $product->price, 0) }}</span>
                        @if(isset($disc) && $product->sale_price && $product->price > $product->sale_price)
                            <span class="product-detail-original-price">৳{{ number_format($product->price, 0) }}</span>
                            <span class="product-detail-discount">{{ $disc }}% Savings</span>
                        @endif
                    @endif
                </div>

                @if(!empty($product->key_benefits) && count($product->key_benefits) > 0)
                <div class="key-benefits-section mb-3">
                    <div class="key-benefits-title"><i class="fas fa-star"></i> Key Benefits</div>
                    <ul class="key-benefits-list">
                        @foreach($product->key_benefits as $benefit)
                            @if(trim($benefit))
                                <li><i class="fas fa-check-circle"></i> {{ $benefit }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($product->short_description)
                    <p class="text-muted mb-3" style="font-size:14px; line-height:1.7;">{{ $product->short_description }}</p>
                @endif

                <!-- Variation Selectors -->
                @if($product->isVariantProduct() && $product->activeVariations->count() > 0)
                @php
                    $groupedAttrs = [];
                    foreach ($product->activeVariations as $var) {
                        foreach ($var->variationAttributes as $va) {
                            $nid = $va->attribute_name_id;
                            $vid = $va->attribute_value_id;
                            if (!isset($groupedAttrs[$nid])) {
                                $groupedAttrs[$nid] = ['name' => $va->attributeName?->name ?? '', 'values' => []];
                            }
                            $groupedAttrs[$nid]['values'][$vid] = $va->attributeValue?->name ?? '';
                        }
                    }
                @endphp
                <div id="variationSelectorWrap" class="mb-3">
                    @foreach($groupedAttrs as $nameId => $attrGroup)
                    <div class="mb-3 var-attr-group" data-name-id="{{ $nameId }}">
                        <label class="fw-bold d-block mb-2" style="font-size:14px;">{{ $attrGroup['name'] }}:</label>
                        <div class="d-flex flex-wrap gap-2 var-value-buttons">
                            @foreach($attrGroup['values'] as $valueId => $valueName)
                            <button type="button"
                                    class="var-option-btn"
                                    data-name-id="{{ $nameId }}"
                                    data-value-id="{{ $valueId }}">
                                {{ $valueName }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    <input type="hidden" id="selectedVariationId" value="">
                    <div id="varSelectionMsg" class="text-muted" style="font-size:12.5px;"></div>
                </div>
                @endif

                <!-- Stock -->
                <div class="mb-3" id="stockDisplay">
                    @if($product->stock_text)
                        <span style="color:#28a745; font-weight:600; font-size:14px;">
                            <i class="fas fa-check-circle me-1"></i> {{ $product->stock_text }}
                        </span>
                    @elseif($product->stock !== null)
                        @if($product->stock > 0)
                            <span style="color:#28a745; font-weight:600; font-size:14px;">
                                <i class="fas fa-check-circle me-1"></i> In Stock
                            </span>
                        @else
                            <span style="color:#dc3545; font-weight:600; font-size:14px;">
                                <i class="fas fa-times-circle me-1"></i> Out of Stock
                            </span>
                        @endif
                    @else
                        <span style="color:#28a745; font-weight:600; font-size:14px;">
                            <i class="fas fa-check-circle me-1"></i> In Stock
                        </span>
                    @endif
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label class="fw-bold d-block mb-2" style="font-size:14px;">Quantity:</label>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="changeQty(-1)" type="button">−</button>
                        <input type="number" id="productQty" class="qty-input" value="1" min="1"
                               max="{{ ($product->stock !== null && $product->stock > 0) ? $product->stock : 99 }}">
                        <button class="qty-btn" onclick="changeQty(1)" type="button">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="product-action-row mb-3">
                    @if($product->stock === null || $product->stock > 0 || $product->isVariantProduct())
                        <button class="btn-add-cart-lg" id="addToCartBtn" onclick="handleAddToCart({{ $product->id }}, {{ $product->isVariantProduct() ? 'true' : 'false' }})">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                        <a href="{{ route('shop.quick-order', $product->slug) }}" class="btn-buy-now">
                            <i class="fas fa-bolt me-2"></i>Buy Now
                        </a>
                    @else
                        <button class="btn-buy-now" disabled style="background:#ccc; border-color:#ccc; cursor:not-allowed;">
                            Out of Stock
                        </button>
                    @endif
                </div>

                <!-- Chat with us (minimal) -->
                <div class="chat-mini-row">
                    <span class="chat-mini-label"><i class="fas fa-headset me-1" style="color:var(--primary);"></i>Chat with us:</span>
                    <a href="https://m.me/opensesame1?text={{ urlencode('Hi! I have a question about: '.$product->name.' '.url()->current()) }}"
                       target="_blank" class="chat-mini-btn" style="background:linear-gradient(135deg,#00b2ff,#006aff);">
                        <i class="fab fa-facebook-messenger"></i> Messenger
                    </a>
                    <a href="https://wa.me/+8801886870406?text={{ urlencode('Hi! I have a question about: '.$product->name.' '.url()->current()) }}"
                       target="_blank" class="chat-mini-btn" style="background:linear-gradient(135deg,#25d366,#128c7e);">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>

                <!-- Delivery Info -->
                <div class="product-delivery-info">
                    <div class="product-delivery-item">
                        <i class="fas fa-location-dot"></i>
                        <div>
                            <strong>Inside Dhaka</strong>
                            <span>৳70 · 1–2 days</span>
                        </div>
                    </div>
                    <div class="product-delivery-item">
                        <i class="fas fa-truck-fast"></i>
                        <div>
                            <strong>Outside Dhaka</strong>
                            <span>৳120 · 2–5 days</span>
                        </div>
                    </div>
                    <div class="product-delivery-item">
                        <i class="fas fa-headset"></i>
                        <div>
                            <strong>Need help?</strong>
                            <span><a href="tel:+8801886870406">01886870406</a></span>
                        </div>
                    </div>
                </div>


                <!-- Share -->
                <div class="share-section mt-3">
                    <div class="share-section-label">Share this product:</div>
                    <div class="share-buttons-row">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           target="_blank" class="share-btn" data-color="#1877f2">
                            <span class="share-icon" style="background:#1877f2;"><i class="fab fa-facebook-f"></i></span>
                            <span class="share-label">Facebook</span>
                        </a>
                        <a href="https://www.facebook.com/dialog/send?link={{ urlencode(url()->current()) }}&app_id=291494419107518&redirect_uri={{ urlencode(url()->current()) }}"
                           target="_blank" class="share-btn" data-color="#006aff">
                            <span class="share-icon" style="background:linear-gradient(135deg,#00b2ff,#006aff);"><i class="fab fa-facebook-messenger"></i></span>
                            <span class="share-label">Messenger</span>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($product->name.' - '.url()->current()) }}"
                           target="_blank" class="share-btn">
                            <span class="share-icon" style="background:#25d366;"><i class="fab fa-whatsapp"></i></span>
                            <span class="share-label">WhatsApp</span>
                        </a>
                        <button onclick="shareToInstagram()" class="share-btn" id="instagramShareBtn">
                            <span class="share-icon" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);"><i class="fab fa-instagram"></i></span>
                            <span class="share-label">Instagram</span>
                        </button>
                        <button onclick="copyProductLink()" class="share-btn" id="copyLinkBtn">
                            <span class="share-icon" style="background:#6c757d;"><i class="fas fa-link"></i></span>
                            <span class="share-label" id="copyLinkLabel">Copy Link</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        </div>

        <!-- Product Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav product-tab-nav border-bottom mb-0" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-3" id="tab-desc" data-bs-toggle="tab"
                                data-bs-target="#tabDescription" type="button" role="tab">
                            Description
                        </button>
                    </li>
                    @if($product->specifications->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3" id="tab-specs" data-bs-toggle="tab"
                                data-bs-target="#tabSpecs" type="button" role="tab">
                            Specifications
                        </button>
                    </li>
                    @endif
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3" id="tab-delivery" data-bs-toggle="tab"
                                data-bs-target="#tabDelivery" type="button" role="tab">
                            Delivery Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3" id="tab-return" data-bs-toggle="tab"
                                data-bs-target="#tabReturn" type="button" role="tab">
                            Return Policy
                        </button>
                    </li>
                    @if($product->qas->count() > 0)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3" id="tab-qa" data-bs-toggle="tab"
                                data-bs-target="#tabQa" type="button" role="tab">
                            <i class="fas fa-question-circle me-1"></i> Q&amp;A
                            <span class="badge ms-1" style="background:var(--primary-gradient); font-size:10px;">{{ $product->qas->count() }}</span>
                        </button>
                    </li>
                    @endif
                </ul>

                <div class="tab-content p-4" style="background:#fff; border:1px solid var(--border); border-top:none; border-radius:0 0 12px 12px; box-shadow:var(--shadow);">

                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="tabDescription" role="tabpanel">
                        @if($product->description)
                            <div class="product-description" style="font-size:14.5px; line-height:1.8;">
                                {!! $product->description !!}
                            </div>
                        @else
                            <p class="text-muted mb-0">No description available for this product.</p>
                        @endif
                    </div>

                    <!-- Specifications Tab -->
                    @if($product->specifications->count() > 0)
                    <div class="tab-pane fade" id="tabSpecs" role="tabpanel">
                        <table class="table table-bordered mb-0" style="font-size:14px;">
                            @foreach($product->specifications as $spec)
                                <tr>
                                    <td style="width:200px; font-weight:600; background:#f9f9f9;">{{ $spec->specification->specifications_name ?? '' }}</td>
                                    <td>{{ $spec->value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    @endif

                    <!-- Delivery Tab -->
                    <div class="tab-pane fade" id="tabDelivery" role="tabpanel">
                        @if($deliveryInfo)
                            <div style="font-size:14.5px; line-height:1.8;">{!! $deliveryInfo !!}</div>
                        @else
                            <div class="row g-4" style="font-size:14px;">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-truck" style="color:var(--primary);"></i> Delivery Charges</h6>
                                    <table class="table table-sm mb-0">
                                        <tr><td>Inside Dhaka</td><td><strong>৳70</strong></td></tr>
                                        <tr><td>Outside Dhaka</td><td><strong>৳120</strong></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-clock" style="color:var(--primary);"></i> Delivery Time</h6>
                                    <ul style="padding-left:18px; line-height:2.2; margin:0;">
                                        <li>Inside Dhaka: 1–2 business days</li>
                                        <li>Outside Dhaka: 2–5 business days</li>
                                        <li>Counted from order confirmation</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Return Policy Tab -->
                    <div class="tab-pane fade" id="tabReturn" role="tabpanel">
                        @if($returnInfo)
                            <div style="font-size:14.5px; line-height:1.8;">{!! $returnInfo !!}</div>
                        @else
                            <div class="row g-4" style="font-size:14px;">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-undo text-danger me-2"></i>Return Conditions</h6>
                                    <ul style="padding-left:18px; line-height:2.4; margin:0;">
                                        <li>Return request must be made within <strong>3 days</strong> of delivery</li>
                                        <li>Product must be <strong>unused</strong> and in original packaging</li>
                                        <li>Tags and labels must be intact</li>
                                        <li>Damaged or washed items are not eligible for return</li>
                                        <li>Customized or sale items cannot be returned</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-exchange-alt text-danger me-2"></i>How to Return</h6>
                                    <ol style="padding-left:18px; line-height:2.4; margin:0;">
                                        <li>Contact us with your order number to initiate a return</li>
                                        <li>Send a clear photo of the product issue</li>
                                        <li>We will confirm eligibility and guide you through the process</li>
                                        <li>Refund/exchange is processed within <strong>3–5 business days</strong></li>
                                    </ol>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Q&A Tab -->
                    @if($product->qas->count() > 0)
                    <div class="tab-pane fade" id="tabQa" role="tabpanel">
                        <div class="accordion accordion-flush" id="qaAccordion">
                            @foreach($product->qas as $qi => $qa)
                            <div class="accordion-item" style="border-bottom:1px solid var(--border); background:transparent;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $qi > 0 ? 'collapsed' : '' }} fw-500"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#qaItem{{ $qa->id }}"
                                            style="background:transparent; font-size:14.5px; color:var(--text-dark); font-weight:600;">
                                        <i class="fas fa-question-circle me-2" style="color:var(--primary); flex-shrink:0;"></i>
                                        {{ $qa->question }}
                                    </button>
                                </h2>
                                <div id="qaItem{{ $qa->id }}"
                                     class="accordion-collapse collapse {{ $qi === 0 ? 'show' : '' }}"
                                     data-bs-parent="#qaAccordion">
                                    <div class="accordion-body" style="font-size:14.5px; line-height:1.8; color:var(--text-body); padding-left:36px;">
                                        @if($qa->answer)
                                            <i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>{{ $qa->answer }}
                                        @else
                                            <span class="text-muted fst-italic">Answer coming soon.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
        <div class="mt-5">
            <div class="section-title-row">
                <h2 class="section-title">Related <span>Products</span></h2>
            </div>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 g-3">
                @foreach($relatedProducts as $rProduct)
                    @include('frontend.components.product-card', ['product' => $rProduct])
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

@endsection

@section('extra_js')
<script>
function changeMainImg(el, src) {
    var mainImg = document.getElementById('mainProductImg');
    if (mainImg) mainImg.src = src;
    var thumbs = document.querySelectorAll('.product-thumb');
    for (var i = 0; i < thumbs.length; i++) {
        thumbs[i].classList.remove('gallery-thumb-active');
    }
    if (el) el.classList.add('gallery-thumb-active');
}

function changeQty(delta) {
    var input = document.getElementById('productQty');
    if (!input) return;
    var val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    var maxVal = parseInt(input.max);
    if (maxVal && val > maxVal) val = maxVal;
    input.value = val;
}

function addToCartWithQty(productId) {
    var input = document.getElementById('productQty');
    var qty = input ? (parseInt(input.value) || 1) : 1;
    addToCart(productId, qty);
}

// ── Product Variation System ──────────────────────────────────────────
@if($product->isVariantProduct() && $product->activeVariations->count() > 0)
(function () {
    var variations = @json($product->activeVariations->map->toFrontendArray()->values());
    var selectedAttrs = {}; // { nameId: valueId, ... }

    // Exact match (all attributes selected)
    function findMatchingVariation() {
        return variations.find(function (v) {
            if (v.attributes.length !== Object.keys(selectedAttrs).length) return false;
            return v.attributes.every(function (a) {
                return String(selectedAttrs[a.name_id]) === String(a.value_id);
            });
        }) || null;
    }

    // Partial match — every currently-selected attr must match the variation
    // Used for image swapping before all attributes are chosen
    function findPartialMatch() {
        var keys = Object.keys(selectedAttrs);
        if (keys.length === 0) return null;
        return variations.find(function (v) {
            return keys.every(function (nameId) {
                return v.attributes.some(function (a) {
                    return String(a.name_id) === String(nameId) &&
                           String(a.value_id) === String(selectedAttrs[nameId]);
                });
            });
        }) || null;
    }

    function updateUI() {
        var matched = findMatchingVariation();

        // ── Price display ──
        var $priceEl = document.getElementById('priceContainer');
        if ($priceEl) {
            var priceHtml;
            if (matched) {
                if (matched.sale_price && matched.sale_price < matched.price) {
                    var disc = Math.round((1 - matched.sale_price / matched.price) * 100);
                    priceHtml = '<span class="product-detail-original-price me-2">৳' + matched.price.toLocaleString('en-BD') + '</span>' +
                                '<span class="product-detail-price">৳' + matched.sale_price.toLocaleString('en-BD') + '</span>' +
                                '<span class="product-detail-discount ms-2">' + disc + '% Savings</span>';
                } else {
                    priceHtml = '<span class="product-detail-price">৳' + matched.price.toLocaleString('en-BD') + '</span>';
                }
                $priceEl.innerHTML = priceHtml;
            }
            // If no full match yet, keep the range display — don't overwrite
        }

        // ── Image swap (uses partial match so Color-only selection still swaps) ──
        var imageSource = (matched && matched.image_url) ? matched : findPartialMatch();
        if (imageSource && imageSource.image_url) {
            var mainImg = document.getElementById('mainProductImg');
            if (mainImg) mainImg.src = imageSource.image_url;
            document.querySelectorAll('.product-thumb').forEach(function (t) {
                t.classList.remove('gallery-thumb-active');
            });
        }

        // ── Stock ──
        var $stockDiv = document.getElementById('stockDisplay');
        if ($stockDiv && matched) {
            var stockHtml = matched.stock > 0
                ? '<span style="color:#28a745;font-weight:600;font-size:14px;"><i class="fas fa-check-circle me-1"></i>In Stock (' + matched.stock + ' left)</span>'
                : '<span style="color:#dc3545;font-weight:600;font-size:14px;"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>';
            $stockDiv.innerHTML = stockHtml;
        }

        // ── Hidden variation ID ──
        var $hiddenVar = document.getElementById('selectedVariationId');
        if ($hiddenVar) $hiddenVar.value = matched ? matched.id : '';

        // ── Selection message ──
        var $msg = document.getElementById('varSelectionMsg');
        if ($msg) {
            var totalAttrs = document.querySelectorAll('.var-attr-group').length;
            var selectedCount = Object.keys(selectedAttrs).length;
            if (selectedCount < totalAttrs) {
                $msg.textContent = 'Please select all options to add to cart.';
            } else if (!matched) {
                $msg.textContent = 'This combination is not available.';
            } else {
                $msg.textContent = '';
            }
        }

        // ── Add to cart button ──
        var $btn = document.getElementById('addToCartBtn');
        if ($btn) {
            var totalAttrCount = document.querySelectorAll('.var-attr-group').length;
            var allSelected = Object.keys(selectedAttrs).length === totalAttrCount;
            var isAvailable = matched && matched.stock > 0;
            $btn.disabled = !allSelected || !isAvailable;
            $btn.style.opacity = (!allSelected || !isAvailable) ? '0.55' : '1';
        }
    }

    // Handle clicks on option buttons
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.var-option-btn');
        if (!btn) return;

        var nameId  = btn.dataset.nameId;
        var valueId = btn.dataset.valueId;

        // Toggle within this attribute group
        var alreadySelected = btn.classList.contains('selected');

        // Deselect all others in same group
        document.querySelectorAll('.var-option-btn[data-name-id="' + nameId + '"]').forEach(function (b) {
            b.classList.remove('selected');
        });

        if (!alreadySelected) {
            btn.classList.add('selected');
            selectedAttrs[nameId] = valueId;
        } else {
            delete selectedAttrs[nameId];
        }

        updateUI();
    });

    // ── handleAddToCart with variation ──
    window.handleAddToCart = function (productId, isVariant) {
        if (!isVariant) {
            addToCartWithQty(productId);
            return;
        }
        var totalAttrCount = document.querySelectorAll('.var-attr-group').length;
        var allSelected = Object.keys(selectedAttrs).length === totalAttrCount;
        if (!allSelected) {
            if (typeof showToast === 'function') showToast('Please select all options first.', 'error');
            else alert('Please select all options first.');
            return;
        }
        var variationId = document.getElementById('selectedVariationId').value;
        if (!variationId) {
            if (typeof showToast === 'function') showToast('This combination is not available.', 'error');
            else alert('This combination is not available.');
            return;
        }
        var qty = parseInt(document.getElementById('productQty')?.value || 1);
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route("shop.cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_id: productId, variation_id: variationId, quantity: qty })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var cartCountEls = document.querySelectorAll('#cart-count, .cart-count, #cartCount, .nav-cart-count');
                cartCountEls.forEach(function (el) { el.textContent = data.cart_qty || data.cart_count || 0; });
                if (typeof showToast === 'function') showToast(data.message || 'Added to cart!', 'success');
                // ── Meta Pixel: AddToCart (variant) ──────────────
                if (typeof fbq === 'function') {
                    var contentId = data.variation_id ? String(data.variation_id) : String(productId);
                    fbq('track', 'AddToCart', {
                        content_ids:  [contentId],
                        content_name: data.product_name || '',
                        content_type: 'product',
                        num_items:    qty,
                        value:        Math.round((data.product_price || 0) / 2 * 100) / 100,
                        currency:     'BDT'
                    }, {eventID: data.atc_event_id || ''});
                }
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Error adding to cart.', 'error');
            }
        })
        .catch(function () {
            if (typeof showToast === 'function') showToast('Error adding to cart.', 'error');
        });
    };

    // ── Auto-select first value of every attribute group on page load ──
    document.querySelectorAll('.var-attr-group').forEach(function (group) {
        var firstBtn = group.querySelector('.var-option-btn');
        if (firstBtn) {
            firstBtn.classList.add('selected');
            selectedAttrs[firstBtn.dataset.nameId] = firstBtn.dataset.valueId;
        }
    });

    // Initialize UI on load
    updateUI();
})();
@else
window.handleAddToCart = function (productId) {
    addToCartWithQty(productId);
};
@endif

function copyProductLink() {
    var url = window.location.href;
    var doAfterCopy = function() {
        var icon = document.querySelector('#copyLinkBtn .share-icon');
        var lbl  = document.getElementById('copyLinkLabel');
        if (icon) icon.innerHTML = '<i class="fas fa-check"></i>';
        if (lbl)  lbl.textContent = 'Copied!';
        setTimeout(function() {
            if (icon) icon.innerHTML = '<i class="fas fa-link"></i>';
            if (lbl)  lbl.textContent = 'Copy Link';
        }, 2500);
        if (typeof showToast === 'function') showToast('Link copied to clipboard!', 'success');
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(doAfterCopy);
    } else {
        var ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.focus(); ta.select();
        document.execCommand('copy'); document.body.removeChild(ta);
        doAfterCopy();
    }
}

function shareToInstagram() {
    var url   = window.location.href;
    var title = {!! json_encode($product->name) !!};
    // Use native Web Share API on mobile if available
    if (navigator.share) {
        navigator.share({ title: title, url: url })
            .catch(function() {}); // user cancelled – no error needed
    } else {
        // Desktop fallback: copy link and inform user
        var doAfterCopy = function() {
            if (typeof showToast === 'function') {
                showToast('Link copied! Open Instagram and paste it to share.', 'success');
            }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(doAfterCopy);
        } else {
            var ta = document.createElement('textarea');
            ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.focus(); ta.select();
            document.execCommand('copy'); document.body.removeChild(ta);
            doAfterCopy();
        }
    }
}

// ── Meta Pixel: ViewContent ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'ViewContent', {
            content_ids:  [{{ $product->id }}],
            content_name:  {!! json_encode($product->name) !!},
            content_type: 'product',
            value:        {{ round((float)($product->sale_price ?? $product->price ?? 0) / 2, 2) }},
            currency:     'BDT'
        }, {eventID: '{{ $vcEventId ?? '' }}'});
    }
});
</script>
@endsection

