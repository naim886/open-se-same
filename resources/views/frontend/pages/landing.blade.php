<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Open Se Same - Coming Soon">
    <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.png')}}">
    <meta property="og:title" content="Open Se Same"/>
    <meta property="og:description" content="Open Se Same - Coming Soon"/>
    <meta property="og:image" content="{{asset('images/og.png')}}"/>
    <title>Open Se Same | Home</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="{{ minified_asset('frontend/css/style.css') }}">
</head>
<body>


<nav class="navbar navbar-expand-lg brand-navbar">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="{{asset('images/logo-white.png')}}" alt="Open Se Same Logo" class="logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Premium Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Just for you</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Others</a>
                </li>
            </ul>
            <div class="search-area">

                <button class="search-icon" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                <div class="search-input">
                    <input class="search-input-field" type="search" placeholder="Search" aria-label="Search">
                </div>


            </div>
            <div class="user-area">
                <i class="fa-solid fa-heart"></i>
                <i class="fa-solid fa-cart-shopping"></i>
                <i class="fa-solid fa-circle-user"></i>
            </div>
        </div>
    </div>
</nav>


<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery.min.js') }}"></script>
</body>
</html>
