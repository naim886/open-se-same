@extends('frontend.layouts.app')

@section('title', 'Shopping Cart - Open Se Same')
@section('meta_description', 'Review your shopping cart at Open Se Same. Fast checkout with COD and delivery across Bangladesh.')
@section('og_title', 'Shopping Cart - Open Se Same')
@section('og_description', 'Review your shopping cart at Open Se Same. Fast checkout with COD and delivery across Bangladesh.')
@section('robots', 'noindex, nofollow')

@section('content')

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item active">Shopping Cart</li>
            </ol>
        </nav>
    </div>
</div>

<section class="cart-section">
    <div class="container">
        @php
            $cart = session('cart', []);
            $cartItemCount = array_sum(array_column($cart, 'quantity'));
            $cartFreeEligible = $cartItemCount >= 3;
        @endphp

        @if(count($cart) === 0)
            <div class="empty-cart">
                <div class="empty-cart-icon"><i class="fas fa-shopping-cart"></i></div>
                <h4 class="fw-bold mb-2">Your Cart is Empty</h4>
                <p class="text-muted mb-4">You haven't added any products yet. Browse our store and find something you love!</p>
                <a href="{{ route('shop.products') }}" class="btn-buy-now d-inline-block">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        @else
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">{{ count($cart) }} item(s) in cart</h5>
                        <form action="{{ route('shop.cart.clear') }}" method="POST" onsubmit="return confirm('Clear your entire cart?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                    <div class="cart-table-wrapper">
                        <table class="table cart-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center d-none-mobile">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @foreach($cart as $productId => $item)
                                @php
                                    $itemPrice = $item['sale_price'] ?? $item['price'];
                                    $itemTotal = $itemPrice * $item['quantity'];
                                    $subtotal += $itemTotal;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item['image'])
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="cart-product-img">
                                            @else
                                                <div class="cart-product-img d-flex align-items-center justify-content-center" style="background:#f5f5f5;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('shop.product', $item['slug']) }}" class="cart-product-name d-block">{{ $item['name'] }}</a>
                                                @if(!empty($item['variation_label']))
                                                    <small class="text-muted d-block" style="font-size:11.5px; margin-top:2px;">{{ $item['variation_label'] }}</small>
                                                @endif
                                                @if(isset($item['category'])) <small class="text-muted">{{ $item['category'] }}</small> @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center d-none-mobile">৳{{ number_format($itemPrice, 0) }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('shop.cart.update', $productId) }}" method="POST" class="d-flex align-items-center justify-content-center">
                                            @csrf @method('PATCH')
                                            <div class="qty-control" style="transform:scale(0.85);">
                                                <button type="button" class="qty-btn" onclick="var i=this.closest('form').querySelector('input[name=quantity]');var v=parseInt(i.value)-1;if(v>=1){i.value=v;this.closest('form').submit();}">−</button>
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" step="1" class="qty-input" onchange="if(parseInt(this.value)>=1)this.closest('form').submit()">
                                                <button type="button" class="qty-btn" onclick="var i=this.closest('form').querySelector('input[name=quantity]');i.value=parseInt(i.value)+1;this.closest('form').submit();">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold" style="color:var(--primary);">৳{{ number_format($itemTotal, 0) }}</td>
                                    <td>
                                        <form action="{{ route('shop.cart.remove', $productId) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="cart-remove-btn" title="Remove"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('shop.products') }}" style="color:var(--primary-dark); font-size:14px; font-weight:600;">
                            <i class="fas fa-arrow-left me-1"></i>Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h5 class="cart-summary-title">Order Summary</h5>

                        <div class="cart-summary-row">
                            <span>Products Total:</span>
                            <span class="fw-bold">৳{{ number_format($subtotal, 0) }}</span>
                        </div>

                        <!-- Delivery Selection -->
                        <div class="mb-3">
                            <label class="fw-semibold d-block mb-2" style="font-size:14px;">Select Delivery:</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="delivery-option-card w-100 {{ $cartFreeEligible ? '' : 'active' }}" id="inside-dhaka-opt">
                                        <input type="radio" name="delivery_area" value="70" id="deliveryInside" {{ $cartFreeEligible ? '' : 'checked' }} onchange="updateDelivery(70,1)">
                                        <div>
                                            <div style="font-size:12.5px; font-weight:700;">Inside Dhaka</div>
                                            <div style="font-size:11px; color:var(--text-muted);">1–2 days</div>
                                            <div style="font-size:12.5px; font-weight:700; color:var(--primary-dark);">৳70</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="delivery-option-card w-100" id="outside-dhaka-opt">
                                        <input type="radio" name="delivery_area" value="120" id="deliveryOutside" onchange="updateDelivery(120,2)">
                                        <div>
                                            <div style="font-size:12.5px; font-weight:700;">Outside Dhaka</div>
                                            <div style="font-size:11px; color:var(--text-muted);">2–5 days</div>
                                            <div style="font-size:12.5px; font-weight:700; color:var(--primary-dark);">৳120</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="delivery-option-card w-100" id="express-opt">
                                        <input type="radio" name="delivery_area" value="150" id="deliveryExpress" onchange="updateDelivery(150,3)">
                                        <div>
                                            <div style="font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                                <i class="fas fa-bolt" style="color:var(--primary-dark);"></i> Express
                                                <span style="background:var(--primary-gradient); color:#fff; font-size:9px; padding:1px 5px; border-radius:8px;">FAST</span>
                                            </div>
                                            <div style="font-size:10.5px; color:var(--text-muted); line-height:1.3; margin-top:2px;">ঢাকা / ১১টার আগে</div>
                                            <div style="font-size:12.5px; font-weight:700; color:var(--primary-dark);">৳150</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="delivery-option-card w-100" id="super-express-opt">
                                        <input type="radio" name="delivery_area" value="se" id="deliverySuperExpress" onchange="updateDelivery(0,4)">
                                        <div>
                                            <div style="font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                                <i class="fas fa-rocket" style="color:var(--primary-dark);"></i> Super Express
                                                <span style="background:var(--primary-gradient); color:#fff; font-size:9px; padding:1px 5px; border-radius:8px;">URGENT</span>
                                            </div>
                                            <div style="font-size:10.5px; color:var(--text-muted); line-height:1.3; margin-top:2px;">ঢাকা / চার্জ কুরিয়ারকে</div>
                                            <div style="font-size:12px; font-weight:700; color:var(--primary-dark);">কুরিয়ারকে</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-12">
                                    <label class="delivery-option-card w-100 {{ $cartFreeEligible ? 'active' : '' }}"
                                           id="free-opt"
                                           style="{{ !$cartFreeEligible ? 'opacity:0.55; cursor:not-allowed;' : '' }}">
                                        <input type="radio" name="delivery_area" value="free" id="deliveryFree"
                                               {{ $cartFreeEligible ? 'checked' : 'disabled' }}
                                               onchange="updateDelivery(0,5)">
                                        <div style="flex:1; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                            <div>
                                                <div style="font-size:12.5px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                                    <i class="fas fa-gift" style="color:#28a745;"></i> Free Delivery
                                                </div>
                                                <div style="font-size:11px; color:var(--text-muted); line-height:1.4; margin-top:2px;">
                                                    @if($cartFreeEligible)
                                                        ✅ মোট {{ $cartItemCount }}টি কোয়ান্টিটি!
                                                    @else
                                                        মোট ৩টি কোয়ান্টিটি হলে ফ্রী।
                                                    @endif
                                                </div>
                                            </div>
                                            <div style="font-size:14px; font-weight:800; color:#28a745; white-space:nowrap;">ফ্রি!</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            {{-- Express / Super Express notice --}}
                            <div id="cart-delivery-notice" class="mt-2 p-2 rounded"
                                 style="display:none; background:rgba(107,0,2,0.06); border:1px solid rgba(107,0,2,0.2); font-size:12.5px; color:var(--text-dark); line-height:1.7;"></div>
                        </div>

                        <hr class="cart-summary-divider">
                        <div class="cart-summary-row">
                            <span>Delivery Charge:</span>
                            <span id="deliveryCharge">{{ $cartFreeEligible ? 'ফ্রি' : '৳70' }}</span>
                        </div>
                        <hr class="cart-summary-divider">
                        <div class="cart-summary-row">
                            <span class="fw-bold">Grand Total:</span>
                            <span class="cart-summary-total" id="grandTotal">৳{{ number_format($subtotal + ($cartFreeEligible ? 0 : 70), 0) }}</span>
                        </div>

                        <a href="{{ route('shop.checkout') }}" class="btn-checkout mt-3" id="checkoutBtn">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>
                        <a href="https://wa.me/+8801886870406?text={{ urlencode('I want to place an order') }}" target="_blank"
                           class="d-block text-center mt-2" style="font-size:13px; color:#25d366; font-weight:600;">
                            <i class="fab fa-whatsapp me-1"></i>Order via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@section('extra_js')
