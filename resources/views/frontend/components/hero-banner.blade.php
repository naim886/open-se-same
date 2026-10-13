<!-- Hero Banner Section -->
@if($banners && $banners->count() > 0)
<section id="hero-banner" class="hero-banner-section">
    <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <!-- Carousel Indicators -->
        @if($banners->count() > 1)
        <div class="carousel-indicators">
            @foreach($banners as $index => $banner)
            <button type="button"
                    data-bs-target="#bannerCarousel"
                    data-bs-slide-to="{{ $index }}"
                    class="{{ $index === 0 ? 'active' : '' }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="Slide {{ $index + 1 }}">
            </button>
            @endforeach
        </div>
        @endif

        <!-- Carousel Items -->
        <div class="carousel-inner">
            @foreach($banners as $index => $banner)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <div class="banner-slide position-relative">
                    <!-- Banner Image -->
                    <div class="banner-image">
                        <img src="{{ asset('storage/' . $banner->image) }}"
                             class="d-block w-100"
                             alt="{{ $banner->title }}"
                             style="height: 600px; object-fit: cover;">
                    </div>

                    <!-- Banner Overlay -->
                    <div class="banner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center"
                         style="background: rgba(0,0,0,0.4);">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-8 col-xl-6">
                                    <div class="banner-content text-white">
                                        <h1 class="banner-title display-4 fw-bold mb-3"
                                            data-aos="fade-up"
                                            data-aos-delay="100">
                                            {{ $banner->title }}
                                        </h1>

                                        @if($banner->description)
                                        <p class="banner-description lead mb-4"
                                           data-aos="fade-up"
                                           data-aos-delay="200">
                                            {{ $banner->description }}
                                        </p>
                                        @endif

                                        @if($banner->button_text && $banner->button_url)
                                        <div class="banner-action"
                                             data-aos="fade-up"
                                             data-aos-delay="300">
                                            <a href="{{ $banner->button_url }}"
                                               class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg">
                                                {{ $banner->button_text }}
                                                <i class="fas fa-arrow-right ms-2"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Carousel Controls -->
        @if($banners->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        @endif
    </div>
</section>

<style>
.hero-banner-section {
    position: relative;
    z-index: 1;
}

.banner-slide {
    min-height: 600px;
}

.banner-image img {
    width: 100%;
    height: 600px;
    object-fit: cover;
}

.banner-content {
    z-index: 10;
    position: relative;
}

.banner-title {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    line-height: 1.2;
}

.banner-description {
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    font-size: 1.25rem;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    opacity: 0.8;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
}

.carousel-indicators {
    bottom: 20px;
}

.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .banner-slide {
        min-height: 400px;
    }

    .banner-image img {
        height: 400px;
    }

    .banner-title {
        font-size: 2rem;
    }

    .banner-description {
        font-size: 1rem;
    }

    .btn-lg {
        padding: 0.75rem 2rem !important;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .banner-slide {
        min-height: 300px;
    }

    .banner-image img {
        height: 300px;
    }

    .banner-title {
        font-size: 1.75rem;
    }
}
</style>
@endif
