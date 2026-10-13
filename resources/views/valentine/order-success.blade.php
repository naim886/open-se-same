<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thank you for your Valentine's Day order from Open Se Same">
    <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.png')}}">
    <title>Order Successful 💝 - Open Se Same</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Valentine CSS -->
    <link rel="stylesheet" href="{{ minified_asset('frontend/css/valentine.css') }}">
</head>
<body>
    <div class="valentine-success-container">
        <!-- Floating Hearts -->
        <div class="floating-hearts">
            <span class="heart">💕</span>
            <span class="heart">❤️</span>
            <span class="heart">💖</span>
            <span class="heart">💗</span>
            <span class="heart">💝</span>
            <span class="heart">🌹</span>
            <span class="heart">💕</span>
            <span class="heart">❤️</span>
            <span class="heart">💖</span>
            <span class="heart">🌹</span>
        </div>

        <div class="valentine-success-card">
            <div class="valentine-success-icon">
                💝
            </div>

            <h1>Thank You for Your Order!</h1>

            @if(session('success'))
                <p class="lead">{{ session('success') }}</p>
            @else
                <p class="lead">Your Valentine's Day order has been placed successfully!</p>
            @endif

            <div class="mb-4 p-3 rounded" style="background: linear-gradient(135deg, #FFF0F5 0%, #FFE4E1 100%);">
                <h6 class="valentine-heading mb-3">What's Next?</h6>
                <ul class="list-unstyled text-start">
                    <li class="mb-2">
                        <i class="fas fa-check-circle me-2" style="color: #28a745;"></i>
                        We've received your order details
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2" style="color: var(--valentine-deep-red);"></i>
                        Our team will call you shortly to confirm
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-gift me-2" style="color: var(--valentine-deep-red);"></i>
                        Your order will be beautifully gift-wrapped
                    </li>
                    <li>
                        <i class="fas fa-truck me-2" style="color: var(--valentine-deep-red);"></i>
                        Delivered with love on your chosen date
                    </li>
                </ul>
            </div>

            <p class="text-muted mb-4">
                <i class="fas fa-question-circle me-1"></i>
                Questions? Call us at <a href="tel:01886870406" style="color: var(--valentine-deep-red);">01886870406</a>
            </p>

            <a href="{{ route('valentine.landing') }}" class="btn btn-order-valentine btn-lg">
                <i class="fas fa-heart me-2"></i> Continue Shopping
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
