<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="Celebrate love with our exclusive Valentine's Day rose collection. Express your feelings with beautiful roses from Open Se Same.">
    <meta property="og:title" content="Valentine's Day Rose Collection - Open Se Same"/>
    <meta property="og:description"
          content="Celebrate love with our exclusive Valentine's Day rose collection. Express your feelings with beautiful roses."/>
    <meta property="og:image" content="{{asset('images/og.png')}}"/>
    <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.png')}}">
    <title>Valentine's Day Rose Collection 💝 - Open Se Same</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Valentine CSS -->
    <link rel="stylesheet" href="{{ minified_asset('frontend/css/valentine.css') }}">
</head>
<body class="valentine-page">

<!-- Floating Hearts Background -->
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

<!-- Navigation -->
<nav class="navbar navbar-expand-lg valentine-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('valentine.landing') }}">
            <img src="{{ asset('images/logo-web-white.png') }}" alt="Open Se Same" class="valentine-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#valentineNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="valentineNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('valentine.landing') }}">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#products">
                        <i class="fas fa-heart me-1"></i> Collection
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">
                        <i class="fas fa-phone me-1"></i> Contact
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="valentine-hero">
    <div class="valentine-hero-overlay"></div>
    <div class="valentine-hero-content">
        <div class="valentine-hero-badge">
            <i class="fas fa-heart"></i> Valentine's Day Special
        </div>
        <h1>Preserved Mini Rose</h1>
        <p class="tagline">Forever Love, Forever Beautiful</p>
        <div class="hero-features">
            <span><i class="fas fa-infinity"></i> Lasts 3-5 Years</span>
            <span><i class="fas fa-leaf"></i> 100% Real Rose</span>
            <span><i class="fas fa-gift"></i> Fast Delivery</span>
        </div>
        <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
            <a href="#products" class="btn btn-valentine">
                <i class="fas fa-heart me-2"></i> Shop Now
            </a>
        </div>
    </div>
</section>

<!-- Special Offer Banner -->
<section class="valentine-newsletter">
    <div class="container">
        <h3><i class="fas fa-gift me-2"></i> Valentine's Day Special</h3>
        <p>We don't just deliver gifts, we deliver your love</p>
    </div>
</section>

<!-- Products Section -->
<section class="valentine-section" id="products">
    <div class="container">
        <h2 class="valentine-section-title">Preserved Mini Rose Collection</h2>
        <p class="valentine-section-subtitle">Real roses that last forever, just like your love 💕</p>

        <div class="row g-4 valentine-products-row">
            @forelse($products as $product)
                <div class="col-lg-3 col-md-4 col-sm-6">
                   <x-valentine-product-card :product="$product"/>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-rose fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Our roses are blooming soon!</h4>
                    <p class="text-muted">Check back later for our Valentine's Day collection.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="valentine-section" style="background: #fff;">
    <div class="container">
        <h2 class="valentine-section-title">Why Choose Us</h2>
        <p class="valentine-section-subtitle">We make every petal count</p>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #FFB6C1 0%, #FFC0CB 100%);">
                        <i class="fas fa-infinity fa-2x" style="color: #8B0000;"></i>
                    </div>
                    <h5 class="valentine-heading">Preserved Forever</h5>
                    <p class="text-muted">Real roses that last 3-5 years</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #FFB6C1 0%, #FFC0CB 100%);">
                        <i class="fas fa-truck fa-2x" style="color: #8B0000;"></i>
                    </div>
                    <h5 class="valentine-heading">Fast Delivery</h5>
                    <p class="text-muted">Same day delivery available</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #FFB6C1 0%, #FFC0CB 100%);">
                        <i class="fas fa-gift fa-2x" style="color: #8B0000;"></i>
                    </div>
                    <h5 class="valentine-heading">Premium</h5>
                    <p class="text-muted">Premium gifts for your loved ones</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #FFB6C1 0%, #FFC0CB 100%);">
                        <i class="fas fa-heart fa-2x" style="color: #8B0000;"></i>
                    </div>
                    <h5 class="valentine-heading">Made with Love</h5>
                    <p class="text-muted">Every arrangement crafted with care</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="valentine-section" id="contact">
    <div class="container">
        <h2 class="valentine-section-title">Get in Touch</h2>
        <p class="valentine-section-subtitle">We're here to help you express your love</p>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="row g-4 text-center">
                    <div class="col-md-4">
                        <div class="p-4">
                            <i class="fas fa-phone fa-2x mb-3" style="color: #C41E3A;"></i>
                            <h6 class="valentine-heading">Call Us</h6>
                            <p class="text-muted mb-0"><a class="text-dark" href="tel:+8801886870406">+8801886870406</a></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4">
                            <i class="fas fa-envelope fa-2x mb-3" style="color: #C41E3A;"></i>
                            <h6 class="valentine-heading">Email Us</h6>
                            <p class="text-muted mb-0">info@opensesame.com</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4">
                            <i class="fas fa-clock fa-2x mb-3" style="color: #C41E3A;"></i>
                            <h6 class="valentine-heading">Opening Hours</h6>
                            <p class="text-muted mb-0">24/7 365 Days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="valentine-footer text-center text-lg-start">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="{{ asset('images/logo-web-white.png') }}" alt="Open Se Same" class="valentine-footer-logo mb-3">
                <p class="text-white">At our heart, we don't just sell flowers we preserve the emotions that words often fail to capture. We
                    believe that true love, like a beautiful memory, should never have an expiration date. That is why we dedicated ourselves to
                    the art of treasures s the ultimate symbol of a love that stands the test of time.</p>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ route('valentine.landing') }}"><i class="fas fa-chevron-right me-2"></i> Home</a></li>
                    <li><a href="#products"><i class="fas fa-chevron-right me-2"></i> Our Roses</a></li>
                    <li><a href="#contact"><i class="fas fa-chevron-right me-2"></i> Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Follow Us</h5>
                <div class="gap-3">
                    <a href="https://www.facebook.com/opensesame1" target="_blank" class="fs-4 me-3"><i class="fab fa-facebook"></i></a>
                    <a href="https://www.instagram.com/open_se_same/" target="_blank" class="fs-4"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="valentine-footer-bottom">
            <p class="mb-0">💝 Made with love by Open Se Same © {{ date('Y') }} | Happy Valentine's Day!</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
