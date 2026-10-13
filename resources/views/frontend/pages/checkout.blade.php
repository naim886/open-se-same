@extends('frontend.layouts.app')

@section('title', 'Checkout - Open Se Same')
@section('meta_description', 'Complete your order at Open Se Same. Secure checkout with cash on delivery across Bangladesh.')
@section('og_title', 'Checkout - Open Se Same')
@section('og_description', 'Complete your order at Open Se Same. Secure checkout with cash on delivery across Bangladesh.')
@section('robots', 'noindex, nofollow')

@section('content')

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shop.cart') }}">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
    </div>
</div>

<section class="checkout-section">
    <div class="container">
        @php
            $cart = session('cart', []);
            $cartCount = count($cart);
            $cartTotalQty = array_sum(array_column($cart, 'quantity'));
            $freeEligible = $cartTotalQty >= 3;

            // location param (set by cart page) takes priority — covers Super Express (4) and Free (5)
            if (request()->has('location')) {
                $deliveryInitLocation = (int) request('location');
                $delivery = match($deliveryInitLocation) {
                    1 => 70,
                    2 => 120,
                    3 => 150,
                    4, 5 => 0,
                    default => $freeEligible ? 0 : 70,
                };
                if ($deliveryInitLocation === 5 && !$freeEligible) {
                    // not eligible for free, fall back to inside Dhaka
                    $deliveryInitLocation = 1;
                    $delivery = 70;
                }
            } elseif (request()->has('delivery')) {
                $delivery = (int) request('delivery');
                $deliveryInitLocation = match(true) {
                    $delivery === 70  => 1,
                    $delivery === 120 => 2,
                    $delivery === 150 => 3,
                    $freeEligible     => 5,
                    default           => 1,
                };
            } elseif ($freeEligible) {
                $delivery = 0;
                $deliveryInitLocation = 5;
            } else {
                $delivery = 70;
                $deliveryInitLocation = 1;
            }
            $subtotal = collect($cart)->sum(fn($i) => ($i['sale_price'] ?? $i['price']) * $i['quantity']);
            $total = $subtotal + $delivery;
        @endphp

        @if(count($cart) === 0)
            <div class="text-center py-5">
                <h5 class="text-muted">Your cart is empty.</h5>
                <a href="{{ route('shop.products') }}" class="btn-buy-now d-inline-block mt-3">Browse Products</a>
            </div>
        @else
        <form action="{{ route('shop.order.place') }}" method="POST" id="checkoutForm">
            @csrf
            <input type="hidden" name="delivery_charge" value="{{ $delivery }}" id="hiddenDelivery">
            <input type="hidden" name="product_price" value="{{ $subtotal }}">
            <input type="hidden" name="order_total" value="{{ $total }}" id="hiddenTotal">
            <input type="hidden" name="delivery_location" value="{{ $deliveryInitLocation }}" id="hiddenDeliveryLocation">

            <div class="row g-4">
                <!-- Left: Customer Info + Delivery -->
                <div class="col-lg-7">
                    <!-- Customer Info -->
                    <div class="checkout-card">
                        <h5 class="checkout-card-title"><i class="fas fa-user"></i>Your Information</h5>

                        @auth
                        @php
                            $savedAddresses = auth()->user()->addresses()->get();
                        @endphp
                        @if($savedAddresses->isNotEmpty())
                        <div class="mb-3 p-3 rounded" style="background:rgba(194,67,107,.05); border:1.5px solid rgba(194,67,107,.2);">
                            <label class="form-label" style="font-size:13px; font-weight:600; color:var(--primary);">
                                <i class="fas fa-address-book me-1"></i> Use Saved Address
                            </label>
                            <select id="savedAddressSelect" class="form-select" style="font-size:13px;"
                                    onchange="fillFromSavedAddress(this)">
                                <option value="">— Select a saved address —</option>
                                @foreach($savedAddresses as $sa)
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
                        @endif
                        @endauth

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="co_name" class="form-control" placeholder="Enter your full name"
                                       value="{{ old('customer_name', $defaultAddress?->recipient_name ?? $authUser?->name) }}" required>
                                @error('customer_name') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" id="co_phone" class="form-control" placeholder="01XXXXXXXXX"
                                       value="{{ old('customer_phone', $defaultAddress?->phone ?? $authUser?->phone) }}"
                                       pattern="[0-9]{11}" maxlength="11" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')" required>
                                @error('customer_phone') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                                <small style="font-size:11px; color:var(--text-muted);">Please use English numbers only (e.g. 01712345678)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email (Optional)</label>
                                <input type="email" name="customer_email" class="form-control" placeholder="your@email.com"
                                       value="{{ old('customer_email', $authUser?->email) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                <textarea name="customer_address" id="co_address" class="form-control" rows="3"
                                          placeholder="House no, road, area, district..." required>{{ old('customer_address', $defaultAddress?->address) }}</textarea>
                                @error('customer_address') <div class="text-danger" style="font-size:12px;">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Special Instructions (Optional)</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Any special instructions for your order...">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method -->
                    <div class="checkout-card">
                        <h5 class="checkout-card-title"><i class="fas fa-truck"></i>Delivery Method</h5>
                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <label class="delivery-option-card w-100 {{ $deliveryInitLocation == 1 ? 'active' : '' }}" id="co-inside">
                                    <input type="radio" name="_delivery_select" value="70" {{ $deliveryInitLocation == 1 ? 'checked' : '' }} onchange="setDelivery(70, 1)">
                                    <div>
                                        <div style="font-weight:700; font-size:12.5px;">Inside Dhaka</div>
                                        <div style="font-size:11px; color:var(--text-muted);">1–2 days</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--primary-dark);">৳70</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="delivery-option-card w-100 {{ $deliveryInitLocation == 2 ? 'active' : '' }}" id="co-outside">
                                    <input type="radio" name="_delivery_select" value="120" {{ $deliveryInitLocation == 2 ? 'checked' : '' }} onchange="setDelivery(120, 2)">
                                    <div>
                                        <div style="font-weight:700; font-size:12.5px;">Outside Dhaka</div>
                                        <div style="font-size:11px; color:var(--text-muted);">2–5 days</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--primary-dark);">৳120</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="delivery-option-card w-100 {{ $deliveryInitLocation == 3 ? 'active' : '' }}" id="co-express">
                                    <input type="radio" name="_delivery_select" value="150" {{ $deliveryInitLocation == 3 ? 'checked' : '' }} onchange="setDelivery(150, 3)">
                                    <div>
                                        <div style="font-weight:700; font-size:12px; display:flex; align-items:center; gap:3px;">
                                            <i class="fas fa-bolt" style="color:var(--primary-dark);"></i> Express
                                            <span style="background:var(--primary-gradient); color:#fff; font-size:9px; padding:1px 5px; border-radius:8px;">FAST</span>
                                        </div>
                                        <div style="font-size:11px; color:var(--text-muted);">ঢাকা / ১১টার আগে</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--primary-dark);">৳150</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="delivery-option-card w-100 {{ $deliveryInitLocation == 4 ? 'active' : '' }}" id="co-super-express">
                                    <input type="radio" name="_delivery_select" value="se" {{ $deliveryInitLocation == 4 ? 'checked' : '' }} onchange="setDelivery(0, 4)">
                                    <div>
                                        <div style="font-weight:700; font-size:12px; display:flex; align-items:center; gap:3px;">
                                            <i class="fas fa-rocket" style="color:var(--primary-dark);"></i> Super Express
                                            <span style="background:var(--primary-gradient); color:#fff; font-size:9px; padding:1px 5px; border-radius:8px;">URGENT</span>
                                        </div>
                                        <div style="font-size:11px; color:var(--text-muted);">ঢাকা / চার্জ কুরিয়ারকে</div>
                                        <div style="font-size:12px; font-weight:700; color:var(--primary-dark);">কুরিয়ারকে</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="delivery-option-card w-100 {{ $freeEligible && $deliveryInitLocation == 5 ? 'active' : '' }}"
                                       id="co-free"
                                       style="{{ !$freeEligible ? 'opacity:0.55; cursor:not-allowed;' : '' }}">
                                    <input type="radio" name="_delivery_select" value="free"
                                           {{ $freeEligible && $deliveryInitLocation == 5 ? 'checked' : '' }}
                                           {{ !$freeEligible ? 'disabled' : '' }}
                                           onchange="setDelivery(0, 5)">
                                    <div>
                                        <div style="font-weight:700; font-size:12px; display:flex; align-items:center; gap:3px;">
                                            <i class="fas fa-gift" style="color:#28a745;"></i> Free Delivery
                                        </div>
                                        <div style="font-size:10.5px; color:var(--text-muted); line-height:1.4;">
                                            @if($freeEligible)
                                                ✅ আপনার কার্টে {{ $cartTotalQty }}টি পণ্য!
                                            @else
                                                ৩টি প্রোডাক্ট অর্ডার করলে ডেলিভারি চার্জ সম্পূর্ণ ফ্রী।
                                            @endif
                                        </div>
                                        <div style="font-size:13px; font-weight:700; color:#28a745;">ফ্রি!</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        {{-- Delivery Notice --}}
                        <div id="co-delivery-notice" class="mt-3 p-2 rounded"
                             style="{{ in_array($deliveryInitLocation, [3,4]) ? '' : 'display:none;' }} background:rgba(107,0,2,0.06); border:1px solid rgba(107,0,2,0.2); font-size:13px; color:var(--text-dark); line-height:1.7;">
                            @if($deliveryInitLocation == 3)
                                <i class="fas fa-info-circle me-1" style="color:var(--primary-dark);"></i> আপনি যদি সকাল ১১টার মধ্যে এক্সপ্রেস অর্ডার প্লেস না করেন তাহলে সেটা রেগুলার ডেলিভারিতে যাবে। বিঃ দ্রঃ - এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।
                            @elseif($deliveryInitLocation == 4)
                                <i class="fas fa-info-circle me-1" style="color:var(--primary-dark);"></i> সুপার এক্সপ্রেস ডেলিভারিতে ডেলিভারি চার্জ যেটা আসবে সেটা আপনাকে পে করতে হবে ডেলিভারিম্যান কে।&nbsp; এবং প্রোডাক্টের মূল্য আগে পরিশোধ করতে হবে।&nbsp; বিঃ দ্রঃ - সুপার এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।
                            @endif
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <h5 class="checkout-card-title"><i class="fas fa-credit-card"></i>Payment Method</h5>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="p-3 rounded d-flex align-items-center gap-3" style="background:rgba(40,167,69,0.08); border:2px solid #28a745;">
                                    <i class="fas fa-money-bill-wave" style="font-size:24px; color:#28a745; flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-weight:700; font-size:14px;">Cash on Delivery</div>
                                        <div style="font-size:12px; color:var(--text-muted);">Pay when you receive your order</div>
                                    </div>
                                    <i class="fas fa-check-circle text-success ms-auto" style="font-size:18px;"></i>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded d-flex align-items-center gap-3" style="background:#f8f8f8; border:1.5px solid #ddd; opacity:0.55; cursor:not-allowed;" title="Coming soon">
                                    <i class="fas fa-credit-card" style="font-size:24px; color:#0d6efd; flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-weight:700; font-size:14px;">Online Payment</div>
                                        <div style="font-size:12px; color:var(--text-muted);">Card, Mobile Banking &amp; more — Coming soon</div>
                                    </div>
                                    <span class="ms-auto" style="font-size:11px; background:#ffc107; color:#333; padding:2px 8px; border-radius:10px; font-weight:700;">Soon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="col-lg-5">
                    <div class="cart-summary-card" style="position:sticky; top:90px;">
                        <h5 class="cart-summary-title">Order Summary</h5>

                        <div class="order-summary-items">
                            @foreach($cart as $productId => $item)
                            @php $itemPrice = $item['sale_price'] ?? $item['price']; @endphp
                            <div class="order-item-row">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="order-item-img">
                                @else
                                    <div class="order-item-img d-flex align-items-center justify-content-center" style="background:#f5f5f5;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                                <div class="order-item-name">
                                    {{ Str::limit($item['name'], 35) }}
                                    @if(!empty($item['variation_label']))
                                        <div style="font-size:11px; color:var(--text-muted);">{{ $item['variation_label'] }}</div>
                                    @endif
                                    <div style="font-size:12px; color:var(--text-muted);">× {{ $item['quantity'] }}</div>
                                </div>
                                <div class="order-item-price">৳{{ number_format($itemPrice * $item['quantity'], 0) }}</div>
                            </div>
                            @endforeach
                        </div>

                        <hr class="cart-summary-divider">
                        <div class="cart-summary-row">
                            <span>Products Total:</span>
                            <span class="fw-bold">৳{{ number_format($subtotal, 0) }}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Delivery Charge:</span>
                            <span id="coSummaryDelivery">৳{{ number_format($delivery, 0) }}</span>
                        </div>
                        <hr class="cart-summary-divider">
                        <div class="cart-summary-row">
                            <span class="fw-bold fs-6">Grand Total:</span>
                            <span class="cart-summary-total" id="coSummaryTotal">৳{{ number_format($total, 0) }}</span>
                        </div>

                        <button type="submit" class="btn-place-order mt-3">
                            <i class="fas fa-check-circle me-2"></i>Confirm Order
                        </button>
                        <p class="text-center text-muted mt-2" style="font-size:12px;">
                            <i class="fas fa-lock me-1"></i>Your information is fully secure
                        </p>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>
