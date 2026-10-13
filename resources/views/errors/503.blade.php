@extends('errors.layout')

@section('page_title', '503 – Service Unavailable')
@section('code', '503')
@section('icon', 'fas fa-wrench')
@section('heading', 'Down for Maintenance')
@section('subtitle', 'We\'re currently performing scheduled maintenance to improve your experience.<br>We\'ll be back very shortly — thank you for your patience! 🙏')
@section('extra_css')
<style>
    .error-icon-bubble { animation: spin-wrench 2s ease-in-out infinite; }
    @keyframes spin-wrench { 0%,100%{transform:rotate(-10deg)} 50%{transform:rotate(10deg)} }
</style>
@endsection
