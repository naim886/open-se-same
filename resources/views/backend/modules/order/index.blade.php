@extends('backend.layouts.app')

@section('title', 'Order Management')

@section('content')

    <div class="container-fluid  my-5 orders-page">
        <!-- Order Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6" onclick="filterOrders(0)" style="cursor:pointer;" aria-label="Filter All Orders" >
                <div class="card {{ !request('status') ? 'active-status-card' : '' }}">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Total Orders</p>
                                <h4 class="mb-2">{{ $stats['total_orders'] }}</h4>
                            </div>
                            <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-shopping-cart-line font-size-24"></i>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Render all statuses dynamically (fallback to old manual ones if not provided) --}}
            @if(!empty($stats['statuses']) && is_array($stats['statuses']))
                @foreach($stats['statuses'] as $status)
                    <div class="col-xl-2 col-md-4 col-6" role="button" onclick="filterOrders('{{ $status['id'] }}')" style="cursor:pointer;" aria-label="Filter {{ $status['label'] }}">
                        <div class="card {{ request('status') == $status['id'] ? 'active-status-card' : '' }}">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-truncate font-size-14 mb-2">{{ $status['label'] }}</p>
                                        <h4 class="mb-2">{{ $status['count'] }}</h4>
                                    </div>
                                    <div class="avatar-sm">
                                        <span class="avatar-title bg-light text-{{ $status['color'] }} rounded-3">
                                            <i class="ri-stack-line font-size-24"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-truncate font-size-14 mb-2">Pending</p>
                                    <h4 class="mb-2">{{ $stats['pending_orders'] }}</h4>
                                </div>
                                <div class="avatar-sm">
                                <span class="avatar-title bg-light text-warning rounded-3">
                                    <i class="ri-time-line font-size-24"></i>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-truncate font-size-14 mb-2">Confirmed</p>
                                    <h4 class="mb-2">{{ $stats['confirmed_orders'] }}</h4>
                                </div>
                                <div class="avatar-sm">
                                <span class="avatar-title bg-light text-info rounded-3">
                                    <i class="ri-check-line font-size-24"></i>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-truncate font-size-14 mb-2">Delivered</p>
                                    <h4 class="mb-2">{{ $stats['delivered_orders'] }}</h4>
                                </div>
                                <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="ri-truck-line font-size-24"></i>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-truncate font-size-14 mb-2">Cancelled</p>
                                    <h4 class="mb-2">{{ $stats['cancelled_orders'] }}</h4>
                                </div>
                                <div class="avatar-sm">
                                <span class="avatar-title bg-light text-danger rounded-3">
                                    <i class="ri-close-line font-size-24"></i>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-xl-2 col-md-4 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Total Revenue</p>
                                <h4 class="mb-2">৳{{ number_format($stats['total_revenue'], 0) }}</h4>
                            </div>
                            <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-money-dollar-circle-line font-size-24"></i>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">

                {{-- Advanced Filter Panel --}}
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="$(this).find('.adv-filter-arrow').toggleClass('ri-arrow-up-s-line ri-arrow-down-s-line'); $('#adv-filter-body').toggleClass('d-none');">
                        <span><i class="ri-menu-line me-2"></i> Advanced Filter</span>
                        <i class="adv-filter-arrow {{ request()->anyFilled(['customer_name','customer_phone','order_number','date_from','date_to','delivery_location','paid_status','status','created_by','source','product_id']) ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line' }}"></i>
                    </div>
                    <div class="card-body {{ request()->anyFilled(['customer_name','customer_phone','order_number','date_from','date_to','delivery_location','paid_status','status','created_by','source','product_id']) ? '' : 'd-none' }}" id="adv-filter-body">
                        <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label mb-1">Order No</label>
                                <input type="text" name="order_number" class="form-control form-control-sm" placeholder="e.g. 1001" value="{{ request('order_number') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Customer Name</label>
                                <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Name..." value="{{ request('customer_name') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Phone</label>
                                <input type="text" name="customer_phone" class="form-control form-control-sm" placeholder="Phone..." value="{{ request('customer_phone') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Models\Order::STATUS_LIST as $k => $v)
                                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Delivery</label>
                                <select name="delivery_location" class="form-select form-select-sm">
                                    <option value="">All Locations</option>
                                    @foreach(\App\Models\Order::DELIVERY_LIST as $k => $v)
                                        <option value="{{ $k }}" {{ request('delivery_location') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Payment</label>
                                <select name="paid_status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="paid" {{ request('paid_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="unpaid" {{ request('paid_status') == 'unpaid' ? 'selected' : '' }}>Unpaid / Partial</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Date From</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Date To</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Created By</label>
                                <select name="created_by" class="form-select form-select-sm">
                                    <option value="">All Users</option>
                                    @foreach($users ?? [] as $u)
                                        <option value="{{ $u->id }}" {{ request('created_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Source</label>
                                <select name="source" class="form-select form-select-sm">
                                    <option value="">All Sources</option>
                                    @foreach(\App\Http\Controllers\Backend\CustomerController::SOURCE_LIST as $k => $v)
                                        <option value="{{ $k }}" {{ request('source') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Product</label>
                                <select name="product_id" class="form-select form-select-sm" id="filter-product-select">
                                    <option value="">All Products</option>
                                    @foreach($allProducts ?? [] as $p)
                                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="ri-search-line"></i> Filter</button>
                                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ri-refresh-line"></i> Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table theme-table">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            @php
                                $firstItem = $order->orderItems->first();
                                $totalQty = $order->orderItems->sum('quantity');
                                $cod = (float)$order->total_amount - (float)($order->paid_amount ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}">
                                        M-{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div style="max-width: 200px;">
                                        <p>Name: <strong>{{ $order->customer_name }}</strong></p>
                                        <p class="text-success">Phone: {{ $order->customer_phone }}</p>
                                        <p> Address: <em>{{ $order->customer_address }}</em></p>
                                        <p class="text-success"><small>Note: {{ $order->notes }}</small></p>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        @forelse($order->orderItems as $items)
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img class="img-thumbnail" style="max-width: 50px"
                                                         src="{{ optional($items->product)->primary_photo ? asset('storage/' . optional($items->product)->primary_photo) : asset('images/placeholder-50x50.png') }}"
                                                         alt="{{ optional($items->product)->name ?? 'Product' }}">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p><small>{{ optional($items->product)->name ?? ($items->product_name ?? 'Product unavailable') }}</small></p>
                                                    @if($items->variation_label)
                                                        <p><small class="text-info">{{ $items->variation_label }}</small></p>
                                                    @endif
                                                    <p class="text-success"><small>Qty: <strong>{{ $items->quantity }}</strong> pcs</small></p>
                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                                <hr class="my-1"/>
                                            @endif
                                        @empty
                                            <p>No items found</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 200px;" class="amounts">
                                        <p>
                                            Product:
                                            <span>{{ number_format($order->product_price ?? 0, 2) }} tk</span>
                                        </p>
                                        <p>
                                            Delivery:
                                            <span>{{ number_format($order->delivery_charge ?? 0, 2) }} tk</span>
                                        </p>
                                        <p class="text-success">
                                            Paid:
                                            <span>{{ number_format($order->paid_amount ?? 0, 2) }} tk</span>
                                        </p>
                                        <p class="text-danger">
                                            COD:
                                            <span>{{ number_format($cod, 2) }} tk</span>
                                        </p>
                                        <p class="text-primary">
                                            Total:
                                            <strong>{{ number_format($order->total_amount, 2) }} tk</strong>
                                        </p>
                                    </div>
                                </td>

                                <td>
                                        <span class="badge bg-{{ $order->getStatusColor() }}">
                                            {{ $order->getStatusText() }}
                                        </span>
                                        @if((float)$order->total_amount > 0 && (float)$order->total_amount == (float)($order->paid_amount ?? 0))
                                            <br><span class="badge bg-success mt-1"><i class="ri-checkbox-circle-line"></i> PAID</span>
                                        @elseif((float)($order->paid_amount ?? 0) > 0)
                                            <br><span class="badge bg-warning text-dark mt-1">Partial</span>
                                        @endif
                                        @php
                                            $dloc = $order->delivery_location;
                                            $deliveryBadgeMap = [
                                                1 => ['Inside Dhaka',  'secondary', ''],
                                                2 => ['Outside Dhaka', 'dark',      ''],
                                                3 => ['Express',       'danger',    'fa-bolt'],
                                                4 => ['Super Express', 'danger',    'fa-rocket'],
                                                5 => ['Free Delivery', 'success',   'fa-gift'],
                                            ];
                                        @endphp
                                        @if($dloc && isset($deliveryBadgeMap[$dloc]))
                                            @php [$dlabel, $dcolor, $dicon] = $deliveryBadgeMap[$dloc]; @endphp
                                            <br><span class="badge bg-{{ $dcolor }} mt-1" style="font-size:10px;">
                                                @if($dicon)<i class="fas {{ $dicon }} me-1"></i>@endif{{ $dlabel }}
                                            </span>
                                        @endif
                                        @php
                                            $srcKey   = $order->source ?? '';
                                            $srcLabel = \App\Http\Controllers\Backend\CustomerController::SOURCE_LIST[$srcKey] ?? ucfirst($srcKey);
                                            $srcColor = \App\Http\Controllers\Backend\CustomerController::SOURCE_COLORS[$srcKey] ?? '#6c757d';
                                        @endphp
                                        @if($srcLabel)
                                            <br><span class="badge mt-1" style="background-color:{{ $srcColor }};font-size:10px;">{{ $srcLabel }}</span>
                                        @endif
                                </td>
                                <td>
                                    <p><small>Placed on: {{$order->order_date->toDayDateTimeString()}}</small></p>
                                    <p><small>Last
                                            Updated: {{ $order->created_at != $order->updated_at ? $order->updated_at->toDayDateTimeString() : 'Not updated' }}</small>
                                    </p>
                                    <p><small>Placed By: {{ $order->user->name ?? 'System'}}</small></p>
                                </td>
                                <td>
                                    {{-- Row 1: View + Edit + Courier Track --}}
                                    <div class="d-flex gap-1 mb-1 flex-wrap">
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary" style="font-size:13px;padding:5px 12px;">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-secondary" style="font-size:13px;padding:5px 12px;">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        @if($order->tracking_code)
                                        <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}" target="_blank"
                                           class="btn btn-outline-info" style="font-size:13px;padding:5px 12px;" title="Track in Steadfast">
                                            <i class="fas fa-external-link-alt me-1"></i> Courier Track
                                        </a>
                                        @endif
                                    </div>

                                    {{-- Row 2: Copy Tracking + Courier action --}}
                                    <div class="d-flex gap-1 mb-1 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary btn-copy-tracking"
                                            style="font-size:13px;padding:5px 12px;"
                                            title="Copy customer tracking link"
                                            data-tracking-url="{{ url('track-order') }}?q=M-{{ $order->order_number }}">
                                            <i class="fas fa-copy me-1"></i> Copy Tracking
                                        </button>
                                        @if(!$order->consignment_id)
                                        <button type="button" class="btn btn-warning" style="font-size:13px;padding:5px 12px;"
                                            title="Send to Steadfast Courier"
                                            onclick="openCourierModal(
                                                {{ $order->id }},
                                                '{{ addslashes($order->order_number) }}',
                                                '{{ addslashes($order->customer_name) }}',
                                                '{{ addslashes($order->customer_phone) }}',
                                                {{ max(0, (float)$order->total_amount - (float)($order->paid_amount ?? 0)) }}
                                            )">
                                            <i class="fas fa-truck me-1"></i> Courier
                                        </button>
                                        @else
                                        <span class="btn btn-outline-success" style="font-size:13px;padding:5px 12px;cursor:default;"
                                              title="Consignment: {{ $order->consignment_id }} | Tracking: {{ $order->tracking_code }}">
                                            <i class="fas fa-check-circle me-1"></i> Sent
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Row 3: Inline status update --}}
                                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="d-flex" style="gap:6px;">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="width:120px;">
                                            @foreach(\App\Models\Order::STATUS_LIST as $key => $label)
                                                <option value="{{ $key }}" {{ $order->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" aria-label="Update order status">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No orders found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>


    {{-- ===== Steadfast Courier Modal ===== --}}
    <div class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:#1e2130;color:#fff;border:1px solid rgba(255,255,255,0.2);">
                <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.15);">
                    <h5 class="modal-title" id="courierModalLabel"><i class="fas fa-truck me-2"></i>Send to Steadfast Courier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="courierForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <table class="table table-sm mb-3" style="color:inherit;--bs-table-bg:transparent;--bs-table-color:inherit;">
                            <tbody>
                                <tr><td style="opacity:.6;width:40%">Order #</td><td><strong id="cm-order-num"></strong></td></tr>
                                <tr><td style="opacity:.6;">Customer</td><td id="cm-name"></td></tr>
                                <tr><td style="opacity:.6;">Phone</td><td id="cm-phone"></td></tr>
                                <tr><td style="opacity:.6;">COD Amount</td><td id="cm-amount"></td></tr>
                            </tbody>
                        </table>

                        <div id="cm-bengali-warning" class="alert alert-warning d-none" style="background:rgba(255,193,7,.15);border-color:#ffc107;color:#ffc107;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Bengali digits detected in phone number!</strong><br>
                            The phone number contains Bengali digits. They will be auto-converted to English before submission.
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="cm-convert-check" name="convert_phone" value="1" checked>
                                <label class="form-check-label" for="cm-convert-check">
                                    Convert Bengali digits to English <span id="cm-converted" class="badge bg-success ms-1"></span>
                                </label>
                            </div>
                        </div>
                        <input type="hidden" id="cm-convert-hidden" name="convert_phone" value="0">
                    </div>
                    <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.15);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane me-1"></i>Send to Courier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function filterOrders(status) {
            const url = new URL(window.location.href);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.location.href = url.toString();
        }

        // ---------- Steadfast courier modal ----------
        const bengaliChars = '০১২৩৪৫৬৭৮৯';
        const englishChars = '0123456789';

        function hasBengali(str) { return /[০-৯]/.test(str); }

        function toBengaliEnglish(str) {
            return str.split('').map(c => {
                const i = bengaliChars.indexOf(c);
                return i >= 0 ? englishChars[i] : c;
            }).join('');
        }

        function openCourierModal(orderId, orderNum, name, phone, total) {
            document.getElementById('cm-order-num').textContent = 'M-' + orderNum;
            document.getElementById('cm-name').textContent      = name;
            document.getElementById('cm-phone').textContent     = phone;
            document.getElementById('cm-amount').textContent    = parseFloat(total).toFixed(2) + ' tk';

            const warning       = document.getElementById('cm-bengali-warning');
            const convertCheck  = document.getElementById('cm-convert-check');
            const convertHidden = document.getElementById('cm-convert-hidden');
            const converted     = document.getElementById('cm-converted');

            if (hasBengali(phone)) {
                warning.classList.remove('d-none');
                const eng = toBengaliEnglish(phone);
                converted.textContent = '→ ' + eng;
                convertCheck.checked  = true;
                convertHidden.value   = '1';
                convertCheck.onchange = () => {
                    convertHidden.value = convertCheck.checked ? '1' : '0';
                };
            } else {
                warning.classList.add('d-none');
                convertHidden.value = '0';
            }

            const baseUrl = '{{ url("admin/orders") }}';
            document.getElementById('courierForm').action = baseUrl + '/' + orderId + '/send-to-courier';

            new bootstrap.Modal(document.getElementById('courierModal')).show();
        }

        // ---------- Copy Tracking URL ----------
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-copy-tracking');
            if (!btn) return;
            const url = btn.getAttribute('data-tracking-url');
            if (!url) return;
            navigator.clipboard.writeText(url).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-primary');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            }).catch(() => {
                // Fallback for older browsers
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus(); ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-primary');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            });
        });
    </script>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 colour theming comes from backend/css/styles.css;
           this only matches the compact filter-bar sizing. */
        #filter-product-select + .select2-container .select2-selection--single {
            border-radius: 4px;
            height: 31px;
        }
        #filter-product-select + .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 29px;
            font-size: .875rem;
            padding-left: 8px;
        }
        #filter-product-select + .select2-container .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        .select2-results__option {
            font-size: .875rem;
        }
    </style>
    <style>
        .card {
            margin-bottom: 24px;
        }

        .active-status-card {
            border-color: var(--brand-light) !important;
            box-shadow: 0 0 0 2px rgba(2,123,80,.45), 0 4px 16px rgba(2,123,80,.2) !important;
            background: rgba(2,123,80,.12) !important;
        }
        .active-status-card p,
        .active-status-card h4 {
            color: var(--yellow) !important;
        }

        small {
            line-height: .8;
        }

        .amounts p {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            border-bottom: 1px solid var(--border-subtle);
            padding: 2px 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            $('#filter-product-select').select2({
                placeholder: 'All Products',
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
            });
        });
    </script>
@endpush