</section>
@endsection

@section('extra_js')
<script>
var baseSubtotal = {{ $subtotal ?? 0 }};

function fillFromSavedAddress(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    var n = document.getElementById('co_name');
    var p = document.getElementById('co_phone');
    var a = document.getElementById('co_address');
    if (n) n.value = opt.dataset.name    || '';
    if (p) p.value = opt.dataset.phone   || '';
    if (a) a.value = opt.dataset.address || '';
}

// Keep select in sync after PHP pre-fill (cosmetic)
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('savedAddressSelect');
    if (sel) sel.dispatchEvent(new Event('change'));
});

var coDeliveryNotices = {
    3: 'আপনি যদি সকাল ১১টার মধ্যে এক্সপ্রেস অর্ডার প্লেস না করেন তাহলে সেটা রেগুলার ডেলিভারিতে যাবে। বিঃ দ্রঃ - এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।',
    4: 'সুপার এক্সপ্রেস ডেলিভারিতে ডেলিভারি চার্জ যেটা আসবে সেটা আপনাকে পে করতে হবে ডেলিভারিম্যান কে।  এবং প্রোডাক্টের মূল্য আগে পরিশোধ করতে হবে।  বিঃ দ্রঃ - সুপার এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।'
};

function setDelivery(charge, locationVal) {
    var hd  = document.getElementById('hiddenDelivery');
    var ht  = document.getElementById('hiddenTotal');
    var hlo = document.getElementById('hiddenDeliveryLocation');
    var sd  = document.getElementById('coSummaryDelivery');
    var st  = document.getElementById('coSummaryTotal');

    if (hd)  hd.value  = charge;
    if (ht)  ht.value  = baseSubtotal + charge;
    if (hlo) hlo.value = locationVal;

    // Delivery label in summary
    if (sd) {
        if (locationVal === 5) sd.textContent = 'ফ্রি';
        else if (locationVal === 4) sd.textContent = 'কুরিয়ারকে পেমেন্ট';
        else sd.textContent = '৳' + charge;
    }
    if (st) st.textContent = '৳' + (baseSubtotal + charge).toLocaleString();

    ['co-inside','co-outside','co-express','co-super-express','co-free'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });

    var idMap = {1:'co-inside', 2:'co-outside', 3:'co-express', 4:'co-super-express', 5:'co-free'};
    var activeEl = document.getElementById(idMap[locationVal]);
    if (activeEl) activeEl.classList.add('active');

    // Show / hide notice
    var notice = document.getElementById('co-delivery-notice');
    if (notice) {
        if (coDeliveryNotices[locationVal]) {
            notice.innerHTML = '<i class="fas fa-info-circle me-1" style="color:var(--primary-dark);"></i> ' + coDeliveryNotices[locationVal];
            notice.style.display = '';
        } else {
            notice.innerHTML = '';
            notice.style.display = 'none';
        }
    }
}

