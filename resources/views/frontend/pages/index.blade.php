<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Momo+Trust+Sans:wght@200..800&display=swap" rel="stylesheet">
     <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{asset('frontend/style/style.css')}}">
</head>
<body>

<section class="nav-bar">
    <div class="container-fluid">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3">
                    <a href="{{route('frontend.index')}}">
                        <img class="logo" src="{{asset('images/logo-web.png')}}" alt="Logo">
                    </a>
                </div>
                <div class="col-lg-5">
                    <div class="search-area">
                        <input type="search" placeholder="Search..." />
                        <button><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>

                </div>
                <div class="col-lg-4">
                    <p class="contact-text">আমাদের যে কোন পণ্য অর্ডার করতে যোগাযোগ করুন</p>
                    <div class="right-area">
                        <a href="https://wa.me/+8801886870406" target="_blank">
                            <i class="fa-brands fa-whatsapp"></i> 01886870406
                        </a>
                        <a href="https://m.me/opensesame1" target="_blank">
                            <i class="fa-brands fa-facebook-messenger"></i> Open Se Same
                        </a>
                        <a href="https://www.facebook.com/opensesame1" target="_blank">
                            <i class="fa-brands fa-facebook"></i> Open Se Same
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<nav class="nav">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <a href="">Home</a>
                <a href="">treasures </a>
                <a href="">Night Dress</a>
                <a href="">Baby Toys</a>
                <a href="">Baby Accessories</a>
                <a href="">About Us</a>
                <a href="">Contact Us</a>
            </div>
        </div>
    </div>
</nav>
<section class="hero">
    <img src="{{asset('images/banner/lingaree.png')}}" alt="Hero Image">
</section>
<section class="category my-5">
    <div class="container">
        <div class="row">
            @for($i=0 ; $i<4; $i++)
            <div class="col-lg-3">
                <a href="#" class="category-card">
                    <img class="category-image img-thumbnail" src="{{asset('images/category/rose.webp')}}" alt="Category Image">
                    <h3>treasures </h3>
                </a>
            </div>
            @endfor
        </div>
    </div>
</section>
<section class="product-block my-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between">
                    <h2 class="section-title">Top Products</h2>
                    <a href="" class="section-subtitle">View All</a>
                </div>

            </div>
            <div class="col-lg-3">
                <a href="#" class="product-card">
                    <img class="product-image img-thumbnail" src="{{asset('images/product/rose.webp')}}" alt="Product Image">
                    <div class="product-card-content">
                        <h4>Eternal Preserved Black Rose Flower With Acrylic Crystal Ring Box</h4>
                        <div class="star-review">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="price">
                            <span>৳ 1000</span>
                            <span>৳ 1200</span>
                        </div>
                        <a href="" class="add-to-cart">Add to Cart</a>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>






<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery.min.js') }}"></script>
<script src="{{asset('frontend/js/script.js')}}"></script>
</body>
</html>
