@extends('errors.layout')

@section('page_title', '429 – Too Many Requests')
@section('code', '429')
@section('icon', 'fas fa-gauge-high')
@section('heading', 'Slow Down a Bit!')
@section('subtitle', 'You\'re making too many requests in a short period of time.<br>Please wait a moment before trying again.')
@section('extra_actions')
    <a href="javascript:history.back()" class="btn-outline-brand">
        <i class="fas fa-arrow-left"></i> Go Back
    </a>
@endsection
