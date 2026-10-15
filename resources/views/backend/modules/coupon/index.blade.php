@extends('backend.layouts.app')

@section('title', 'Coupons')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-ticket me-2 text-primary"></i>Coupons</h4>
            <div class="small text-muted mt-1">
                Discount codes customers can apply at checkout.
            </div>
        </div>
        <a href="{{ route('coupon.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>New Coupon
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('coupon.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                    <label class="form-label mb-1 small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ $search }}" placeholder="Code or description">
                </div>
                <div>
                    <label class="form-label mb-1 small">Status</label>
                    <select name="status" class="form-select form-select-sm" style="min-width:150px;">
                        <option value="">All</option>
                        <option value="redeemable" {{ $status === 'redeemable' ? 'selected' : '' }}>Redeemable now</option>
                        <option value="inactive"   {{ $status === 'inactive'   ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                @if($search || $status)
                    <a href="{{ route('coupon.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── List ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($coupons->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-ticket fa-2x mb-2 d-block"></i>No coupons found.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Discount</th>
                            <th class="text-end">Min Order</th>
                            <th class="text-end">Usage</th>
                            <th>Window</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3" style="width:150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold" style="font-size:13px;">{{ $coupon->code }}</div>
                                @if($coupon->description)
                                    <div class="text-muted" style="font-size:11px;">{{ $coupon->description }}</div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $coupon->valueLabel() }}</td>
                            <td class="text-end">
                                {{ $coupon->min_order_amount > 0 ? '৳' . number_format((float) $coupon->min_order_amount, 0) : '—' }}
                            </td>
                            <td class="text-end">
                                {{ number_format($coupon->used_count) }}
                                @if($coupon->usage_limit !== null)
                                    / {{ number_format($coupon->usage_limit) }}
                                @endif
                            </td>
                            <td class="small text-muted">
                                @if($coupon->starts_at || $coupon->ends_at)
                                    {{ $coupon->starts_at?->format('d M Y H:i') ?? 'any' }}
                                    →
                                    {{ $coupon->ends_at?->format('d M Y H:i') ?? 'any' }}
                                @else
                                    Always
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $label = $coupon->statusLabel();
                                    $color = match($label) {
                                        'Active'    => 'success',
                                        'Scheduled' => 'info',
                                        'Used up'   => 'warning',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('coupon.edit', $coupon->id) }}" class="btn btn-sm btn-outline-primary"
                                   title="Edit"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('coupon.toggle', $coupon->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form action="{{ route('coupon.destroy', $coupon->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-3">{{ $coupons->links() }}</div>

</div>
@endsection
