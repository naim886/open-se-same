@extends('customer.layouts.panel')

@section('title', 'My Orders — Open Se Same')

@section('panel_content')

<div class="panel-section-title">
    <i class="fas fa-box"></i> My Orders
</div>

@if($orders->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-shopping-bag" style="font-size:52px;color:var(--border);"></i>
        <p class="mt-3 text-muted fs-6">You haven't placed any orders yet.</p>
        <a href="{{ route('shop.products') }}" class="btn-primary-custom mt-2 d-inline-block">
            <i class="fas fa-shopping-cart me-1"></i> Start Shopping
        </a>
    </div>
@else
    <!-- Mobile cards -->
    <div class="d-md-none">
        @foreach($orders as $order)
        <div class="border rounded p-3 mb-3" style="border-color:var(--border)!important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <strong style="font-size:14px;">M-{{ $order->order_number }}</strong>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $order->order_date?->format('d M Y') }}</div>
                </div>
                <span class="status-badge status-{{ $order->status }}">
                    {{ \App\Models\Order::STATUS_LIST[$order->status] ?? 'Unknown' }}
                </span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div style="font-size:13px;">
                    <i class="fas fa-box-open me-1" style="color:var(--primary);"></i>
                    {{ $order->orderItems->count() }} item{{ $order->orderItems->count() != 1 ? 's' : '' }}
                    &nbsp;·&nbsp;
                    <strong>৳{{ number_format($order->total_amount, 0) }}</strong>
                </div>
                <a href="{{ route('customer.order.detail', $order->id) }}"
                   class="btn-outline-custom" style="font-size:12px;padding:6px 12px;">
                    <i class="fas fa-map-marker-alt me-1"></i> Track
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Desktop table -->
    <div class="d-none d-md-block table-responsive">
        <table class="table order-table table-hover mb-3">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Delivery</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td><strong>M-{{ $order->order_number }}</strong></td>
                    <td>{{ $order->order_date?->format('d M Y') }}</td>
                    <td>
                        @foreach($order->orderItems->take(2) as $item)
                            <div style="font-size:12px;">{{ $item->product_name ?? $item->product?->name ?? 'Product' }}</div>
                        @endforeach
                        @if($order->orderItems->count() > 2)
                            <div style="font-size:11px;color:var(--text-muted);">+{{ $order->orderItems->count() - 2 }} more</div>
                        @endif
                    </td>
                    <td style="font-size:12px;">
                        @php
                            $locLabels = [1 => 'Inside Dhaka', 2 => 'Outside Dhaka', 3 => 'Express', 4 => 'Super Express', 5 => 'Free'];
                        @endphp
                        {{ $locLabels[$order->delivery_location] ?? '—' }}
                    </td>
                    <td><strong>৳{{ number_format($order->total_amount, 0) }}</strong></td>
                    <td>৳{{ number_format($order->paid_amount, 0) }}</td>
                    <td>
                        <span class="status-badge status-{{ $order->status }}">
                            {{ \App\Models\Order::STATUS_LIST[$order->status] ?? 'Unknown' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('customer.order.detail', $order->id) }}"
                           class="btn btn-sm"
                           style="background:var(--bg-light);color:var(--primary);border:1px solid var(--border);font-size:12px;white-space:nowrap;">
                            <i class="fas fa-map-marker-alt"></i> Track
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
@endif

@endsection
