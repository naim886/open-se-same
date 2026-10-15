@extends('backend.layouts.app')

@section('title', 'Edit Coupon')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    <i class="fas fa-ticket me-2"></i>Edit Coupon
                    <span class="badge bg-secondary ms-1">{{ $coupon->code }}</span>
                </span>
                <span class="small text-muted">
                    Used {{ number_format($coupon->used_count) }}
                    time{{ $coupon->used_count === 1 ? '' : 's' }}
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('coupon.update', $coupon->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('backend.modules.coupon._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('coupon.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
