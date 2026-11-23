@extends('errors.layout')

@section('page_title', '403 – Access Denied')
@section('code', '403')
@section('icon', 'fas fa-lock')
@section('heading', 'Access Denied')

@if(request()->is('payment/*'))
    @section('subtitle')
        This payment link has expired or is no longer valid.<br>
        Search for your order on the <strong>Track Order</strong> page and use
        <strong>Pay Now</strong> to try the payment again.
    @endsection

    @section('extra_actions')
        <a href="{{ route('shop.track-order') }}" class="btn-primary-brand">
            <i class="fas fa-search"></i> Track Order
        </a>
        <a href="javascript:history.back()" class="btn-outline-brand">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
    @endsection
@else
    @section('subtitle', 'You don\'t have permission to view this page.<br>If you believe this is a mistake, please contact us.')

    @section('extra_actions')
        <a href="javascript:history.back()" class="btn-outline-brand">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
    @endsection
@endif
