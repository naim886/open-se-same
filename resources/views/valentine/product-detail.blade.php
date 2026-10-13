<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
          content="{{ $product->meta_description ?? $product->short_description ?? 'Preserved Mini Rose for Valentine\'s Day from Open Se Same' }}">
    <meta property="og:title" content="{{ $product->meta_title ?? $product->name }} - Valentine's Day | Open Se Same"/>
    <meta property="og:description" content="{{ $product->short_description ?? 'Express your love with preserved mini roses' }}"/>
    @if($product->og_image)
        <meta property="og:image" content="{{ asset('storage/' . $product->og_image) }}"/>
    @elseif($product->primary_photo)
        <meta property="og:image" content="{{ asset('storage/' . $product->primary_photo) }}"/>
    @endif
    <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.png')}}">
    <title>{{ $product->name }} 💝 - Valentine's Day | Open Se Same</title>

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

    <style>
        .product-detail-wrapper {
            padding-top: 100px;
            padding-bottom: 4rem;
        }

        .product-main-image {
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(196, 30, 58, 0.15);
            position: relative;
        }

        .product-main-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-main-image:hover img {
            transform: scale(1.03);
        }

        .product-thumbnails {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .product-thumb {
            width: 75px;
            height: 75px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-thumb:hover, .product-thumb.active {
            border-color: var(--valentine-deep-red);
            transform: translateY(-3px);
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content-card {
            background: #fff;
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 40px rgba(196, 30, 58, 0.08);
            border-top: 4px solid var(--valentine-rose);
        }

        .content-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--valentine-dark-red);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-card-title i {
            color: var(--valentine-rose);
        }

        .spec-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .spec-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px dashed #eee;
        }

        .spec-list li:last-child {
            border-bottom: none;
        }

        .spec-list li span:first-child {
            color: var(--valentine-dark-red);
            font-weight: 600;
        }

        .right-sticky {
            position: sticky;
            top: 100px;
        }

        .product-info-card {
            background: #fff;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 15px 50px rgba(196, 30, 58, 0.12);
            border: 2px solid rgba(196, 30, 58, 0.1);
        }

        .preserved-tag {
            display: inline-block;
            background: linear-gradient(135deg, #FFE4E1 0%, #FFC0CB 100%);
            color: var(--valentine-deep-red);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--valentine-dark);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--valentine-deep-red);
            margin-bottom: 1rem;
        }

        .product-price .old-price {
            font-size: 1.2rem;
            color: #999;
            text-decoration: line-through;
            margin-right: 0.5rem;
        }

        .product-price .discount-badge {
            background: var(--valentine-gradient);
            color: #fff;
            font-size: 0.75rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            margin-left: 0.5rem;
        }

        .valentine-offer-box {
            background: linear-gradient(135deg, #FFF0F5 0%, #FFE4E1 100%);
            border: 2px dashed var(--valentine-rose);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .valentine-offer-box i {
            color: var(--valentine-deep-red);
            margin-right: 0.3rem;
        }

        .valentine-offer-box span {
            color: var(--valentine-dark-red);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .product-overview {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .order-btn {
            background: var(--valentine-gradient);
            color: #fff;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            width: 100%;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(196, 30, 58, 0.3);
            margin-bottom: 1rem;
        }

        .order-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(196, 30, 58, 0.4);
            color: #fff;
        }

        .order-btn i {
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        .order-form {
            background: linear-gradient(135deg, #FFF8FA 0%, #fff 100%);
            border-radius: 20px;
            padding: 1.5rem;
            border: 2px solid var(--valentine-pink);
            margin-top: 1rem;
        }

        .order-form .form-control {
            border-radius: 12px;
            border: 2px solid #eee;
            padding: 0.7rem 1rem;
        }

        .order-form .form-control:focus {
            border-color: var(--valentine-rose);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
        }

        .order-form .form-label {
            color: var(--valentine-dark-red);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .help-box {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            margin-top: 1rem;
        }

        .help-box a {
            color: var(--valentine-deep-red);
            font-weight: 600;
        }

        .related-section {
            margin-top: 2rem;
        }

        .related-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--valentine-dark-red);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .related-title::after {
            content: ' 💕';
        }
    </style>
</head>
<body class="valentine-page">

<!-- Navigation -->
<nav class="navbar navbar-expand-lg valentine-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('valentine.landing') }}">
            <img src="{{ asset('images/logo-web-white.png') }}" alt="Open Se Same" class="valentine-logo">
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="{{ route('valentine.landing') }}">
                <i class="fas fa-arrow-left me-1"></i> Back to Collection
            </a>
        </div>
    </div>
</nav>

<!-- Product Detail -->
<div class="product-detail-wrapper">
    <div class="container">
        <div class="row g-4">

            <!-- LEFT COLUMN (8) - Photos, Overview, Specs, Related -->
            <div class="col-lg-8">

                <!-- Product Images -->
                <div class="product-main-image mb-3">
                    @if($product->primary_photo)
                        <img src="{{ asset('storage/' . $product->primary_photo) }}" alt="{{ $product->name }}" id="mainImage">
                    @elseif($product->activePhotos->count() > 0)
                        <img src="{{ asset('storage/' . $product->activePhotos->first()->photo_path) }}" alt="{{ $product->name }}"
                             id="mainImage">
                    @else
                        <div class="valentine-no-image" style="height: 500px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-heart fa-4x" style="color: var(--valentine-pink);"></i>
                        </div>
                    @endif
                </div>

                @if($product->activePhotos->count() > 0 || $product->primary_photo)
                    <div class="product-thumbnails">
                        @if($product->primary_photo)
                            <div class="product-thumb active" onclick="changeImage('{{ asset('storage/' . $product->primary_photo) }}', this)">
                                <img src="{{ asset('storage/' . $product->primary_photo) }}" alt="Primary">
                            </div>
                        @endif
                        @foreach($product->activePhotos as $photo)
                            <div class="product-thumb {{ !$product->primary_photo && $loop->first ? 'active' : '' }}"
                                 onclick="changeImage('{{ asset('storage/' . $photo->photo_path) }}', this)">
                                <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Gallery">
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Overview Card -->
                @if($product->description)
                    <div class="content-card mt-4">
                        <h4 class="content-card-title"><i class="fas fa-heart"></i> About This Rose</h4>
                        <p class="mb-0" style="color: #555; line-height: 1.8;">{{ $product->description }}</p>
                    </div>
                @endif

                <!-- Specifications Card -->
                @if($product->specifications && $product->specifications->count() > 0)
                    <div class="content-card">
                        <h4 class="content-card-title"><i class="fas fa-list-check"></i> Specifications</h4>
                        <ul class="spec-list">
                            @foreach($product->specifications as $spec)
                                <li>
                                    <span>{{ $spec->specification->name ?? 'Specification' }}</span>
                                    <span>{{ $spec->value }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Related Products -->
                @if($relatedProducts->count() > 0)
                    <div class="related-section">
                        <h4 class="related-title">You May Also Love</h4>
                        <div class="row g-4 valentine-products-row" style="scrollbar-width: thin;">
                            @foreach($relatedProducts as $related)
                                <div class="col-lg-6">
                                    <x-valentine-product-card :product="$related"/>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- RIGHT COLUMN (4) - Title, Price, Overview, Order -->
            <div class="col-lg-4">
                <div class="right-sticky">
                    <div class="product-info-card">

                        <!-- Preserved Tag -->
                        <span class="preserved-tag">
                                <i class="fas fa-infinity me-1"></i> Preserved Forever
                            </span>

                        <!-- Product Title -->
                        <h1 class="product-title">{{ $product->name }}</h1>

                        <!-- Price -->
                        <div class="product-price">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="old-price">৳{{ number_format($product->price) }}</span>
                                ৳{{ number_format($product->sale_price) }}
                                <span class="discount-badge"><i class="fas fa-heart"></i> Sale!</span>
                            @else
                                ৳{{ number_format($product->price) }}
                            @endif
                        </div>

                        <!-- Valentine Offer -->
                        <div class="valentine-offer-box">
                            <i class="fas fa-gift"></i>
                            <span>💝 Free gift wrapping included!</span>
                        </div>

                        <!-- Short Overview -->
                        @if($product->short_description)
                            <div class="product-overview">
                                {{ $product->short_description }}
                            </div>
                        @endif

                        <!-- Order Button -->
                        <div id="orderBtnContainer">
                            <button class="order-btn" id="showOrderForm">
                                <i class="fas fa-heart me-2"></i> Order Now 💝
                            </button>
                            <p class="text-center text-muted mb-0" style="font-size: 0.85rem;">
                                <i class="fas fa-truck me-1"></i> Free delivery above ৳1000
                            </p>
                        </div>

                        <!-- Order Form (Hidden) -->
                        <div id="orderFormContainer" style="display: none;">
                            <div class="order-form">
                                <h6 class="text-center mb-3" style="color: var(--valentine-dark-red);">
                                    <i class="fas fa-heart me-1"></i> Complete Your Order
                                </h6>
                                <form action="{{ route('valentine.placeOrder') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                                    <div class="mb-2">
                                        <label class="form-label mb-1"><i class="fas fa-user me-1"></i> Name *</label>
                                        <input type="text" name="name" class="form-control" required placeholder="Your name">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label mb-1"><i class="fas fa-phone me-1"></i> Phone *</label>
                                        <input type="tel" name="phone" class="form-control" required placeholder="01XXXXXXXXX">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label mb-1"><i class="fas fa-map-marker-alt me-1"></i> Address *</label>
                                        <textarea name="address" class="form-control" rows="2" required
                                                  placeholder="Delivery address"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label mb-1"><i class="fas fa-sort-numeric-up me-1"></i> Quantity *</label>
                                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="10" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label mb-1"><i class="fas fa-heart me-1"></i> Gift Message</label>
                                        <textarea name="message" class="form-control" rows="2" placeholder="Love message (optional)"></textarea>
                                    </div>
                                    <button type="submit" class="order-btn">
                                        <i class="fas fa-check me-2"></i> Place Order
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="cancelOrder">Cancel</button>
                                </form>
                            </div>
                        </div>

                        <!-- Help -->
                        <div class="help-box">
                            <p class="mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-phone me-1 text-danger"></i>
                                Call: <a href="tel:01886870406">01886870406</a>
                            </p>
                            <p class="mb-0 text-muted" style="font-size: 0.8rem;">Mon-Sat, 9AM-8PM</p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Footer -->
<footer class="valentine-footer">
    <div class="container">
        <div class="valentine-footer-bottom">
            <p class="mb-0">💝 Made with love by Open Se Same © {{ date('Y') }} | Happy Valentine's Day!</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('showOrderForm').addEventListener('click', function () {
        document.getElementById('orderFormContainer').style.display = 'block';
        document.getElementById('orderBtnContainer').style.display = 'none';
    });

    document.getElementById('cancelOrder').addEventListener('click', function () {
        document.getElementById('orderFormContainer').style.display = 'none';
        document.getElementById('orderBtnContainer').style.display = 'block';
    });

    function changeImage(src, element) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.product-thumb').forEach(t => t.classList.remove('active'));
        element.classList.add('active');
    }
</script>
</body>
</html>
