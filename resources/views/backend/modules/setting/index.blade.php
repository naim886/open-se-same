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
            <span class="text-muted" style="font-size:13px;">Delivery charges, courier credentials, invoices and notifications</span>
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
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">BIN</label>
                            <input type="text" class="form-control" name="settings[invoice.company_bin]"
                                   value="{{ $val('invoice.company_bin') }}" placeholder="004261738-0208">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Mushak</label>
                            <input type="text" class="form-control" name="settings[invoice.company_mushak]"
                                   value="{{ $val('invoice.company_mushak') }}" placeholder="Mushak-6.3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Website</label>
                            <input type="text" class="form-control" name="settings[invoice.company_website]"
                                   value="{{ $val('invoice.company_website') }}" placeholder="opensesame.com.bd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">VAT Footer Note</label>
                            <input type="text" class="form-control" name="settings[invoice.vat_note]"
                                   value="{{ $val('invoice.vat_note') }}" placeholder="*** VAT INCLUDED IN THE PRODUCT PRICE ***">
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

            {{-- ── Courier Integrations ────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-truck me-2"></i>Courier Integrations
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Credentials used when an order is sent to courier and when courier webhooks
                        update an order. Pathao access tokens are stored automatically.
                    </p>

                    <h6 class="fw-bold mb-3"><i class="fas fa-box me-1"></i>Steadfast</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Base URL</label>
                            <input type="url" class="form-control" name="settings[courier.steadfast.base_url]"
                                   value="{{ $val('courier.steadfast.base_url') }}"
                                   placeholder="https://portal.packzy.com/api/v1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">API Key</label>
                            <input type="password" class="form-control" name="settings[courier.steadfast.api_key]"
                                   value="{{ $val('courier.steadfast.api_key') }}" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Secret Key</label>
                            <input type="password" class="form-control" name="settings[courier.steadfast.secret_key]"
                                   value="{{ $val('courier.steadfast.secret_key') }}" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Webhook URL</label>
                            <input type="text" class="form-control" value="{{ route('webhooks.steadfast') }}" readonly>
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Use the API key above as the Bearer token for Steadfast webhooks.
                            </div>
                        </div>
                    </div>

                    <hr style="border-color:rgba(255,255,255,.12);">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-motorcycle me-1"></i>Pathao</h6>
                        <a href="https://merchant.pathao.com/courier/developer-api" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt me-1"></i> Developer API
                        </a>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Base URL</label>
                            <input type="url" class="form-control" name="settings[courier.pathao.base_url]"
                                   value="{{ $val('courier.pathao.base_url') }}"
                                   placeholder="https://api-hermes.pathao.com">
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Production: https://api-hermes.pathao.com — Sandbox: https://courier-api-sandbox.pathao.com
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Client ID</label>
                            <input type="text" class="form-control" name="settings[courier.pathao.client_id]"
                                   value="{{ $val('courier.pathao.client_id') }}" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Client Secret</label>
                            <input type="password" class="form-control" name="settings[courier.pathao.client_secret]"
                                   value="{{ $val('courier.pathao.client_secret') }}" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Merchant Email / Username</label>
                            <input type="text" class="form-control" name="settings[courier.pathao.username]"
                                   value="{{ $val('courier.pathao.username') }}" autocomplete="off"
                                   placeholder="merchant@example.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Merchant Password</label>
                            <input type="password" class="form-control" name="settings[courier.pathao.password]"
                                   value="{{ $val('courier.pathao.password') }}" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Store ID</label>
                            <input type="text" class="form-control" name="settings[courier.pathao.store_id]"
                                   value="{{ $val('courier.pathao.store_id') }}" placeholder="e.g. 512138">
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Find it in Pathao Merchant → Stores (Id column).
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Delivery Type</label>
                            <select class="form-select" name="settings[courier.pathao.delivery_type]">
                                <option value="48" @selected($val('courier.pathao.delivery_type') === '48')>Normal Delivery (48)</option>
                                <option value="12" @selected($val('courier.pathao.delivery_type') === '12')>On Demand Delivery (12)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Item Type</label>
                            <select class="form-select" name="settings[courier.pathao.item_type]">
                                <option value="2" @selected($val('courier.pathao.item_type') === '2')>Parcel (2)</option>
                                <option value="1" @selected($val('courier.pathao.item_type') === '1')>Document (1)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Item Weight (kg)</label>
                            <input type="number" step="0.1" min="0.5" max="10" class="form-control"
                                   name="settings[courier.pathao.item_weight]"
                                   value="{{ $val('courier.pathao.item_weight') }}" placeholder="0.5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Webhook Secret</label>
                            <input type="password" class="form-control" name="settings[courier.pathao.webhook_secret]"
                                   value="{{ $val('courier.pathao.webhook_secret') }}" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Webhook Callback URL</label>
                            <input type="text" class="form-control" value="{{ route('webhooks.pathao') }}" readonly>
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Add this callback URL in Pathao Merchant → Developer's API → Webhook Integration,
                                and use the same secret above.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Social Media Links ──────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">
                    <i class="fas fa-share-alt me-2"></i>Social Media Links
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        These links power the social icons in the storefront header and footer.
                        Leave a field empty to hide that icon.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Facebook URL</label>
                            <input type="url" class="form-control" name="settings[social.facebook_url]"
                                   value="{{ $val('social.facebook_url') }}" placeholder="https://www.facebook.com/your-page">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Messenger URL</label>
                            <input type="url" class="form-control" name="settings[social.messenger_url]"
                                   value="{{ $val('social.messenger_url') }}" placeholder="https://m.me/your-page">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">WhatsApp URL</label>
                            <input type="url" class="form-control" name="settings[social.whatsapp_url]"
                                   value="{{ $val('social.whatsapp_url') }}" placeholder="https://wa.me/8801XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Instagram URL</label>
                            <input type="url" class="form-control" name="settings[social.instagram_url]"
                                   value="{{ $val('social.instagram_url') }}" placeholder="https://instagram.com/your-page">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">YouTube URL</label>
                            <input type="url" class="form-control" name="settings[social.youtube_url]"
                                   value="{{ $val('social.youtube_url') }}" placeholder="https://youtube.com/@your-channel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">TikTok URL</label>
                            <input type="url" class="form-control" name="settings[social.tiktok_url]"
                                   value="{{ $val('social.tiktok_url') }}" placeholder="https://tiktok.com/@your-handle">
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
