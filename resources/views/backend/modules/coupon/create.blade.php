@extends('backend.layouts.app')

@section('title', 'New Coupon')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-ticket me-2"></i>New Coupon
            </div>
            <div class="card-body">
                <form action="{{ route('coupon.store') }}" method="POST">
                    @csrf

                    @include('backend.modules.coupon._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create Coupon
                        </button>
                        <a href="{{ route('coupon.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
