<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Placed Successfully – Open Se Same</title>
    <meta name="title" content="Order Placed Successfully – Open Se Same">
    <meta name="description" content="Your order has been placed successfully at Open Se Same. Our team will contact you shortly to confirm.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="Open Se Same">
    <meta property="og:title" content="Order Placed Successfully – Open Se Same">
    <meta property="og:description" content="Your order has been placed successfully at Open Se Same.">
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
            <a href="{{ route('frontend.index') }}" class="btn-primary-custom">
                <i class="fas fa-home"></i>
                Continue Shopping
            </a>
            <a href="tel:01886870406" class="btn-secondary-custom">
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
        $pixelIp = trim(explode(',', request()->header('CF-Connecting-IP') ?? request()->header('X-Forwarded-For') ?? request()->ip())[0]);
        $pixelUa = request()->userAgent() ?? '';
        $fbPurchaseData = session('order_details');
        $fbFn = $fbLn = $fbPh = '';
        if ($fbPurchaseData) {
            $nameParts = explode(' ', trim($fbPurchaseData['customer_name'] ?? ''), 2);
            $rawFn = strtolower(trim($nameParts[0] ?? ''));
            $rawLn = strtolower(trim($nameParts[1] ?? ''));
            $rawPh = preg_replace('/\D/', '', $fbPurchaseData['customer_phone'] ?? '');
            $fbFn = $rawFn !== '' ? hash('sha256', $rawFn) : '';
            $fbLn = $rawLn !== '' ? hash('sha256', $rawLn) : '';
            $fbPh = $rawPh !== '' ? hash('sha256', $rawPh) : '';
        }
    @endphp
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
        (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '26065580893095421', {
            client_ip_address: {!! json_encode($pixelIp) !!},
            client_user_agent: {!! json_encode($pixelUa) !!}
            @if($fbFn), fn: '{{ $fbFn }}'@endif
            @if($fbLn), ln: '{{ $fbLn }}'@endif
            @if($fbPh), ph: '{{ $fbPh }}'@endif
        });
        fbq('track', 'PageView');
        @if($fbPurchaseData)
        fbq('track', 'Purchase', {
            value:        {{ round((float)($fbPurchaseData['total_amount'] ?? 0) / 2, 2) }},
            currency:     'BDT',
            content_ids:  {!! json_encode($fbPurchaseData['content_ids'] ?? [session('order_number') ?? '']) !!},
            content_type: 'product',
            order_id:     {!! json_encode(session('order_number') ?? '') !!}
        }, {eventID: {!! json_encode(session('purchase_event_id') ?? '') !!}});
        @endif
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=26065580893095421&ev=PageView&noscript=1"/></noscript>
    <!-- End Meta Pixel Code -->
    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
