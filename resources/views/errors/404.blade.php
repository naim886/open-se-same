@extends('errors.layout')

@section('page_title', '404 � Page Not Found')
@section('code', '404')
@section('icon', 'fas fa-search')
@section('heading', 'Oops! Page Not Found')
@section('subtitle', 'The page you\'re looking for seems to have wandered off.<br>It may have been moved, deleted, or never existed at all.')
@section('extra_actions')
    <a href="{{ url('/products') }}" class="btn-outline-brand">
        <i class="fas fa-shopping-bag"></i> Browse Products
    </a>
@endsection
