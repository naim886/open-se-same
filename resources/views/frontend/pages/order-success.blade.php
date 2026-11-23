<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Placed Successfully – OpenSesame BD</title>
    <meta name="title" content="Order Placed Successfully – OpenSesame BD">
    <meta name="description" content="Your order has been placed successfully at OpenSesame BD. Our team will contact you shortly to confirm.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="OpenSesame BD">
    <meta property="og:title" content="Order Placed Successfully – OpenSesame BD">
    <meta property="og:description" content="Your order has been placed successfully at OpenSesame BD.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/og.webp') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            margin: 2rem;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .success-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #a62b26 0%, #8b2420 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(166, 43, 38, 0.3);
            color: white;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 2px solid #dee2e6;
            color: #6c757d;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            border-color: #a62b26;
            color: #a62b26;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid #28a745;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
            font-weight: 600;
            padding-top: 0.5rem;
            border-top: 1px solid #dee2e6;
        }
    </style>
    @include('frontend.partials.analytics')
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1 class="success-title">Order Placed Successfully!</h1>

        <p class="success-message">
            Thank you for your order! We have received your request and our team will contact you shortly to confirm the details and arrange delivery.
        </p>

        @if(session('success'))
        <div class="alert alert-success" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('success') }}
        </div>
        @endif

        <div class="order-details">
            <h5 class="mb-3"><i class="fas fa-clipboard-list me-2"></i>What's Next?</h5>
            <ul class="list-unstyled text-start">
                <li class="mb-2"><i class="fas fa-phone text-success me-2"></i> Our team will call you within 2-4 hours</li>
                <li class="mb-2"><i class="fas fa-calendar-check text-info me-2"></i> We'll schedule a convenient delivery time</li>
                <li class="mb-2"><i class="fas fa-tools text-warning me-2"></i> Professional installation service available</li>
                <li><i class="fas fa-headset text-primary me-2"></i> 24/7 customer support for any questions</li>
            </ul>
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('shop.home') }}" class="btn-primary-custom">
                <i class="fas fa-home"></i>
                Continue Shopping
            </a>
            <a href="tel:01826339988" class="btn-secondary-custom">
                <i class="fas fa-phone"></i>
                Call Us Now
            </a>
        </div>

        <div class="mt-4 pt-4 border-top">
            <p class="text-muted mb-0">
                <small>
                    <i class="fas fa-shield-alt me-1"></i>
                    Your information is secure and will only be used to process your order.
                </small>
            </p>
        </div>
    </div>

    @php
        $fbPurchaseData = session('order_details');
        $fbOrder        = session('order_number') ?? '';
        $fbTotal        = round((float) ($fbPurchaseData['total_amount'] ?? 0), 2);
        $fbContentIds   = $fbPurchaseData['content_ids'] ?? [$fbOrder];
        $fbItems        = $fbPurchaseData['items'] ?? [];
        $fbNumItems     = collect($fbItems)->sum('quantity') ?: 1;
        $fbShipping     = (float) ($fbPurchaseData['delivery_charge'] ?? 0);
        $fbCoupon       = $fbPurchaseData['coupon_code'] ?? null;
        $purchasePhone = preg_replace('/\D/', '', $fbPurchaseData['customer_phone'] ?? '');
        if (strlen($purchasePhone) === 11 && str_starts_with($purchasePhone, '0')) {
            $purchasePhone = '880' . substr($purchasePhone, 1);
        }
        $purchaseExternalId = $purchasePhone !== ''
            ? hash('sha256', $purchasePhone)
            : hash('sha256', (string) session('capi_uid'));
    @endphp
    @if($fbPurchaseData)
    <script>
        if (typeof fbq === 'function' && window.ocMetaPixelId) {
            fbq('init', window.ocMetaPixelId, { external_id: {!! json_encode($purchaseExternalId) !!} });
        }
        if (typeof ocTrack === 'function') {
            ocTrack('Purchase', {
                value:        {{ $fbTotal }},
                currency:     'BDT',
                content_ids:  {!! json_encode($fbContentIds) !!},
                content_type: 'product',
                num_items:    {{ $fbNumItems }},
                order_id:     {!! json_encode($fbOrder) !!}
            }, {!! json_encode(session('purchase_event_id') ?? '') !!}, {
                items: {!! json_encode($fbItems ?: null) !!},
                transaction_id: {!! json_encode($fbOrder) !!},
                shipping: {{ $fbShipping }},
                coupon: {!! json_encode($fbCoupon) !!}
            });
        }
    </script>
    @endif
    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
