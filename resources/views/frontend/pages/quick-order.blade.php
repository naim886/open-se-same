@extends('frontend.layouts.app')

@section('extra_css')
<style>
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
.var-option-btn:hover { border-color: var(--primary); color: var(--primary); }
.var-option-btn.selected { border-color: var(--primary); background: var(--primary); color: #fff; }
</style>
@endsection

@section('title', 'Quick Order - '.$product->name.' - Open Se Same')
@section('meta_description', 'Quick order '.$product->name.' at Open Se Same. Fast checkout with same-day delivery in Dhaka. COD available.')
@section('og_title', 'Quick Order - '.$product->name.' - Open Se Same')
@section('og_description', 'Quick order '.$product->name.' at Open Se Same. Fast checkout with same-day delivery in Dhaka.')
@section('og_image', $product->primary_photo ? asset('storage/'.$product->primary_photo) : asset('images/og.webp'))
@section('robots', 'noindex, nofollow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
$schemaPrice = (float)($product->sale_price ?? $product->price ?? 0);
echo Schema::product()
    ->name($product->name)
    ->description(strip_tags($product->short_description ?? ''))
    ->image($product->primary_photo ? asset('storage/'.$product->primary_photo) : asset('images/og.webp'))
    ->url(route('shop.product', $product->slug))
    ->sku((string) $product->id)
    ->brand(Schema::brand()->name('Open Se Same'))
    ->offers(
        Schema::offer()
            ->price($schemaPrice)
            ->priceCurrency('BDT')
            ->availability($product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock')
            ->url(route('shop.quick-order', $product->slug))
            ->seller(Schema::organization()->name('Open Se Same')->url(url('/')))
            ->priceValidUntil(now()->addYear()->format('Y-m-d'))
    )
    ->toScript();
@endphp
@endsection

@section('content')
@php
    $deliveryCosts = \App\Models\Order::deliveryCostList();
    $dcInside      = $deliveryCosts[\App\Models\Order::DELIVERY_INSIDE_DHAKA] ?? 0;
    $dcOutside     = $deliveryCosts[\App\Models\Order::DELIVERY_OUTSIDE_DHAKA] ?? 0;
@endphp

<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.product', $product->slug) }}">{{ Str::limit($product->name, 30) }}</a></li>
                <li class="breadcrumb-item active">Quick Order</li>
            </ol>
        </nav>
    </div>
</div>

<section class="checkout-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row g-4">
                    <!-- Product Summary -->
                    <div class="col-md-4">
                        <div class="checkout-card text-center">
                            @php
                                $img = $product->primary_photo
                                    ? asset('storage/'.$product->primary_photo)
                                    : ($product->photos->first() ? asset('storage/'.$product->photos->first()->photo_path) : null);
                            @endphp
                            @if($img)
                                <img id="quickOrderMainImg" src="{{ $img }}" alt="{{ $product->name }}" class="rounded mb-3" style="width:100%; aspect-ratio:1/1; object-fit:cover; border:1px solid var(--border);">
                            @endif
                            <h6 class="fw-bold mb-2" style="font-size:15px;">{{ $product->name }}</h6>
                            <div class="mb-2" id="qoPriceDisplay">
                                @if($product->isVariantProduct() && ($qoVarRange = $product->priceRange))
                                    @if($qoVarRange['min'] == $qoVarRange['max'])
                                        <span style="font-size:22px; font-weight:800; background:var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">৳{{ number_format($qoVarRange['min'], 0) }}</span>
                                    @else
                                        <span style="font-size:22px; font-weight:800; background:var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">৳{{ number_format($qoVarRange['min'], 0) }}–{{ number_format($qoVarRange['max'], 0) }}</span>
                                    @endif
                                @else
                                    <span style="font-size:22px; font-weight:800; background:var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
                                        ৳{{ number_format($product->sale_price ?? $product->price, 0) }}
                                    </span>
                                    @if($product->sale_price && $product->price > $product->sale_price)
                                        <span class="text-muted text-decoration-line-through ms-2" style="font-size:15px;">৳{{ number_format($product->price, 0) }}</span>
                                    @endif
                                @endif
                            </div>
                            @if($product->stock !== null)
                                <small class="{{ $product->stock > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $product->stock > 0 ? '✓ In Stock' : '✗ Out of Stock' }}
                                </small>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Order Form -->
                    <div class="col-md-8">
                        <div class="checkout-card">
                            <h5 class="checkout-card-title"><i class="fas fa-bolt"></i>Quick Order</h5>

                            <form action="{{ route('shop.order.quick') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="product_price" id="productPriceHidden" value="{{ $product->isVariantProduct() ? 0 : ($product->sale_price ?? $product->price) }}">
                                <input type="hidden" name="delivery_charge" id="deliveryHidden" value="{{ $dcInside }}">
                                <input type="hidden" name="delivery_location" id="deliveryLocationHidden" value="1">
                                <input type="hidden" name="order_total" id="orderTotalHidden" value="{{ $product->isVariantProduct() ? $dcInside : (($product->sale_price ?? $product->price) + $dcInside) }}">
                                @if($product->isVariantProduct())
                                <input type="hidden" name="variation_id" id="variationIdHidden" value="">
                                @endif

                                @if($product->isVariantProduct() && $product->activeVariations->count() > 0)
                                @php
                                    $qoGroupedAttrs = [];
                                    foreach ($product->activeVariations as $qoVar) {
                                        foreach ($qoVar->variationAttributes as $va) {
                                            $nid = $va->attribute_name_id;
                                            $vid = $va->attribute_value_id;
                                            if (!isset($qoGroupedAttrs[$nid])) {
                                                $qoGroupedAttrs[$nid] = ['name' => $va->attributeName?->name ?? '', 'values' => []];
                                            }
                                            $qoGroupedAttrs[$nid]['values'][$vid] = $va->attributeValue?->name ?? '';
                                        }
                                    }
                                @endphp
                                <div class="mb-3 p-3 rounded" style="border:1px solid var(--border);">
                                    <div class="fw-bold mb-2" style="font-size:14px;"><i class="fas fa-palette me-1"></i>Select Options <span class="text-danger">*</span></div>
                                    @foreach($qoGroupedAttrs as $qoNameId => $qoAttrGroup)
                                    <div class="mb-2 qo-var-attr-group" data-name-id="{{ $qoNameId }}">
                                        <label class="d-block mb-1" style="font-size:13px; font-weight:600;">{{ $qoAttrGroup['name'] }}:</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($qoAttrGroup['values'] as $qoValueId => $qoValueName)
                                            <button type="button"
                                                    class="var-option-btn qo-var-btn"
                                                    data-name-id="{{ $qoNameId }}"
                                                    data-value-id="{{ $qoValueId }}">
                                                {{ $qoValueName }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                    <div id="qoVarMsg" class="mt-1" style="font-size:12px; color:#dc3545;"></div>
                                </div>
                                @endif

                                <div class="row g-3">
                                    @auth
                                    @php $qoAddresses = auth()->user()->addresses()->get(); @endphp
                                    @if($qoAddresses->isNotEmpty())
                                    <div class="col-12">
                                        <div class="p-3 rounded mb-1" style="background:rgba(2,123,80,.05); border:1.5px solid rgba(2,123,80,.2);">
                                            <label class="form-label mb-1" style="font-size:13px; font-weight:600; color:var(--primary);">
                                                <i class="fas fa-address-book me-1"></i> Use Saved Address
                                            </label>
                                            <select id="qoSavedAddr" class="form-select" style="font-size:13px;"
                                                    onchange="qoFillAddress(this)">
                                                <option value="">— Select saved address —</option>
                                                @foreach($qoAddresses as $sa)
                                                <option value="{{ $sa->id }}"
                                                        data-name="{{ $sa->recipient_name }}"
                                                        data-phone="{{ $sa->phone }}"
                                                        data-address="{{ $sa->address }}"
                                                        {{ $sa->is_default ? 'selected' : '' }}>
                                                    {{ $sa->label }}: {{ $sa->recipient_name }}, {{ Str::limit($sa->address, 40) }}
                                                    {{ $sa->is_default ? '(Default)' : '' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                    @endauth

                                    <div class="col-12">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" id="qo_name" class="form-control" placeholder="Enter your full name"
                                               value="{{ old('customer_name', $defaultAddress?->recipient_name ?? $authUser?->name) }}" required>
                                        @error('customer_name') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" name="customer_phone" id="qo_phone" class="form-control" placeholder="01XXXXXXXXX"
                                               value="{{ old('customer_phone', $defaultAddress?->phone ?? $authUser?->phone) }}"
                                               pattern="[0-9]{11}" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                                        @error('customer_phone') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                                        <small style="font-size:11px; color:var(--text-muted);">Please use English numbers only (e.g. 01712345678)</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <div class="qty-control">
                                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                                            <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="{{ $product->stock > 0 ? $product->stock : 99 }}" class="qty-input" onchange="recalcTotal()">
                                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                        <textarea name="customer_address" id="qo_address" class="form-control" rows="3" placeholder="House no, road, area, district..." required>{{ old('customer_address', $defaultAddress?->address) }}</textarea>
                                        @error('customer_address') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold">Delivery Method <span class="text-danger">*</span></label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="delivery-option-card w-100 active" id="qo-inside">
                                                    <input type="radio" name="_qo_delivery" value="{{ $dcInside }}" checked onchange="setQODelivery({{ $dcInside }}, 1)">
                                                    <div>
                                                        <i class="fas fa-location-dot delivery-option-icon"></i>
                                                        <div style="font-weight:700; font-size:12px;">Inside Dhaka</div>
                                                        <div style="font-size:11px; color:var(--primary-dark);">৳{{ number_format($dcInside, 0) }}</div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <label class="delivery-option-card w-100" id="qo-outside">
                                                    <input type="radio" name="_qo_delivery" value="{{ $dcOutside }}" onchange="setQODelivery({{ $dcOutside }}, 2)">
                                                    <div>
                                                        <i class="fas fa-truck-fast delivery-option-icon"></i>
                                                        <div style="font-weight:700; font-size:12px;">Outside Dhaka</div>
                                                        <div style="font-size:11px; color:var(--primary-dark);">৳{{ number_format($dcOutside, 0) }}</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold">Coupon Code</label>
                                        <input type="hidden" name="coupon_code" id="qoCouponHidden" value="">
                                        <div class="d-flex gap-2">
                                            <input type="text" id="qoCouponInput" class="form-control text-uppercase"
                                                   placeholder="Enter code" autocomplete="off">
                                            <button type="button" class="btn btn-outline-primary text-nowrap"
                                                    onclick="applyQoCoupon()">Apply</button>
                                        </div>
                                        <div id="qoCouponMessage" class="mt-1" style="font-size:12px;"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Special Instructions (Optional)</label>
                                        <textarea name="note" class="form-control" rows="2" placeholder="Any special instructions...">{{ old('note') }}</textarea>
                                    </div>

                                    <!-- Total -->
                                    <div class="col-12">
                                        <div class="p-3 rounded" style="background:var(--bg-light); border:1px solid var(--border);">
                                            <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                                                <span>Product Price:</span>
                                                <span class="fw-bold">৳<span id="productPriceShow">{{ number_format($product->sale_price ?? $product->price, 0) }}</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                                                <span>Delivery:</span>
                                                <span id="deliveryShow">৳{{ number_format($dcInside, 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 d-none" style="font-size:14px;" id="qoDiscountRow">
                                                <span>Discount <span id="qoCouponCode" class="text-muted"></span>:</span>
                                                <span class="text-success" id="qoDiscountShow">৳0</span>
                                            </div>
                                            <hr style="margin:8px 0; border-color:var(--border);">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">Grand Total:</span>
                                                <span class="fw-bold" style="font-size:18px; background:var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">৳<span id="totalShow">{{ number_format(($product->sale_price ?? $product->price) + $dcInside, 0) }}</span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Payment Method</label>
                                        <div class="payment-method-list">
                                            <label class="payment-method-card active">
                                                <input type="radio" name="payment_method" value="cod" checked>
                                                <div class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></div>
                                                <div class="payment-method-text">
                                                    <div class="payment-method-title">Cash on Delivery</div>
                                                    <div class="payment-method-sub">Pay when you receive</div>
                                                </div>
                                                <i class="fas fa-check-circle payment-method-check"></i>
                                            </label>
                                            <label class="payment-method-card disabled" title="Coming soon">
                                                <input type="radio" name="payment_method" value="online" disabled>
                                                <div class="payment-method-icon"><i class="fas fa-credit-card"></i></div>
                                                <div class="payment-method-text">
                                                    <div class="payment-method-title">Online Payment</div>
                                                    <div class="payment-method-sub">Card, bKash, Nagad &amp; more</div>
                                                </div>
                                                <span class="payment-method-badge">Coming Soon</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn-place-order" id="qoSubmitBtn"{{ $product->isVariantProduct() ? ' disabled style="opacity:0.55;"' : '' }}>
                                            <i class="fas fa-check-circle me-2"></i>Confirm Order
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('extra_js')
<script>
var deliveryCharge = {{ $dcInside }};
var couponDiscount = 0;

function qoSubtotal() {
    var input = document.getElementById('qtyInput');
    var qty = input ? (parseInt(input.value) || 1) : 1;
    return unitPrice * qty;
}

function applyQoCoupon() {
    var input  = document.getElementById('qoCouponInput');
    var msg    = document.getElementById('qoCouponMessage');
    var hidden = document.getElementById('qoCouponHidden');
    var row    = document.getElementById('qoDiscountRow');
    var code   = input ? input.value.trim() : '';

    if (!code) {
        couponDiscount = 0;
        if (hidden) hidden.value = '';
        if (row) row.classList.add('d-none');
        if (msg) { msg.textContent = 'Please enter a coupon code.'; msg.className = 'mt-1 text-danger'; }
        recalcTotal();
        return;
    }

    var phoneEl = document.getElementById('qo_phone');
    var locEl   = document.getElementById('deliveryLocationHidden');

    var body = new URLSearchParams({
        code:              code,
        subtotal:          String(qoSubtotal()),
        delivery_location: String(locEl ? locEl.value : 1),
        phone:             phoneEl ? phoneEl.value : ''
    });

    fetch('{{ route('shop.coupon.validate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: body.toString()
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.ok) {
            couponDiscount = parseFloat(data.discount) || 0;
            if (hidden) hidden.value = data.code || code;
            if (row) row.classList.remove('d-none');

            var dEl = document.getElementById('qoDiscountShow');
            var cEl = document.getElementById('qoCouponCode');
            if (dEl) dEl.textContent = '৳' + couponDiscount.toLocaleString();
            if (cEl) cEl.textContent = '(' + (data.code || code) + ')';
            if (msg) { msg.textContent = data.message; msg.className = 'mt-1 text-success'; }
        } else {
            couponDiscount = 0;
            if (hidden) hidden.value = '';
            if (row) row.classList.add('d-none');
            if (msg) { msg.textContent = data.message || 'Invalid coupon code.'; msg.className = 'mt-1 text-danger'; }
        }
        recalcTotal();
    })
    .catch(function () {
        couponDiscount = 0;
        if (hidden) hidden.value = '';
        if (row) row.classList.add('d-none');
        if (msg) { msg.textContent = 'Could not validate the coupon. Please try again.'; msg.className = 'mt-1 text-danger'; }
        recalcTotal();
    });
}

function qoFillAddress(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    var n = document.getElementById('qo_name');
    var p = document.getElementById('qo_phone');
    var a = document.getElementById('qo_address');
    if (n) n.value = opt.dataset.name    || '';
    if (p) p.value = opt.dataset.phone   || '';
    if (a) a.value = opt.dataset.address || '';
}

// Auto-fill from default on load
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('qoSavedAddr');
    if (sel && sel.value) qoFillAddress(sel);
});

@if($product->isVariantProduct() && $product->activeVariations->count() > 0)
var variations = @json($product->activeVariations->map->toFrontendArray()->values());
var qoSelectedAttrs = {};
var unitPrice = 0;

function qoFindMatchingVariation() {
    return variations.find(function (v) {
        if (v.attributes.length !== Object.keys(qoSelectedAttrs).length) return false;
        return v.attributes.every(function (a) {
            return String(qoSelectedAttrs[a.name_id]) === String(a.value_id);
        });
    }) || null;
}

function qoFindPartialMatch() {
    var keys = Object.keys(qoSelectedAttrs);
    if (keys.length === 0) return null;
    return variations.find(function (v) {
        return keys.every(function (nameId) {
            return v.attributes.some(function (a) {
                return String(a.name_id) === String(nameId) &&
                       String(a.value_id) === String(qoSelectedAttrs[nameId]);
            });
        });
    }) || null;
}

function updateQOVariationUI() {
    var matched = qoFindMatchingVariation();

    if (matched) {
        unitPrice = matched.sale_price ? matched.sale_price : matched.price;
        var priceEl = document.querySelector('#qoPriceDisplay span');
        if (priceEl) {
            priceEl.style.webkitTextFillColor = 'transparent';
            priceEl.textContent = '৳' + Math.round(unitPrice).toLocaleString('en-BD');
        }
        var vh = document.getElementById('variationIdHidden');
        if (vh) vh.value = matched.id;
    } else {
        unitPrice = 0;
        var vh = document.getElementById('variationIdHidden');
        if (vh) vh.value = '';
    }

    // Image swap on partial match
    var imgSrc = (matched && matched.image_url) ? matched : qoFindPartialMatch();
    if (imgSrc && imgSrc.image_url) {
        var mainImg = document.getElementById('quickOrderMainImg');
        if (mainImg) mainImg.src = imgSrc.image_url;
    }

    // Message + submit button
    var totalAttrs = document.querySelectorAll('.qo-var-attr-group').length;
    var selCount   = Object.keys(qoSelectedAttrs).length;
    var $msg = document.getElementById('qoVarMsg');
    var $btn = document.getElementById('qoSubmitBtn');
    var ok   = matched && matched.stock > 0;
    if (selCount < totalAttrs) {
        if ($msg) $msg.textContent = 'Please select all options to continue.';
        if ($btn) { $btn.disabled = true; $btn.style.opacity = '0.55'; }
    } else if (!matched) {
        if ($msg) $msg.textContent = 'This combination is not available.';
        if ($btn) { $btn.disabled = true; $btn.style.opacity = '0.55'; }
    } else if (!ok) {
        if ($msg) $msg.textContent = 'Selected combination is out of stock.';
        if ($btn) { $btn.disabled = true; $btn.style.opacity = '0.55'; }
    } else {
        if ($msg) $msg.textContent = '';
        if ($btn) { $btn.disabled = false; $btn.style.opacity = '1'; }
    }
    recalcTotal();
}

document.addEventListener('click', function (e) {
    var btn = e.target.closest('.qo-var-btn');
    if (!btn) return;
    var nameId  = btn.dataset.nameId;
    var valueId = btn.dataset.valueId;
    var alreadySel = btn.classList.contains('selected');
    document.querySelectorAll('.qo-var-btn[data-name-id="' + nameId + '"]').forEach(function (b) {
        b.classList.remove('selected');
    });
    if (!alreadySel) {
        btn.classList.add('selected');
        qoSelectedAttrs[nameId] = valueId;
    } else {
        delete qoSelectedAttrs[nameId];
    }
    updateQOVariationUI();
});

// Auto-select first value in every attribute group
document.querySelectorAll('.qo-var-attr-group').forEach(function (group) {
    var first = group.querySelector('.qo-var-btn');
    if (first) {
        first.classList.add('selected');
        qoSelectedAttrs[first.dataset.nameId] = first.dataset.valueId;
    }
});
updateQOVariationUI();
@else
var unitPrice = {{ (float)($product->sale_price ?? $product->price ?? 0) }};
@endif

function changeQty(delta) {
    var input = document.getElementById('qtyInput');
    if (!input) return;
    var val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    var maxVal = parseInt(input.max);
    if (maxVal && val > maxVal) val = maxVal;
    input.value = val;
    recalcTotal();
}

function setQODelivery(charge, locationVal) {
    deliveryCharge = charge;
    var dh  = document.getElementById('deliveryHidden');
    var dlh = document.getElementById('deliveryLocationHidden');
    var ds  = document.getElementById('deliveryShow');
    if (dh)  dh.value  = charge;
    if (dlh) dlh.value = locationVal;
    if (ds)  ds.textContent = '৳' + charge;

    ['qo-inside','qo-outside'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });
    var idMap = {1:'qo-inside', 2:'qo-outside'};
    var activeEl = document.getElementById(idMap[locationVal]);
    if (activeEl) activeEl.classList.add('active');

    recalcTotal();
}

function recalcTotal() {
    var input = document.getElementById('qtyInput');
    var qty = input ? (parseInt(input.value) || 1) : 1;
    var productTotal = unitPrice * qty;
    var total = Math.max(0, productTotal + deliveryCharge - couponDiscount);

    var ps = document.getElementById('productPriceShow');
    var ts = document.getElementById('totalShow');
    var ph = document.getElementById('productPriceHidden');
    var oh = document.getElementById('orderTotalHidden');

    if (ps) ps.textContent = productTotal.toLocaleString();
    if (ts) ts.textContent = total.toLocaleString();
    if (ph) ph.value = productTotal;
    if (oh) oh.value = total;
}

@php
    $pixelIp = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
    $pixelUa = request()->userAgent() ?? '';
@endphp
// ── Meta Pixel: InitiateCheckout on load (no PageView, no ViewContent) ──
// Purchase fires on order-success page after the server confirms the order.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'InitiateCheckout', {
            content_ids:  ['{{ $product->id }}'],
            content_name:  {!! json_encode($product->name) !!},
            content_type: 'product',
            num_items:    parseInt(document.getElementById('qtyInput')?.value || 1),
            value:        Math.round(parseFloat(document.getElementById('productPriceHidden')?.value || {{ (float)($product->sale_price ?? $product->price ?? 0) }}) / 2 * 100) / 100,
            currency:     'BDT'
        }, {eventID: '{{ $icEventId ?? '' }}'});
    }
});
</script>
@endsection

