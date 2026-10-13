@extends('errors.layout')

@section('page_title', '403 – Access Denied')
@section('code', '403')
@section('icon', 'fas fa-lock')
@section('heading', 'Access Denied')
@section('subtitle', 'You don\'t have permission to view this page.<br>If you believe this is a mistake, please contact us.')
@section('extra_actions')
    <a href="javascript:history.back()" class="btn-outline-brand">
        <i class="fas fa-arrow-left"></i> Go Back
    </a>
@endsection
