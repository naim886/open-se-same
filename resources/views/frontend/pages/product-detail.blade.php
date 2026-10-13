<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Open Se Same</title>
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
            line-height: 1.6;
            color: #2c3e50;
        }

        .product-detail-container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .main-image-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
        }

        .main-product-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .main-product-image:hover {
            transform: scale(1.05);
        }

        .thumbnail-container {
            margin-top: 1rem;
        }

        .img-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .img-thumbnail:hover,
        .img-thumbnail.active {
            border-color: #a62b26;
            transform: scale(1.1);
        }

        .product-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .product-price {
            color: #a62b26;
            font-weight: 700;
        }

        .product-category {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            display: inline-block;
            font-size: 0.9rem;
        }

        .btn-order {
            background: linear-gradient(135deg, #a62b26 0%, #8b2420 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(166, 43, 38, 0.3);
            color: white;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #a62b26 !important;
            text-decoration: none;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #a62b26;
        }

        .accordion-button {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
        }

        .accordion-button:not(.collapsed) {
            background: #a62b26;
            color: white;
        }

        .no-image-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 500px;
            border-radius: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('frontend.index') }}">
                <i class="fas fa-home me-2"></i>Open Se Same
            </a>

            <div class="navbar-nav ms-auto">
                <a href="{{ route('frontend.index') }}" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container product-detail-container">
        <div class="row g-5">
            <!-- Product Images Section -->
            <div class="col-lg-7">
                <div class="main-image-container mb-3">
                    @if($product->primary_photo)
                        <img src="{{ asset('storage/' . $product->primary_photo) }}" alt="{{ $product->name }}" class="main-product-image" id="mainImage">
                    @elseif($product->activePhotos->count() > 0)
                        <img src="{{ asset('storage/' . $product->activePhotos->first()->photo_path) }}" alt="{{ $product->name }}" class="main-product-image" id="mainImage">
                    @else
                        <div class="no-image-placeholder">
                            <div class="text-center">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <p>No Image Available</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if($product->activePhotos->count() > 0)
                <div class="thumbnail-container d-flex justify-content-start gap-2 flex-wrap">
                    @if($product->primary_photo)
                        <img src="{{ asset('storage/' . $product->primary_photo) }}"
                             alt="Primary Image"
                             class="img-thumbnail active"
                             onclick="changeImage('{{ asset('storage/' . $product->primary_photo) }}')">
                    @endif

                    @foreach($product->activePhotos as $index => $photo)
                        <img src="{{ asset('storage/' . $photo->photo_path) }}"
                             alt="Gallery Image {{ $index + 1 }}"
                             class="img-thumbnail {{ !$product->primary_photo && $index === 0 ? 'active' : '' }}"
                             onclick="changeImage('{{ asset('storage/' . $photo->photo_path) }}')">
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Product Details Section -->
            <div class="col-lg-5">
                <div class="mb-3">
                    <span class="product-category">{{ $product->category->name ?? 'Uncategorized' }}</span>
                </div>

                <h1 class="product-title mb-3">{{ $product->name }}</h1>

                <div class="product-price h3 mb-4">
                    @if($product->sale_price && $product->sale_price < $product->price)
                        <span class="text-decoration-line-through text-muted me-2">${{ number_format($product->price, 2) }}</span>
                        <span>${{ number_format($product->sale_price, 2) }}</span>
                        <span class="badge bg-danger ms-2">Sale!</span>
                    @else
                        ${{ number_format($product->price, 2) }}
                    @endif
                </div>

                @if($product->short_description)
                <div class="mb-4">
                    <h5>Overview</h5>
                    <p class="text-muted">{{ $product->short_description }}</p>
                </div>
                @endif

                @if($product->description)
                <div class="mb-4">
                    <h5>Description</h5>
                    <p>{{ $product->description }}</p>
                </div>
                @endif

                <!-- Order Now Button -->
                <div class="d-grid gap-2 mb-4">
                    <button class="btn btn-order btn-lg" type="button" id="orderNowBtn">
                        <i class="fas fa-shopping-bag me-2"></i> Order Now
                    </button>
                </div>

                <!-- Order Form (Hidden by default) -->
                <div id="orderFormContainer" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Place Your Order</h5>
                            <form method="POST" action="{{ route('frontend.placeOrder') }}" id="orderForm">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="customer_name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" id="customer_phone" name="phone" required>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Address *</label>
                                    <textarea class="form-control" id="customer_address" name="address" rows="3" required></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check me-2"></i> Place Order
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancelOrder">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Product Information Accordion -->
                <div class="accordion mt-4" id="productInfoAccordion">
                    @if($product->specifications && $product->specifications->count() > 0)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSpecs">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecs" aria-expanded="true" aria-controls="collapseSpecs">
                                <i class="fas fa-list me-2"></i> Specifications
                            </button>
                        </h2>
                        <div id="collapseSpecs" class="accordion-collapse collapse show" aria-labelledby="headingSpecs" data-bs-parent="#productInfoAccordion">
                            <div class="accordion-body">
                                @foreach($product->specifications as $spec)
                                    <div class="row mb-2">
                                        <div class="col-6"><strong>{{ $spec->specification->name ?? 'Specification' }}:</strong></div>
                                        <div class="col-6">{{ $spec->value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingShipping">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping" aria-expanded="false" aria-controls="collapseShipping">
                                <i class="fas fa-shipping-fast me-2"></i> Shipping & Returns
                            </button>
                        </h2>
                        <div id="collapseShipping" class="accordion-collapse collapse" aria-labelledby="headingShipping" data-bs-parent="#productInfoAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li>Free standard shipping on orders over $100</li>
                                    <li>Express shipping available</li>
                                    <li>Easy 30-day returns</li>
                                    <li>Professional installation available</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSupport">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                                <i class="fas fa-headset me-2"></i> Customer Support
                            </button>
                        </h2>
                        <div id="collapseSupport" class="accordion-collapse collapse" aria-labelledby="headingSupport" data-bs-parent="#productInfoAccordion">
                            <div class="accordion-body">
                                <p>Need help? Contact our customer support team:</p>
                                <ul>
                                    <li><i class="fas fa-phone me-2"></i> 01886870406</li>
                                    <li><i class="fas fa-envelope me-2"></i> info@opensesame.com</li>
                                    <li><i class="fas fa-clock me-2"></i> Mon-Fri: 9AM-6PM</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script>
        // Order form functionality
        document.getElementById('orderNowBtn').addEventListener('click', function() {
            document.getElementById('orderFormContainer').style.display = 'block';
            this.style.display = 'none';
        });

        document.getElementById('cancelOrder').addEventListener('click', function() {
            document.getElementById('orderFormContainer').style.display = 'none';
            document.getElementById('orderNowBtn').style.display = 'block';
            document.getElementById('orderForm').reset();
        });

        // Image change functionality
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
            // Update active thumbnail
            document.querySelectorAll('.img-thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Show success message if redirected after order
        @if(session('success'))
            alert('{{ session('success') }}');
        @endif
    </script>
</body>
</html>
