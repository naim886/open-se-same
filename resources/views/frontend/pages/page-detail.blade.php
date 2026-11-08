@extends('frontend.layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - OpenSesame BD')
@if($page->meta_description)
@section('meta_description', $page->meta_description)
@else
@section('meta_description', $page->title . ' - Read more on OpenSesame BD.')
@endif
@section('og_title', $page->meta_title ?? $page->title . ' - OpenSesame BD')
@section('og_description', $page->meta_description ?? $page->title . ' - Read more on OpenSesame BD.')
@section('robots', 'index, follow')

@php
    $lede = trim(strip_tags($page->meta_description ?? ''));
    if ($lede === '') {
        $lede = \Illuminate\Support\Str::limit(trim(strip_tags($page->description ?? '')), 170);
    }
    $quickPages = $relatedPages ?? collect();
@endphp

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::webPage()
    ->name($page->meta_title ?? $page->title.' - OpenSesame BD')
    ->description($page->meta_description ?? $page->title.' - Read more on OpenSesame BD.')
    ->url(route('shop.page', $page->slug))
    ->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name($page->title)->item(route('shop.page', $page->slug)),
])->toScript();
@endphp
@endsection

@section('content')

{{-- Hero --}}
<section class="cms-page-hero">
    <div class="container">
        <nav class="cms-hero-breadcrumb" aria-label="breadcrumb">
            <a href="{{ route('shop.home') }}"><i class="fas fa-home" aria-hidden="true"></i> Home</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">{{ $page->title }}</span>
        </nav>

        <span class="cms-page-eyebrow"><i class="far fa-file-lines" aria-hidden="true"></i> OpenSesame BD</span>
        <h1 class="cms-page-title">{{ $page->title }}</h1>

        @if($lede !== '')
            <p class="cms-page-lede">{{ $lede }}</p>
        @endif

        @if($page->updated_at)
            <span class="cms-page-updated">
                <i class="far fa-calendar-check" aria-hidden="true"></i>
                Last updated {{ $page->updated_at->format('d M Y') }}
            </span>
        @endif
    </div>
</section>

{{-- Main content --}}
<section class="cms-page-section">
    <div class="container">
        <div class="row g-4">

            <div class="col-lg-8">
                <article class="cms-page-card">
                    @if($page->cover_photo)
                        <img src="{{ asset('storage/' . $page->cover_photo) }}" alt="{{ $page->title }}" class="cms-page-cover">
                    @endif

                    <div class="cms-page-body">
                        @if($page->description)
                            <div class="page-content">
                                {!! $page->description !!}
                            </div>
                        @else
                            <div class="cms-empty-content">
                                <i class="far fa-file-lines" aria-hidden="true"></i>
                                <h2>Content coming soon</h2>
                                <p>We are preparing this page. Please check back shortly.</p>
                            </div>
                        @endif
                    </div>
                </article>
            </div>

            <aside class="col-lg-4">
                <div class="cms-side-card">
                    <h3><i class="fas fa-compass" aria-hidden="true"></i> Quick Links</h3>
                    <ul class="cms-page-links">
                        <li>
                            <a href="{{ route('shop.home') }}">
                                <span>Home</span>
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </li>
                        @foreach($quickPages as $quickPage)
                            <li>
                                <a href="{{ route('shop.page', $quickPage->slug) }}">
                                    <span>{{ $quickPage->title }}</span>
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="cms-side-card cms-support-card">
                    <div class="cms-support-icon"><i class="fas fa-headset" aria-hidden="true"></i></div>
                    <h3>Need help?</h3>
                    <p>Our customer support team is available 24/7 for orders, delivery and product questions.</p>
                    <a href="tel:+8801826339988"><i class="fas fa-phone" aria-hidden="true"></i> 01826339988</a>
                    <a href="mailto:opensesamebd@gmail.com"><i class="fas fa-envelope" aria-hidden="true"></i> opensesamebd@gmail.com</a>
                </div>
            </aside>

        </div>
    </div>
</section>

@endsection
