@extends('errors.layout')

@section('page_title', '419 – Session Expired')
@section('code', '419')
@section('icon', 'fas fa-clock-rotate-left')
@section('heading', 'Session Expired')
@section('subtitle', 'Your session has expired — this usually happens after a period of inactivity.<br>Please refresh the page or try your action again.')
@section('extra_actions')
    <a href="javascript:location.reload()" class="btn-outline-brand">
        <i class="fas fa-rotate-right"></i> Refresh Page
    </a>
@endsection
