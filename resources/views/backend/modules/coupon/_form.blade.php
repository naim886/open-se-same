@php
    use App\Models\Coupon;

    $coupon  = $coupon ?? null;
    $typeVal = old('type', $coupon->type ?? Coupon::TYPE_FIXED);
    $percent = $typeVal === Coupon::TYPE_PERCENT;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-600">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control text-uppercase"
               value="{{ old('code', $coupon->code ?? '') }}" required maxlength="50">
        <div class="text-muted mt-1" style="font-size:12px;">Customers enter this at checkout. Stored uppercase.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-600">Description</label>
        <input type="text" name="description" class="form-control"
               value="{{ old('description', $coupon->description ?? '') }}" maxlength="255"
               placeholder="Internal note, e.g. Eid campaign">
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-600">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select" id="couponType">
            @foreach(Coupon::TYPE_LIST as $val => $label)
                <option value="{{ $val }}" {{ $typeVal === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" name="value" class="form-control"
               value="{{ old('value', $coupon->value ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Max Discount (৳)</label>
        <input type="number" step="0.01" min="0" name="max_discount" id="maxDiscount" class="form-control"
               value="{{ old('max_discount', $coupon->max_discount ?? '') }}" @disabled(! $percent)>
        <div class="text-muted mt-1" style="font-size:12px;">Caps percent coupons</div>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Min Order (৳)</label>
        <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control"
               value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label small fw-600">Total Usage Limit</label>
        <input type="number" min="1" name="usage_limit" class="form-control"
               value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}">
        <div class="text-muted mt-1" style="font-size:12px;">Blank = unlimited</div>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Per Customer Limit</label>
        <input type="number" min="0" name="per_customer_limit" class="form-control"
               value="{{ old('per_customer_limit', $coupon->per_customer_limit ?? 1) }}">
        <div class="text-muted mt-1" style="font-size:12px;">0 = unlimited</div>
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Starts At</label>
        <input type="datetime-local" name="starts_at" class="form-control"
               value="{{ old('starts_at', optional($coupon->starts_at ?? null)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-600">Ends At</label>
        <input type="datetime-local" name="ends_at" class="form-control"
               value="{{ old('ends_at', optional($coupon->ends_at ?? null)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="isActive"
                   name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))>
            <label class="form-check-label" for="isActive">Active — redeemable at checkout</label>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const typeSelect = document.getElementById('couponType');
    const maxDiscount = document.getElementById('maxDiscount');

    if (!typeSelect || !maxDiscount) return;

    typeSelect.addEventListener('change', function () {
        const isPercent = this.value === 'percent';
        maxDiscount.disabled = !isPercent;
        if (!isPercent) maxDiscount.value = '';
    });
})();
</script>
@endpush
