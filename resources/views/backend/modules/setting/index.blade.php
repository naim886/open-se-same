@extends('backend.layouts.app')

@section('content')
@php
    $old = old('settings', []);
    $val = fn (string $key) => $old[$key] ?? ($settings[$key] ?? '');
    $on  = fn (string $key) => filter_var($val($key), FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="row justify-content-center my-5">
    <div class="col-lg-10">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Settings</h4>
            <span class="text-muted" style="font-size:13px;">Delivery charges, invoices and notifications</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ── Delivery Charges ─────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-truck me-2"></i>Delivery Charges
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        These amounts are what customers are actually charged. Checkout only offers
                        <strong>Inside Dhaka</strong> and <strong>Outside Dhaka</strong>; the other areas are
                        available for manual (offline) orders. Set an area to <strong>0</strong> for free delivery.
                    </p>
                    <div class="row g-3">
                        @foreach($deliveryLabels as $location => $label)
                            @php $key = \App\Models\Order::DELIVERY_SETTING_KEYS[$location]; @endphp
                            <div class="col-md-4">
                                <label class="form-label" style="font-size:13px; font-weight:600;">{{ $label }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           name="settings[{{ $key }}]" value="{{ $val($key) }}" required>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Invoice & Branding ──────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-file-invoice me-2"></i>Invoice &amp; Branding
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Shown on generated invoices and courier delivery labels.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Company Name</label>
                            <input type="text" class="form-control" name="settings[invoice.company_name]"
                                   value="{{ $val('invoice.company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Company Phone</label>
                            <input type="text" class="form-control" name="settings[invoice.company_phone]"
                                   value="{{ $val('invoice.company_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Company Email</label>
                            <input type="email" class="form-control" name="settings[invoice.company_email]"
                                   value="{{ $val('invoice.company_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Footer Note</label>
                            <input type="text" class="form-control" name="settings[invoice.footer_note]"
                                   value="{{ $val('invoice.footer_note') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Company Address</label>
                            <textarea class="form-control" rows="2" name="settings[invoice.company_address]">{{ $val('invoice.company_address') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Terms &amp; Conditions</label>
                            <textarea class="form-control" rows="2" name="settings[invoice.terms]">{{ $val('invoice.terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Notifications ───────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-bell me-2"></i>Notifications
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="notif-oc"
                               name="settings[notification.order_confirmation]" value="1" @checked($on('notification.order_confirmation'))>
                        <label class="form-check-label" for="notif-oc">
                            Email the customer an order confirmation
                            <div class="text-muted" style="font-size:12px;">Sent when an order is placed and the customer supplied an email address.</div>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="notif-no"
                               name="settings[notification.admin_new_order]" value="1" @checked($on('notification.admin_new_order'))>
                        <label class="form-check-label" for="notif-no">
                            Alert admin on every new order
                            <div class="text-muted" style="font-size:12px;">Requires an admin notification email below.</div>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="notif-ls"
                               name="settings[notification.admin_low_stock]" value="1" @checked($on('notification.admin_low_stock'))>
                        <label class="form-check-label" for="notif-ls">
                            Alert admin when a product runs low on stock
                            <div class="text-muted" style="font-size:12px;">Sent once per product when it first crosses the threshold.</div>
                        </label>
                    </div>
                    <div class="col-md-6 px-0">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Admin Notification Email</label>
                        <input type="email" class="form-control" name="settings[notification.admin_email]"
                               value="{{ $val('notification.admin_email') }}" placeholder="admin@example.com">
                    </div>
                </div>
            </div>

            {{-- ── Inventory & CRM ─────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-warehouse me-2"></i>Inventory &amp; CRM
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Low Stock Threshold</label>
                            <input type="number" min="0" class="form-control"
                                   name="settings[inventory.low_stock_threshold]" value="{{ $val('inventory.low_stock_threshold') }}">
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Products with stock below this are flagged on the dashboard.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Repeat Customer After</label>
                            <input type="number" min="1" class="form-control"
                                   name="settings[crm.repeat_min_orders]" value="{{ $val('crm.repeat_min_orders') }}">
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Number of orders at which a customer is counted as a repeat buyer.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Settings
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
