@extends('customer.layouts.panel')

@section('title', 'Address Book — Open Se Same')

@section('extra_css')
@parent
<style>
.address-card {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 16px 18px;
    position: relative;
    transition: var(--transition);
    background: #fff;
}
.address-card:hover { border-color: var(--primary); box-shadow: 0 2px 12px rgba(2,123,80,.1); }
.address-card.is-default { border-color: var(--primary); background: rgba(2,123,80,.04); }
.address-label-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    background: var(--bg-light);
    color: var(--text-body);
    border: 1px solid var(--border);
    margin-bottom: 6px;
}
.address-card.is-default .address-label-badge {
    background: var(--primary-gradient);
    color: #fff;
    border-color: transparent;
}
.address-default-badge {
    position: absolute;
    top: 12px;
    right: 14px;
    font-size: 11px;
    font-weight: 700;
    color: var(--primary);
    background: rgba(2,123,80,.1);
    border-radius: 20px;
    padding: 2px 10px;
}
.address-action-btn {
    font-size: 12px;
    padding: 5px 12px;
    border-radius: 6px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-body);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-block;
}
.address-action-btn:hover { border-color: var(--primary); color: var(--primary); }
.address-action-btn.btn-delete:hover { border-color: #dc3545; color: #dc3545; }
</style>
@endsection

@section('panel_content')

<div class="panel-section-title">
    <i class="fas fa-address-book"></i> Address Book
</div>

<!-- Add New Address Button -->
<div class="mb-4 d-flex justify-content-between align-items-center">
    <p class="text-muted mb-0" style="font-size:13px;">
        Save delivery addresses for faster checkout.
    </p>
    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addAddressModal" style="padding:8px 18px; font-size:13px;">
        <i class="fas fa-plus me-1"></i> Add New Address
    </button>
</div>

@if($addresses->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-map-marker-alt" style="font-size:48px; color:var(--border);"></i>
        <p class="mt-3 text-muted">No saved addresses yet.</p>
        <button class="btn-primary-custom mt-1" data-bs-toggle="modal" data-bs-target="#addAddressModal">
            Add Your First Address
        </button>
    </div>
@else
    <div class="row g-3">
        @foreach($addresses as $addr)
        <div class="col-md-6">
            <div class="address-card {{ $addr->is_default ? 'is-default' : '' }}">
                @if($addr->is_default)
                    <span class="address-default-badge"><i class="fas fa-check-circle me-1"></i>Default</span>
                @endif
                <div class="address-label-badge">{{ $addr->label }}</div>
                <div style="font-weight:700; font-size:14px; color:var(--text-dark); margin-bottom:2px;">
                    {{ $addr->recipient_name }}
                </div>
                <div style="font-size:13px; color:var(--text-body); margin-bottom:2px;">
                    <i class="fas fa-phone fa-xs me-1" style="color:var(--primary);"></i>{{ $addr->phone }}
                </div>
                <div style="font-size:13px; color:var(--text-muted); line-height:1.5;">
                    <i class="fas fa-map-marker-alt fa-xs me-1" style="color:var(--primary);"></i>{{ $addr->address }}
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <button class="address-action-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editAddressModal{{ $addr->id }}">
                        <i class="fas fa-pencil-alt me-1"></i> Edit
                    </button>
                    @if(!$addr->is_default)
                    <form method="POST" action="{{ route('customer.addresses.setDefault', $addr->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="address-action-btn">
                            <i class="fas fa-star me-1"></i> Set Default
                        </button>
                    </form>
                    <form method="POST" action="{{ route('customer.addresses.destroy', $addr->id) }}"
                          onsubmit="return confirm('Remove this address?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="address-action-btn btn-delete">
                            <i class="fas fa-trash me-1"></i> Remove
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editAddressModal{{ $addr->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="font-size:16px;">Edit Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('customer.addresses.update', $addr->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-body panel-form">
                            @include('customer.partials.address-form', ['a' => $addr])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-primary-custom" style="padding:8px 20px; font-size:13px;">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:16px;">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customer.addresses.store') }}">
                @csrf
                <div class="modal-body panel-form">
                    @include('customer.partials.address-form', ['a' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom" style="padding:8px 20px; font-size:13px;">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('addAddressModal'));
        modal.show();
    });
</script>
@endif

@endsection
