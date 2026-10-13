<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Essential Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title>Open Se Same - Premium Custom Closets & Storage Solutions</title>
    <meta name="description" content="Discover premium custom closets and storage solutions. Transform your space with our luxury closet designs, organization systems, and expert installation services.">
    <meta name="keywords" content="custom closets, storage solutions, closet organization, luxury closets, home storage">
    <meta name="author" content="Open Se Same">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.png')}}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">

    <style>
        :root {
            --primary-color: #a62b26;
            --primary-dark: #8b2420;
            --primary-light: #d63a33;
            --secondary-color: #2c3e50;
            --accent-gold: #f39c12;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --text-muted: #8e8e93;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --border-light: #e9ecef;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.12);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Promotional Bar */
        .promo-bar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .swiper-wrapper{
            height: 550px!important;
        }

        .promo-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .promo-close {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.8;
            transition: var(--transition);
        }

        .promo-close:hover {
            opacity: 1;
        }

        /* Navigation */
        .navbar-custom {
            background: var(--bg-white);
            padding: 15px 0;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar-brand img {
            height: 50px;
            width: auto;
        }

        .navbar-nav .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            margin: 0 15px;
            padding: 8px 0;
            position: relative;
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        /* Hero Banner */
        .hero-banner {
            position: relative;
            /*height: 70vh;*/
            /*min-height: 500px;*/
            overflow: hidden;
            /*border-radius: 0 0 50px 50px;*/
        }

        .swiper-slide {
            position: relative;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
        }

        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.7) 0%, rgba(166, 43, 38, 0.6) 100%);
        }

        .slide-content {
            position: relative;
            z-index: 2;
            color: white;
            max-width: 600px;
        }

        .slide-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .slide-description {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        /* Categories Section */
        .section-padding {
            padding: 80px 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .category-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 30px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--border-light);
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .category-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }

        .category-card img.category-icon {
            object-fit: contain;
            border-radius: 50%;
        }

        .category-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .category-description {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Product Cards */
        .product-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--border-light);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            height: 250px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }

        .stars {
            color: #ffc107;
        }

        .rating-text {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Customer Reviews */
        .reviews-section {
            background: var(--bg-light);
        }

        .review-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            height: 100%;
            position: relative;
        }

        .review-stars {
            color: #ffc107;
            margin-bottom: 15px;
        }

        .review-text {
            font-style: italic;
            color: var(--text-dark);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* FAQ Section */
        .faq-item {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .faq-question {
            padding: 20px;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .faq-question:hover {
            color: var(--primary-color);
        }

        .faq-answer {
            padding: 0 20px 20px;
            color: var(--text-light);
            display: none;
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        .faq-item.active .faq-question {
            color: var(--primary-color);
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer h5 {
            color: white;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer ul li a:hover {
            color: var(--primary-color);
        }

        .social-links a {
            display: inline-flex;
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            margin-right: 10px;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .slide-title {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .hero-banner {
                height: 60vh;
                min-height: 400px;
            }
        }

        /* Loading Animation */
        .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>

<body>
    <!-- Promotional Bar -->
    <div class="promo-bar" id="promoBar">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">
                        <i class="fas fa-fire me-2"></i>
                        <strong>Limited Time Offer!</strong> Get 10% FLAT DISCOUNT on all custom closets - Free consultation included!
                        <i class="fas fa-fire ms-2"></i>
                    </p>
                    <button class="promo-close" onclick="closePromoBar()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('frontend.index') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Open Se Same Logo" loading="eager">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reviews">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="#" class="btn-primary-custom">
                        <i class="fas fa-shopping-cart"></i>
                        Cart (0)
                    </a>
                    <a href="#contact" class="btn-primary-custom">
                        <i class="fas fa-phone"></i>
                        Get Quote
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section id="home" class="hero-banner">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                @forelse($banners as $banner)
                <div class="swiper-slide" style="background-image: url('{{ \Illuminate\Support\Facades\Storage::url($banner->image) }}')">
                    <div class="slide-overlay"></div>
                    <div class="container">
                        <div class="slide-content" data-aos="fade-right">
                            <h1 class="slide-title">{{ $banner->title ?? 'Transform Your Space' }}</h1>
                            <p class="slide-description">{{ $banner->description ?? 'Discover premium custom closets and storage solutions designed for your lifestyle.' }}</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="#categories" class="btn-primary-custom">
                                    <i class="fas fa-eye"></i>
                                    Explore Collection
                                </a>
                                <a href="#contact" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-phone"></i>
                                    Free Consultation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="swiper-slide" style="background-image: url('{{ asset('images/hero-default.jpg') }}')">
                    <div class="slide-overlay"></div>
                    <div class="container">
                        <div class="slide-content" data-aos="fade-right">
                            <h1 class="slide-title">Transform Your Space with Custom Closets</h1>
                            <p class="slide-description">Discover premium storage solutions designed to maximize space and reflect your personal style.</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="#categories" class="btn-primary-custom">
                                    <i class="fas fa-eye"></i>
                                    Explore Collection
                                </a>
                                <a href="#contact" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-phone"></i>
                                    Free Consultation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <!-- Top Categories -->
    <section id="categories" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title" data-aos="fade-up">Top Categories</h2>
                    <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Explore our wide range of premium storage solutions</p>
                </div>
            </div>
            <div class="row g-4">
                @forelse($categories as $key => $category)
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($key + 1) * 100 }}">
                    <div class="category-card">
                        @if($category->icon)
                        <img src="{{ asset($category->icon) }}" alt="{{ $category->name }}" class="category-icon">
                        @else
                        <div class="category-icon">
                            <i class="fas fa-cube"></i>
                        </div>
                        @endif
                        <h4 class="category-title">{{ $category->name }}</h4>
                        <p class="category-description">{{ $category->description ?? 'Premium quality storage solutions' }}</p>
                        <a href="#" class="btn-primary-custom mt-3">
                            <i class="fas fa-arrow-right"></i>
                            View Products
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center">
                        <p class="text-muted">No categories available at the moment.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="products" class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title" data-aos="fade-up">Featured Products</h2>
                    <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Discover our best-selling and most popular items</p>
                </div>
            </div>
            <div class="row g-4">
                @forelse($featuredProducts as $key => $product)
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($key + 1) * 100 }}">
                    <div class="product-card">
                        <div class="product-image">

                            <img src="{{ Storage::url($product->primary_photo) }}" alt="{{ $product->name }}" loading="lazy">

                            <span class="product-badge">Featured</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-title">{{ $product->name }}</h5>
                            <div class="product-price">${{ number_format($product->sale_price ?? $product->price, 2) }}</div>
                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-text">(4.9)</span>
                            </div>
                            <a href="{{ route('frontend.product.detail', $product->slug) }}" class="btn-primary-custom w-100">
                                <i class="fas fa-eye"></i>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center">
                        <p class="text-muted">No featured products available at the moment.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Category-wise Products -->
    @if(!empty($categoryWiseProducts))
    @foreach($categoryWiseProducts as $categoryData)
    @if($categoryData['products']->isNotEmpty())
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title" data-aos="fade-up">{{ $categoryData['category']->name }}</h2>
                    <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">{{ $categoryData['category']->description ?? 'Explore our premium collection' }}</p>
                </div>
            </div>
            <div class="row g-4">
                @foreach($categoryData['products'] as $key => $product)
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($key + 1) * 100 }}">
                    <div class="product-card">
                        <div class="product-image">

                            <img src="{{ asset($product->primary_photo) }}" alt="{{ $product->name }}" loading="lazy">

                        </div>
                        <div class="product-info">
                            <h5 class="product-title">{{ $product->name }}</h5>
                            <div class="product-price">${{ number_format($product->sale_price ?? $product->price, 2) }}</div>
                            <div class="product-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="rating-text">(4.8)</span>
                            </div>
                            <a href="{{ route('frontend.product.detail', $product->slug) }}" class="btn-primary-custom w-100">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="#" class="btn-primary-custom">
                        <i class="fas fa-th-large"></i>
                        View All {{ $categoryData['category']->name }}
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif
    @endforeach
    @endif

    <!-- Customer Reviews -->
    <section id="reviews" class="section-padding reviews-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title" data-aos="fade-up">What Our Customers Say</h2>
                    <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Read reviews from our satisfied customers</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">"Absolutely amazing quality! The custom closet exceeded all my expectations. The installation was professional and the design is perfect for my space."</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">SJ</div>
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">"Great customer service and top-notch products. The team helped me design the perfect storage solution for my bedroom. Highly recommended!"</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">MC</div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="review-card">
                        <div class="review-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="review-text">"The quality is outstanding and the prices are very reasonable. The delivery was fast and the installation team was very professional."</p>
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">LR</div>
                            <div>
                                <h6 class="mb-0">Lisa Rodriguez</h6>
                                <small class="text-muted">Verified Customer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title" data-aos="fade-up">Frequently Asked Questions</h2>
                    <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Find answers to commonly asked questions</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>What materials do you use for custom closets?</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="faq-answer">
                            <p>We use premium quality materials including solid wood, engineered wood, melamine, and high-grade hardware. All materials are carefully selected for durability and aesthetic appeal.</p>
                        </div>
                    </div>

                    <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>How long does installation take?</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Installation typically takes 1-3 days depending on the size and complexity of your project. We provide a detailed timeline during the consultation process.</p>
                        </div>
                    </div>

                    <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>Do you offer warranties on your products?</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Yes, we offer comprehensive warranties on all our products. Materials come with a 10-year warranty and installation work is covered for 2 years.</p>
                        </div>
                    </div>

                    <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>Can you work with existing spaces?</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Absolutely! We specialize in maximizing existing spaces and can work with any room configuration. Our design team will create a custom solution that fits perfectly.</p>
                        </div>
                    </div>

                    <div class="faq-item" data-aos="fade-up" data-aos-delay="500">
                        <button class="faq-question" onclick="toggleFAQ(this)">
                            <span>What is the consultation process like?</span>
                            <i class="fas fa-plus"></i>
                        </button>
                        <div class="faq-answer">
                            <p>Our consultation process includes an initial assessment, 3D design creation, material selection, and detailed quote. The initial consultation is completely free with no obligations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5>Open Se Same</h5>
                    <p class="mb-4">Transform your space with our premium custom closets and storage solutions. Quality craftsmanship meets innovative design.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Categories</h5>
                    <ul>
                        @foreach($categories->take(5) as $category)
                        <li><a href="#">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Support</h5>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Warranty</a></li>
                        <li><a href="#">Size Guide</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact Info</h5>
                    <ul>
                        <li><i class="fas fa-phone me-2"></i> 01886870406</li>
                        <li><i class="fas fa-envelope me-2"></i> info@opensesame.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Open Se Same. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Initialize Swiper
        const heroSwiper = new Swiper('.heroSwiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            }
        });

        // Close promotional bar
        function closePromoBar() {
            document.getElementById('promoBar').style.display = 'none';
        }

        // Toggle FAQ
        function toggleFAQ(element) {
            const faqItem = element.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');

            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-question i').className = 'fas fa-plus';
            });

            // Open clicked item if it wasn't already active
            if (!isActive) {
                faqItem.classList.add('active');
                element.querySelector('i').className = 'fas fa-minus';
            }
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 25px rgba(0,0,0,0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.08)';
            }
        });

        // Add to cart functionality placeholder
        document.querySelectorAll('.btn-primary-custom').forEach(button => {
            if (button.textContent.includes('Add to Cart')) {
                button.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    this.classList.add('btn-loading');

                    // Simulate API call
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-check"></i> Added!';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-loading');
                        }, 1000);
                    }, 1000);
                });
            }
        });

        // Newsletter signup placeholder
        const newsletterForm = document.querySelector('#newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                alert('Thank you for subscribing to our newsletter!');
            });
        }
    </script>
</body>

</html>
