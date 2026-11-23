@extends('frontend.layouts.app')

@section('title', 'Payment Failed – OpenSesame BD')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-5" style="background:var(--bg-light); min-height:60vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 text-center" style="border-radius:var(--radius); box-shadow:var(--shadow);">
                    <div class="card-body p-4 p-md-5">
                        <div style="width:76px;height:76px;background:#fdecec;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                            <i class="fas fa-times" style="font-size:30px;color:#dc3545;"></i>
                        </div>

                        <h3 class="fw-bold mb-2" style="color:var(--text-dark);font-size:22px;">Payment Failed</h3>

                        <p style="color:var(--text-muted);font-size:14px;line-height:1.7;margin-bottom:20px;">
                            {{ $message }}
                        </p>

                        <div class="p-3 mb-4" style="background:#fafafa;border:1px solid var(--border);border-radius:10px;text-align:left;font-size:13.5px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color:var(--text-muted);">Order No.</span>
                                <strong>M-{{ $order->order_number }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="color:var(--text-muted);">Amount</span>
                                <strong>৳{{ number_format((float) $order->total_amount, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span style="color:var(--text-muted);">Payment Status</span>
                                <span class="badge bg-danger">{{ $order->getPaymentStatusText() }}</span>
                            </div>
                        </div>

                        @if((int) $order->payment_status !== \App\Models\Order::PAYMENT_STATUS_PAID && in_array((int) $order->payment_method, [\App\Models\Order::PAYMENT_METHOD_SSLCOMMERZ, \App\Models\Order::PAYMENT_METHOD_NAGAD], true))
                            <a href="{{ $retryUrl }}" class="btn btn-primary w-100 mb-2" style="padding:12px;">
                                <i class="fas fa-redo me-2"></i>Try Payment Again
                            </a>
                        @endif

                        <a href="{{ route('shop.track-order', ['q' => 'M-' . $order->order_number]) }}" class="btn btn-outline-secondary w-100 mb-2" style="padding:12px;">
                            <i class="fas fa-search me-2"></i>Track This Order
                        </a>

                        <a href="{{ route('shop.home') }}" class="btn btn-link text-decoration-none" style="font-size:13.5px;">
                            Back to Home
                        </a>

                        <p class="mb-0 mt-3" style="font-size:12px;color:var(--text-muted);">
                            Need help? Call <a href="tel:+8801826339988" style="color:var(--primary);">01826339988</a>
                            or <a href="https://wa.me/+8801826339988" target="_blank" rel="noopener" style="color:var(--primary);">WhatsApp us</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
