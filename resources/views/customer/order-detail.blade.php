@extends('customer.layouts.panel')

@section('title', 'Order M-{{ $order->order_number }} — Open Se Same')

@section('panel_content')

@php
    $locLabels = [1 => 'Inside Dhaka', 2 => 'Outside Dhaka', 3 => 'Express', 4 => 'Super Express', 5 => 'Free'];
@endphp

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-1">
    <div class="panel-section-title mb-0">
        <i class="fas fa-map-marker-alt"></i> Order Tracking
    </div>
    <a href="{{ route('customer.orders') }}" class="btn-outline-custom" style="font-size:12px;padding:6px 14px;">
        <i class="fas fa-arrow-left me-1"></i> Back to Orders
    </a>
</div>
<hr style="border-color:var(--border);margin-bottom:20px;">

<!-- Order Summary Card -->
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div style="background:var(--bg-light);border:1px solid var(--border);border-radius:var(--radius);padding:18px;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold" style="font-size:15px;">Order <span style="color:var(--primary);">M-{{ $order->order_number }}</span></h6>
                <span class="status-badge status-{{ $order->status }}">
                    {{ \App\Models\Order::STATUS_LIST[$order->status] ?? 'Unknown' }}
                </span>
            </div>
            <div style="font-size:13px;color:var(--text-muted);">
                <i class="fas fa-calendar me-1"></i>
                Placed on {{ $order->order_date?->format('d M Y, h:i A') }}
            </div>
            @if($order->delivery_date)
            <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">
                <i class="fas fa-truck me-1"></i>
                Delivered on {{ $order->delivery_date?->format('d M Y') }}
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-5">
        <div style="background:var(--bg-light);border:1px solid var(--border);border-radius:var(--radius);padding:18px;">
            <div style="font-size:13px;margin-bottom:6px;">
                <span style="color:var(--text-muted);">Delivery Type:</span>
                <strong class="ms-1">{{ $locLabels[$order->delivery_location] ?? '—' }}</strong>
            </div>
            <div style="font-size:13px;margin-bottom:6px;">
                <span style="color:var(--text-muted);">Products:</span>
                <strong class="ms-1">৳{{ number_format($order->product_price, 0) }}</strong>
            </div>
            <div style="font-size:13px;margin-bottom:6px;">
                <span style="color:var(--text-muted);">Delivery Charge:</span>
                <strong class="ms-1">৳{{ number_format($order->delivery_charge, 0) }}</strong>
            </div>
            <div style="font-size:14px;border-top:1px solid var(--border);padding-top:6px;margin-top:6px;">
                <span style="color:var(--text-muted);">Total:</span>
                <strong class="ms-1" style="color:var(--primary);font-size:16px;">৳{{ number_format($order->total_amount, 0) }}</strong>
            </div>
            @if($order->paid_amount > 0)
            <div style="font-size:13px;margin-top:4px;">
                <span style="color:var(--text-muted);">Paid:</span>
                <strong class="ms-1" style="color:#28a745;">৳{{ number_format($order->paid_amount, 0) }}</strong>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Courier Info (if available) -->
@if($order->tracking_code || $order->consignment_id)
<div class="mb-4 p-3" style="background:#f0fff4;border:1px solid #b7e8c8;border-radius:var(--radius);">
    <h6 class="fw-bold mb-2" style="font-size:13px;color:#155724;">
        <i class="fas fa-shipping-fast me-2"></i>Courier Tracking
    </h6>
    <div style="font-size:13px;">
        @if($order->tracking_code)
        <div><span style="color:var(--text-muted);">Tracking Code:</span> <strong>{{ $order->tracking_code }}</strong></div>
        @endif
        @if($order->consignment_id)
        <div><span style="color:var(--text-muted);">Consignment ID:</span> <strong>{{ $order->consignment_id }}</strong></div>
        @endif
    </div>
</div>
@endif

<div class="row g-4">
    <!-- Status Timeline -->
    <div class="col-md-5">
        <h6 class="fw-bold mb-3" style="font-size:14px;color:var(--text-dark);">
            <i class="fas fa-history me-2" style="color:var(--primary);"></i>Order Status History
        </h6>

        @if($order->status_timeline && $order->status_timeline->count() > 0)
            <div class="tracking-timeline">
                @foreach($order->status_timeline->sortBy('time') as $step)
                <div class="timeline-step">
                    <div class="step-label">{{ $step['to'] }}</div>
                    <div class="step-time">
                        <i class="fas fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($step['time'])->format('d M Y, h:i A') }}
                    </div>
                </div>
                @endforeach

                <!-- Current status as last step if not already in timeline -->
                @php
                    $lastTimelineStatus = $order->status_timeline->sortBy('time')->last()['to'] ?? null;
                    $currentStatusLabel = \App\Models\Order::STATUS_LIST[$order->status] ?? null;
                @endphp
                @if($lastTimelineStatus !== $currentStatusLabel && $currentStatusLabel)
                <div class="timeline-step">
                    <div class="step-label" style="color:var(--primary);">{{ $currentStatusLabel }} (Current)</div>
                </div>
                @endif
            </div>
        @else
            <div class="tracking-timeline">
                <div class="timeline-step">
                    <div class="step-label">
                        {{ \App\Models\Order::STATUS_LIST[$order->status] ?? 'Pending' }}
                    </div>
                    <div class="step-time">
                        <i class="fas fa-clock me-1"></i>
                        {{ $order->order_date?->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Order Items -->
    <div class="col-md-7">
        <h6 class="fw-bold mb-3" style="font-size:14px;color:var(--text-dark);">
            <i class="fas fa-shopping-bag me-2" style="color:var(--primary);"></i>Order Items
        </h6>

        @foreach($order->orderItems as $item)
        <div class="d-flex align-items-center gap-3 mb-3 pb-3"
             style="border-bottom:1px solid var(--border);">
            @if($item->product && $item->product->primary_photo)
                <img src="{{ asset('storage/' . $item->product->primary_photo) }}"
                     alt="{{ $item->product_name ?? $item->product->name }}"
                     style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
                     onerror="this.style.display='none'">
            @else
                <div style="width:56px;height:56px;border-radius:8px;background:var(--bg-light);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-image" style="color:var(--text-muted);"></i>
                </div>
            @endif
            <div class="flex-grow-1">
                <div style="font-size:14px;font-weight:600;color:var(--text-dark);">
                    {{ $item->product_name ?? $item->product?->name ?? 'Product' }}
                </div>
                <div style="font-size:12px;color:var(--text-muted);">
                    Qty: {{ $item->quantity }} &nbsp;·&nbsp; ৳{{ number_format($item->unit_price, 0) }} each
                </div>
            </div>
            <div style="font-size:14px;font-weight:700;color:var(--text-dark);white-space:nowrap;">
                ৳{{ number_format($item->subtotal, 0) }}
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
