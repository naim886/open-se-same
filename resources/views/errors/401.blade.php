@extends('errors.layout')

@section('page_title', '401 – Unauthorized')
@section('code', '401')
@section('icon', 'fas fa-user-lock')
@section('heading', 'Unauthorized Access')
@section('subtitle', 'You need to be logged in to access this page.<br>Please sign in and try again.')
@section('extra_actions')
    <a href="{{ url('/login') }}" class="btn-outline-brand">
        <i class="fas fa-sign-in-alt"></i> Sign In
    </a>
@endsection
