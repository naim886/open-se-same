@extends('frontend.layouts.app')

@section('title', $page->meta_title ?? $page->title . ' - Open Se Same')
@if($page->meta_description)
@section('meta_description', $page->meta_description)
@else
@section('meta_description', $page->title . ' - Read more on Open Se Same.')
@endif
@section('og_title', $page->meta_title ?? $page->title . ' - Open Se Same')
@section('og_description', $page->meta_description ?? $page->title . ' - Read more on Open Se Same.')
@section('robots', 'index, follow')

@section('schema')
@php
use Spatie\SchemaOrg\Schema;
echo Schema::webPage()
    ->name($page->meta_title ?? $page->title.' - Open Se Same')
    ->description($page->meta_description ?? $page->title.' - Read more on Open Se Same.')
    ->url(route('shop.page', $page->slug))
    ->toScript();
echo Schema::breadcrumbList()->itemListElement([
    Schema::listItem()->position(1)->name('Home')->item(route('shop.home')),
    Schema::listItem()->position(2)->name($page->title)->item(route('shop.page', $page->slug)),
])->toScript();
@endphp
@endsection

@section('content')

<!-- Breadcrumb -->
<div class="shop-breadcrumb">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}">Home</a></li>
                <li class="breadcrumb-item active">{{ $page->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <!-- Cover Image -->
                @if($page->cover_photo)
                    <img src="{{ asset('storage/' . $page->cover_photo) }}" alt="{{ $page->title }}"
                         class="w-100 rounded mb-4" style="max-height:320px; object-fit:cover;">
                @endif

                <!-- Title -->
                <h1 class="fw-bold mb-4" style="
                    color:var(--text-dark);
                    font-size:28px;
                    line-height:1.35;
                    padding-bottom:16px;
                    border-bottom:2px solid var(--border);
                ">
                    {{ $page->title }}
                </h1>

                <!-- Content -->
                <div class="page-content" style="
                    font-size:15px;
                    line-height:1.9;
                    color:var(--text-body);
                ">
                    {!! $page->description !!}
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
