@extends('frontend.layouts.app')

@section('title', 'Order Placed Successfully! - Open Se Same')
@section('meta_description', 'Your order has been placed successfully at Open Se Same. We will contact you shortly to confirm your order.')
@section('og_title', 'Order Placed Successfully! - Open Se Same')
@section('og_description', 'Your order has been placed successfully at Open Se Same.')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="order-success-section">
    <div class="container">
        <div class="order-success-card">
            <div class="success-icon-wrap">
                <i class="fas fa-check"></i>
            </div>

            <h2 style="font-size:26px; font-weight:800; color:var(--text-dark); margin-bottom:8px;">
                Order Placed Successfully! 🎉
            </h2>
            <p style="font-size:15px; color:var(--text-muted); margin-bottom:24px; line-height:1.7;">
                Your order has been received and confirmed.<br>
                Our team will contact you shortly.
            </p>

            @if(session('order_number'))
            <div class="p-3 rounded mb-4" style="background:rgba(40,167,69,0.08); border:1px solid rgba(40,167,69,0.2);">
                <p class="mb-0" style="font-size:14px; font-weight:600; color:#155724;">
                    Order Number: <span style="font-size:20px; font-weight:800;">M-{{ session('order_number') }}</span>
                </p>
            </div>
            @endif

            @if(session('order_details'))
            @php $details = session('order_details'); @endphp
            <div class="p-3 rounded mb-4 text-start" style="background:var(--bg-light); border:1px solid var(--border);">
                <h6 class="fw-bold mb-3">Order Details</h6>
                <table class="table table-sm mb-0" style="font-size:14px;">
                    <tr><td class="text-muted" style="width:120px;">Name:</td><td class="fw-semibold">{{ $details['customer_name'] ?? '' }}</td></tr>
                    <tr><td class="text-muted">Mobile:</td><td class="fw-semibold">{{ $details['customer_phone'] ?? '' }}</td></tr>
                    <tr><td class="text-muted">Address:</td><td>{{ $details['customer_address'] ?? '' }}</td></tr>
                    <tr>
                        <td class="text-muted">Total:</td>
                        <td class="fw-bold" style="font-size:16px; background:var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
                            ৳{{ number_format($details['total_amount'] ?? 0, 0) }}
                        </td>
                    </tr>
                </table>
            </div>
            @endif

            {{-- Delivery method notice for Express / Super Express --}}
            @if(in_array(session('delivery_location'), [3, 4]))
            <div class="p-3 rounded mb-4 text-start"
                 style="background:rgba(1,92,60,0.07); border:1px solid rgba(1,92,60,0.25);">
                <h6 class="fw-bold mb-2" style="color:var(--primary-dark); font-size:13.5px;">
                    <i class="{{ session('delivery_location') == 4 ? 'fas fa-rocket' : 'fas fa-bolt' }} me-1"></i>
                    {{ session('delivery_location') == 4 ? 'Super Express' : 'Express' }} ডেলিভারি নোট
                </h6>
                <p class="mb-0" style="font-size:13px; line-height:1.8; color:var(--text-dark);">
                    @if(session('delivery_location') == 3)
                        আপনি যদি সকাল ১১টার মধ্যে এক্সপ্রেস অর্ডার প্লেস না করেন তাহলে সেটা রেগুলার ডেলিভারিতে যাবে। বিঃ দ্রঃ - এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।
                    @else
                        সুপার এক্সপ্রেস ডেলিভারিতে ডেলিভারি চার্জ যেটা আসবে সেটা আপনাকে পে করতে হবে ডেলিভারিম্যান কে।&nbsp; এবং প্রোডাক্টের মূল্য আগে পরিশোধ করতে হবে।&nbsp; বিঃ দ্রঃ - সুপার এক্সপ্রেস ডেলিভারি শুধু ঢাকা সিটি এর মধ্যে।
                    @endif
                </p>
            </div>
            @endif

            <div class="p-3 rounded mb-4" style="background:rgba(1,92,60,0.05); border:1px dashed rgba(1,92,60,0.25);">
                <p class="mb-0" style="font-size:13.5px; line-height:1.7; color:var(--text-dark);">
                    <i class="fas fa-info-circle" style="color:var(--primary-dark);"></i>
                    For any queries, <strong>WhatsApp</strong> us at:
                    <a href="https://wa.me/+8801886870406" class="fw-bold" style="color:var(--primary-dark);">01886870406</a>
                </p>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('shop.home') }}" class="btn-add-cart-lg d-inline-block">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <a href="{{ route('shop.products') }}" class="btn-buy-now d-inline-block">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>

            <div class="mt-4">
                <a href="https://wa.me/+8801886870406" target="_blank" style="color:#25d366; font-weight:600; font-size:14px;">
                    <i class="fab fa-whatsapp" style="font-size:18px;"></i> Track your order on WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
@section('extra_js')
@if(session('order_details'))
@php
    $od        = session('order_details');
    $nameParts = explode(' ', trim($od['customer_name'] ?? ''), 2);
    $rawFn     = strtolower(trim($nameParts[0] ?? ''));
    $rawLn     = strtolower(trim($nameParts[1] ?? ''));
    $rawPh     = preg_replace('/\D/', '', $od['customer_phone'] ?? '');
    $rawEm     = strtolower(trim($od['customer_email'] ?? ''));
    $fbFn      = $rawFn !== '' ? hash('sha256', $rawFn) : '';
    $fbLn      = $rawLn !== '' ? hash('sha256', $rawLn) : '';
    $fbPh      = $rawPh !== '' ? hash('sha256', $rawPh) : '';
    $fbEm      = $rawEm !== '' ? hash('sha256', $rawEm) : '';
    $fbTotal   = round((float)($od['product_price'] ?? $od['total_amount'] ?? 0) / 2, 2);
    $fbOrder   = session('order_number') ?? '';
    $pixelIp   = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
    $pixelUa   = request()->userAgent() ?? '';
@endphp
<script>
if (typeof fbq === 'function') {
    fbq('init', '26065580893095421', {
        client_ip_address: {!! json_encode($pixelIp) !!},
        client_user_agent: {!! json_encode($pixelUa) !!}
        @if($fbFn), fn: '{{ $fbFn }}'@endif
        @if($fbLn), ln: '{{ $fbLn }}'@endif
        @if($fbPh), ph: '{{ $fbPh }}'@endif
        @if($fbEm), em: '{{ $fbEm }}'@endif
    });
    fbq('track', 'Purchase', {
        value:        {{ $fbTotal }},
        currency:     'BDT',
        content_ids:  [{!! json_encode($fbOrder) !!}],
        content_type: 'product',
        num_items:    1,
        order_id:     {!! json_encode($fbOrder) !!}
    }, {eventID: '{{ session('purchase_event_id') }}'});
}
</script>
@endif
@endsection