@php
    $fbCoCart  = session('cart', []);
    $fbCoIds   = array_values(array_map(fn($i) => (string) ($i['variation_id'] ?? $i['product_id'] ?? 0), $fbCoCart));
    $fbCoValue = round((float) collect($fbCoCart)->sum(fn($i) => ($i['sale_price'] ?? $i['price']) * $i['quantity']) / 2, 2);
    $fbCoItems = (int)   array_sum(array_column($fbCoCart, 'quantity'));
    $pixelIp   = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
    $pixelUa   = request()->userAgent() ?? '';
@endphp
// ── Meta Pixel: InitiateCheckout ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'InitiateCheckout', {
            content_ids: {!! json_encode($fbCoIds) !!},
            content_type: 'product',
            num_items: {{ $fbCoItems }},
            value: {{ $fbCoValue }},
            currency: 'BDT'
        }, {eventID: '{{ $icEventId ?? '' }}'});
    }
    // AddPaymentInfo when user confirms the order
    var coForm = document.getElementById('checkoutForm');
    if (coForm) {
        coForm.addEventListener('submit', function () {
            if (typeof fbq === 'function') {
                var rawName  = (document.querySelector('[name="customer_name"]')?.value || '').trim();
                var rawPhone = (document.querySelector('[name="customer_phone"]')?.value || '').replace(/\D/g, '');
                var rawEmail = (document.querySelector('[name="customer_email"]')?.value || '').trim().toLowerCase();
                var nameParts = rawName.split(' ');
                var userData = {
                    client_ip_address: {!! json_encode($pixelIp) !!},
                    client_user_agent: {!! json_encode($pixelUa) !!}
                };
                if (rawPhone) userData.ph = rawPhone;
                if (rawEmail) userData.em = rawEmail;
                if (nameParts[0]) userData.fn = nameParts[0].toLowerCase();
                if (nameParts[1]) userData.ln = nameParts.slice(1).join(' ').toLowerCase();
                fbq('init', '26065580893095421', userData);
                fbq('track', 'AddPaymentInfo', {
                    content_ids: {!! json_encode($fbCoIds) !!},
                    content_type: 'product',
                    num_items: {{ $fbCoItems }},
                    value: {{ $fbCoValue }},
                    currency: 'BDT'
                });
            }
        });
    }
});</script>
@endsection