<script>
var subtotal = {{ $subtotal ?? 0 }};
var cartFreeEligible = {{ $cartFreeEligible ? 'true' : 'false' }};

var cartDeliveryLabels = {
    1: '৳70', 2: '৳120', 3: '৳150', 4: 'কুরিয়ারকে পেমেন্ট', 5: 'ফ্রি'
};
var cartOptMap = {1:'inside-dhaka-opt', 2:'outside-dhaka-opt', 3:'express-opt', 4:'super-express-opt', 5:'free-opt'};

function updateDelivery(charge, locationVal) {
    var deliveryEl = document.getElementById('deliveryCharge');
    var totalEl    = document.getElementById('grandTotal');
    var btn        = document.getElementById('checkoutBtn');

    if (deliveryEl) deliveryEl.textContent = cartDeliveryLabels[locationVal] || ('৳' + charge);
    if (totalEl)    totalEl.textContent    = '৳' + (subtotal + charge).toLocaleString();

    if (btn) {
        try {
            var url = new URL(btn.href);
            url.searchParams.set('delivery', charge);
            url.searchParams.set('location', locationVal);
            btn.href = url.toString();
        } catch(e) {}
    }

    Object.values(cartOptMap).forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });
    var activeEl = document.getElementById(cartOptMap[locationVal]);
    if (activeEl) activeEl.classList.add('active');

    var cartDeliveryNotices = {
        3: 'আপনি যদি সকাল ১১টার মধ্যে এক্সপ্রেস অর্ডার প্লেস না করেন তাহলে সেটা রেগুলার ডেলিভারিতে যাবে। বিঃ দ্রঃ - এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।',
        4: 'সুপার এক্সপ্রেস ডেলিভারিতে ডেলিভারি চার্জ যেটা আসবে সেটা আপনাকে পে করতে হবে ডেলিভারিম্যান কে।  এবং প্রোডাক্টের মূল্য আগে পরিশোধ করতে হবে।  বিঃ দ্রঃ - সুপার এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।'
    };
    var notice = document.getElementById('cart-delivery-notice');
    if (notice) {
        if (cartDeliveryNotices[locationVal]) {
            notice.innerHTML = '<i class="fas fa-info-circle me-1" style="color:var(--primary-dark);"></i> ' + cartDeliveryNotices[locationVal];
            notice.style.display = '';
        } else {
            notice.innerHTML = '';
            notice.style.display = 'none';
        }
    }
}

// Initialize checkout button URL on page load
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('checkoutBtn');
    if (btn) {
        try {
            var initCharge    = cartFreeEligible ? 0 : 70;
            var initLocation  = cartFreeEligible ? 5 : 1;
            var url = new URL(btn.href);
            url.searchParams.set('delivery', initCharge);
            url.searchParams.set('location', initLocation);
            btn.href = url.toString();
        } catch(e) {}
    }
});

@php
    $fbCartData    = session('cart', []);
    $fbCartIds     = array_values(array_map(fn($i) => (string) ($i['variation_id'] ?? $i['product_id'] ?? 0), $fbCartData));
    $fbCartValue   = round((float) collect($fbCartData)->sum(fn($i) => ($i['sale_price'] ?? $i['price']) * $i['quantity']) / 2, 2);
    $fbCartItems   = (int)   array_sum(array_column($fbCartData, 'quantity'));
@endphp
// ── Meta Pixel: ViewContent (cart) ───────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq === 'function') {
        fbq('track', 'ViewContent', {
            content_ids: {!! json_encode($fbCartIds) !!},
            content_type: 'product',
            num_items: {{ $fbCartItems }},
            value: {{ $fbCartValue }},
            currency: 'BDT'
        });
    }
});
</script>
@endsection

