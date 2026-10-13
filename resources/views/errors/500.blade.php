@extends('errors.layout')

@section('page_title', '500 – Server Error')
@section('code', '500')
@section('icon', 'fas fa-triangle-exclamation')
@section('heading', 'Internal Server Error')
@section('subtitle', 'Something went wrong on our end and we couldn\'t process your request.<br>Our team has been notified. Please try again in a few moments.')
@section('extra_actions')
    <a href="javascript:location.reload()" class="btn-outline-brand">
        <i class="fas fa-rotate-right"></i> Try Again
    </a>
@endsection
