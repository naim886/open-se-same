@extends('backend.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Order #M-{{ $order->order_number }}</h4>
                <div>
                    <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-primary me-2">
                        <i class="ri-edit-line"></i> Edit Order
                    </a>
                    <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-primary me-2"
                       title="Download A4 invoice PDF">
                        <i class="fas fa-file-invoice me-1"></i> Invoice
                    </a>
                    <a href="{{ route('orders.label', $order->id) }}" class="btn btn-sm btn-outline-dark me-2"
                       title="Download 4x6in courier label PDF">
                        <i class="fas fa-tag me-1"></i> Label
                    </a>
                    @if(!$order->consignment_id)
                    <button type="button" class="btn btn-sm btn-warning me-2"
                        onclick="openCourierModal(
                            {{ $order->id }},
                            '{{ addslashes($order->order_number) }}',
                            '{{ addslashes($order->customer_name) }}',
                            '{{ addslashes($order->customer_phone) }}',
                            {{ (float)$order->total_amount }}
                        )">
                        <i class="fas fa-truck me-1"></i> Send to Courier
                    </button>
                    @else
                    <span class="btn btn-sm btn-outline-success me-2" style="cursor:default;" title="Consignment: {{ $order->consignment_id }}">
                        <i class="fas fa-check-circle me-1"></i> Courier Sent
                    </span>
                    @endif
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-arrow-left-line"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Order Info --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Order Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Order Number</small>
                            <strong>M-{{ $order->order_number }}</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Order Date</small>
                            <strong>{{ $order->order_date->format('M d, Y h:i A') }}</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-{{ $order->getStatusColor() }} font-size-13">{{ $order->getStatusText() }}</span>
                            @if((float)$order->total_amount > 0 && (float)$order->total_amount == (float)($order->paid_amount ?? 0))
                                <span class="badge bg-success ms-1"><i class="ri-checkbox-circle-line"></i> PAID</span>
                            @elseif((float)($order->paid_amount ?? 0) > 0)
                                <span class="badge bg-warning text-dark ms-1">Partial</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Delivery Location</small>
                            <strong>{{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Created By</small>
                            <strong>{{ $order->user->name ?? 'System' }}</strong>
                        </div>
                        @if($order->delivery_date)
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Expected Delivery</small>
                            <strong>{{ $order->delivery_date->format('M d, Y') }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Order Items</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($order->orderItems as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ optional($item->product)->primary_photo ? asset('storage/' . optional($item->product)->primary_photo) : asset('images/placeholder-50x50.png') }}"
                                                 style="width:40px;height:40px;object-fit:cover;border-radius:4px;" alt="">
                                            <div>
                                                <span>{{ optional($item->product)->name ?? ($item->product_name ?? 'Product unavailable') }}</span>
                                                @if($item->variation_label)
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        @foreach(explode(' / ', $item->variation_label) as $vPart)
                                                            <span class="badge rounded-pill" style="background:rgba(2,123,80,.20);color:#8FE6BD;border:1px solid rgba(2,123,80,.45);font-size:11px;font-weight:600;">{{ $vPart }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} tk</td>
                                    <td class="text-end">{{ number_format($item->subtotal, 2) }} tk</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No items</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="ri-sticky-note-line me-1"></i> Order Notes</h5>
                    <span class="badge bg-secondary">{{ $order->orderNotes->count() }}</span>
                </div>
                <div class="card-body">
                    @if($order->orderNotes->isEmpty())
                        <p class="text-muted mb-3">No notes yet.</p>
                    @else
                        <div class="notes-list mb-4" style="max-height:300px;overflow-y:auto;">
                            @foreach($order->orderNotes as $note)
                                <div class="d-flex gap-2 mb-3 pb-2 border-bottom">
                                    <div class="flex-shrink-0">
                                        <span class="avatar-title bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;font-size:13px;">
                                            {{ strtoupper(substr($note->user->name ?? 'S', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <strong class="text-sm">{{ $note->user->name ?? 'System' }}</strong>
                                            <small class="text-muted" title="{{ $note->created_at->format('M d, Y h:i A') }}">
                                                {{ $note->created_at->diffForHumans() }}
                                                <span class="ms-1 opacity-75">({{ $note->created_at->format('M d, h:i A') }})</span>
                                            </small>
                                        </div>
                                        <p class="mb-0 mt-1" style="white-space:pre-wrap;">{{ $note->note }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <form action="{{ route('orders.addNote', $order->id) }}" method="POST" class="d-flex gap-2 align-items-end">
                        @csrf
                        <div class="flex-grow-1">
                            <label class="form-label mb-1">Add New Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Write a note..." required></textarea>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-add-line"></i> Add Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Order Edit History --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-history-line me-1"></i> Order Edit History</h5></div>
                <div class="card-body">
                    @php $editHistories = $order->histories->whereIn('action', ['created', 'updated']); @endphp
                    @if($editHistories->isEmpty())
                        <p class="text-muted">No edit history available.</p>
                    @else
                        <div style="max-height:400px;overflow-y:auto;">
                            @foreach($editHistories as $history)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-{{ $history->action === 'created' ? 'success' : 'info' }}">{{ ucfirst($history->action) }}</span>
                                            <span class="text-muted ms-1 small">by {{ $history->user->name ?? 'System' }}</span>
                                        </div>
                                        <small class="text-muted">{{ $history->created_at->toDayDateTimeString() }}</small>
                                    </div>
                                    @if(!empty($history->changes) && $history->action === 'updated')
                                        <div class="mt-2">
                                            <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                                                <thead><tr><th>Field</th><th>From</th><th>To</th></tr></thead>
                                                <tbody>
                                                @foreach($history->changes as $field => $change)
                                                    <tr>
                                                        <td>{{ str_replace('_', ' ', ucfirst($field)) }}</td>
                                                        <td class="text-danger">{{ is_array($change['from'] ?? null) ? json_encode($change['from']) : ($change['from'] ?? '—') }}</td>
                                                        <td class="text-success">{{ is_array($change['to'] ?? null) ? json_encode($change['to']) : ($change['to'] ?? '—') }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status Change Log --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-swap-line me-1"></i> Status Change Log</h5></div>
                <div class="card-body">
                    @php
                        $statusChanges = $order->histories->filter(function($h) {
                            return $h->action === 'status_changed'
                                || ($h->action === 'updated' && !empty($h->changes['status']));
                        });
                    @endphp
                    @if($statusChanges->isEmpty())
                        <p class="text-muted">No status changes recorded.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($statusChanges as $sc)
                                @php
                                    $fromStatus = $sc->changes['status']['from'] ?? null;
                                    $toStatus   = $sc->changes['status']['to']   ?? null;
                                @endphp
                                <li class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom flex-wrap">
                                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$fromStatus] ?? 'secondary' }}">
                                        {{ \App\Models\Order::STATUS_LIST[$fromStatus] ?? 'Unknown' }}
                                    </span>
                                    <i class="ri-arrow-right-line"></i>
                                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$toStatus] ?? 'secondary' }}">
                                        {{ \App\Models\Order::STATUS_LIST[$toStatus] ?? 'Unknown' }}
                                    </span>
                                    <small class="text-muted ms-auto">{{ $sc->user->name ?? 'System' }} &bull; {{ $sc->created_at->toDayDateTimeString() }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">

            {{-- Customer Info --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Customer Information</h5></div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Name</small>
                        <p class="mb-0 fw-bold">{{ $order->customer_name }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Phone</small>
                        <p class="mb-0">{{ $order->customer_phone }}</p>
                    </div>
                    @if($order->customer_email)
                    <div class="mb-2">
                        <small class="text-muted">Email</small>
                        <p class="mb-0">{{ $order->customer_email }}</p>
                    </div>
                    @endif
                    <div class="mb-2">
                        <small class="text-muted">Address</small>
                        <p class="mb-0">{{ $order->customer_address }}</p>
                    </div>
                    @if($order->source)
                    <div class="mb-0">
                        <small class="text-muted">Source</small>
                        <p class="mb-0">
                            @php
                                $sourceLabels = [
                                    'website'    => ['label'=>'Website',    'color'=>'#027B50'],
                                    'facebook'   => ['label'=>'Facebook',   'color'=>'#1877f2'],
                                    'instagram'  => ['label'=>'Instagram',  'color'=>'#e1306c'],
                                    'tiktok'     => ['label'=>'TikTok',     'color'=>'#010101'],
                                    'whatsapp'   => ['label'=>'WhatsApp',   'color'=>'#25d366'],
                                    'referral'   => ['label'=>'Referral',   'color'=>'#6f42c1'],
                                    'phone_call' => ['label'=>'Phone Call', 'color'=>'#fd7e14'],
                                    'walk_in'    => ['label'=>'Walk In',    'color'=>'#20c997'],
                                    'other'      => ['label'=>'Other',      'color'=>'#6c757d'],
                                ];
                                $src = $sourceLabels[$order->source] ?? ['label'=>ucfirst($order->source),'color'=>'#6c757d'];
                            @endphp
                            <span style="display:inline-block; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; color:#fff; background:{{ $src['color'] }};">
                                {{ $src['label'] }}
                            </span>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Order Summary</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td>Products Total</td>
                                <td class="text-end">{{ number_format($order->product_price ?? 0, 2) }} tk</td>
                            </tr>
                            <tr>
                                <td>Delivery ({{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }})</td>
                                <td class="text-end">{{ number_format($order->delivery_charge ?? 0, 2) }} tk</td>
                            </tr>
                            @if((float) $order->discount_amount > 0)
                            <tr class="text-success">
                                <td>
                                    Discount
                                    @if($order->coupon_code)
                                        <span class="badge bg-secondary" style="font-size:10px;">{{ $order->coupon_code }}</span>
                                    @endif
                                </td>
                                <td class="text-end">- {{ number_format($order->discount_amount, 2) }} tk</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Order Total</th>
                                <th class="text-end">{{ number_format($order->total_amount, 2) }} tk</th>
                            </tr>
                            <tr class="text-success">
                                <td>Paid</td>
                                <td class="text-end">{{ number_format($order->paid_amount ?? 0, 2) }} tk</td>
                            </tr>
                            @php $cod = (float)$order->total_amount - (float)($order->paid_amount ?? 0); @endphp
                            <tr class="{{ $cod > 0 ? 'text-danger' : 'text-success' }}">
                                <td>{{ $cod > 0 ? 'COD Due' : 'Fully Paid ✓' }}</td>
                                <td class="text-end">{{ number_format(abs($cod), 2) }} tk</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Courier Information --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-truck me-1"></i> Steadfast Courier</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Copy Tracking URL (always shown) --}}
                        <button type="button" class="btn btn-sm btn-outline-primary btn-copy-tracking-show"
                            title="Copy customer tracking link"
                            data-tracking-url="{{ url('track-order') }}?q=M-{{ $order->order_number }}">
                            <i class="fas fa-copy me-1"></i> Copy Tracking
                        </button>
                        {{-- Show in Courier: once at top of card --}}
                        @if($order->tracking_code)
                        <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}" target="_blank"
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-external-link-alt me-1"></i> Show in Courier
                        </a>
                        @endif
                        @if($order->consignment_id)
                            <span class="badge bg-success">Sent</span>
                        @else
                            <span class="badge bg-secondary">Not Sent</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($order->consignment_id)
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Consignment ID</small>
                                <strong>{{ $order->consignment_id }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Tracking Code</small>
                                <strong>{{ $order->tracking_code ?? '—' }}</strong>
                            </div>
                        </div>
                    @endif

                    @if($order->courierLogs && $order->courierLogs->count() > 0)
                        @php
                            $apiLogs     = $order->courierLogs->where('source', '!=', 'webhook')->values();
                            $webhookLogs = $order->courierLogs->where('source', 'webhook')->values();
                        @endphp

                        {{-- API submission logs (newest first) --}}
                        @if($apiLogs->count() > 0)
                        <hr style="border-color:rgba(255,255,255,0.1);">
                        <small class="text-muted d-block mb-2">Submission History ({{ $apiLogs->count() }})</small>
                        @foreach($apiLogs as $log)
                            <div class="mb-2 p-2" style="background:rgba(255,255,255,0.04);border-radius:5px;font-size:12px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $log->consignment_id ? 'success' : 'danger' }}">
                                        {{ $log->consignment_id ? 'Success' : 'Failed' }}
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="opacity:.6;">{{ $log->created_at->format('M d, h:i A') }}</span>
                                        <button class="btn btn-outline-secondary" style="font-size:10px;padding:1px 7px;" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#raw-log-{{ $log->id }}">
                                            Raw
                                        </button>
                                    </div>
                                </div>
                                @if($log->consignment_id)
                                    <div class="mt-1">ID: <strong>{{ $log->consignment_id }}</strong></div>
                                    <div>Tracking: <strong>{{ $log->tracking_code }}</strong></div>
                                @endif
                                @if($log->sent_phone)
                                    <div style="opacity:.7;">Phone sent: {{ $log->sent_phone }}</div>
                                @endif
                                <div class="collapse mt-2" id="raw-log-{{ $log->id }}">
                                    <pre style="background:rgba(0,0,0,0.35);color:#a8ff78;border-radius:4px;padding:8px;font-size:11px;max-height:200px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;">{{ json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endforeach
                        @endif

                        {{-- Webhook tracking updates (newest first) --}}
                        @if($webhookLogs->count() > 0)
                        <hr style="border-color:rgba(255,255,255,0.1);">
                        <small class="text-muted d-block mb-2"><i class="fas fa-satellite-dish me-1"></i>Webhook Updates ({{ $webhookLogs->count() }})</small>
                        @foreach($webhookLogs as $log)
                            <div class="mb-2 p-2" style="background:rgba(100,200,255,0.06);border:1px solid rgba(100,200,255,0.15);border-radius:5px;font-size:12px;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    @if($log->status)
                                        <span class="badge bg-{{ in_array($log->status, ['delivered']) ? 'success' : (in_array($log->status, ['cancelled']) ? 'danger' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Tracking Update</span>
                                    @endif
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="opacity:.6;">{{ $log->created_at->format('M d, h:i A') }}</span>
                                        <button class="btn btn-outline-secondary" style="font-size:10px;padding:1px 7px;" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#raw-wh-{{ $log->id }}">
                                            Raw
                                        </button>
                                    </div>
                                </div>
                                @if($log->tracking_message)
                                    <div style="color:rgba(255,255,255,0.85);">{{ $log->tracking_message }}</div>
                                @endif
                                <div class="collapse mt-2" id="raw-wh-{{ $log->id }}">
                                    <pre style="background:rgba(0,0,0,0.35);color:#a8ff78;border-radius:4px;padding:8px;font-size:11px;max-height:200px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;">{{ json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endforeach
                        @endif

                    @else
                        <p class="text-muted mb-0"><small>No courier submissions yet.</small></p>
                    @endif
                </div>
            </div>

            {{-- Quick Status Update --}}
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Update Status</h5></div>
                <div class="card-body">
                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                @foreach(\App\Models\Order::STATUS_LIST as $key => $status)
                                    <option value="{{ $key }}" {{ $order->status == $key ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="ri-refresh-line align-bottom me-1"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ===== Steadfast Courier Modal ===== --}}
<div class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);color:var(--theme-text-color);border:1px solid var(--border-strong);">
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
                    <div id="cm-bengali-warning" class="alert alert-warning d-none" style="background:rgba(255,207,47,.15);border-color:var(--yellow);color:var(--yellow);">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Bengali digits detected in phone number!</strong><br>
                        They will be auto-converted to English before submission.
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="cm-convert-check" value="1" checked>
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
const bengaliChars = '০১২৩৪৫৬৭৮৯';
const englishChars = '0123456789';
function hasBengali(str) { return /[০-৯]/.test(str); }
function toBengaliEnglish(str) {
    return str.split('').map(c => { const i = bengaliChars.indexOf(c); return i >= 0 ? englishChars[i] : c; }).join('');
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
        converted.textContent  = '→ ' + toBengaliEnglish(phone);
        convertCheck.checked   = true;
        convertHidden.value    = '1';
        convertCheck.onchange = () => { convertHidden.value = convertCheck.checked ? '1' : '0'; };
    } else {
        warning.classList.add('d-none');
        convertHidden.value = '0';
    }
    document.getElementById('courierForm').action = '{{ url("admin/orders") }}/' + orderId + '/send-to-courier';
    new bootstrap.Modal(document.getElementById('courierModal')).show();
}

// Copy Tracking URL button (show page)
document.addEventListener('DOMContentLoaded', function () {
    const copyBtn = document.querySelector('.btn-copy-tracking-show');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const url = this.getAttribute('data-tracking-url');
            navigator.clipboard.writeText(url).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                this.classList.add('btn-success'); this.classList.remove('btn-outline-primary');
                setTimeout(() => { this.innerHTML = orig; this.classList.remove('btn-success'); this.classList.add('btn-outline-primary'); }, 2000);
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.focus(); ta.select();
                document.execCommand('copy'); document.body.removeChild(ta);
                const orig = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                this.classList.add('btn-success'); this.classList.remove('btn-outline-primary');
                setTimeout(() => { this.innerHTML = orig; this.classList.remove('btn-success'); this.classList.add('btn-outline-primary'); }, 2000);
            });
        });
    }
});
</script>

@endsection

@push('styles')
<style>
    .timeline .border-bottom { border-color: rgba(255,255,255,0.1) !important; }
    .notes-list .border-bottom { border-color: rgba(255,255,255,0.1) !important; }
    /* Dark theme fix for cards in show page */
    .card, .card-header, .card-body {
        background: transparent !important;
        color: inherit !important;
    }
    .card {
        border: 1px solid rgba(255,255,255,0.15) !important;
    }
    .card-header {
        border-bottom: 1px solid rgba(255,255,255,0.15) !important;
    }
    /* Fix Bootstrap table cells overriding dark theme */
    .table td, .table th, .table thead th, .table tbody td {
        background-color: transparent !important;
        color: inherit !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    .table {
        color: inherit;
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(255,255,255,0.03);
        --bs-table-hover-bg: rgba(255,255,255,0.05);
        --bs-table-border-color: rgba(255,255,255,0.1);
        --bs-table-color: inherit;
    }
    /* Fix muted text — Bootstrap default is near-black, invisible on dark bg */
    .text-muted, small.text-muted, .text-muted small {
        color: rgba(255,255,255,0.5) !important;
    }
    textarea.form-control {
        background: rgba(255,255,255,0.07) !important;
        color: inherit !important;
        border-color: rgba(255,255,255,0.2) !important;
    }
    textarea.form-control::placeholder { color: rgba(255,255,255,0.35) !important; }
    .form-select {
        background-color: rgba(255,255,255,0.07) !important;
        color: inherit !important;
        border-color: rgba(255,255,255,0.2) !important;
    }
</style>
@endpush
