@extends('customer.layouts.panel')

@section('title', 'My Account — Open Se Same')

@section('panel_content')

<div class="panel-section-title">
    <i class="fas fa-home"></i> Welcome Back, {{ Str::words(auth()->user()->name, 1, '') }}!
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">{{ $pendingOrders }}</div>
            <div class="stat-label">Active Orders</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-taka-sign"></i></div>
            <div class="stat-value">৳{{ number_format($totalSpent, 0) }}</div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-bold" style="font-size:15px;color:var(--text-dark);">
        <i class="fas fa-history me-2" style="color:var(--primary);"></i>Recent Orders
    </h5>
    <a href="{{ route('customer.orders') }}" class="btn-outline-custom" style="font-size:12px;padding:6px 14px;">
        View All <i class="fas fa-arrow-right ms-1"></i>
    </a>
</div>

@if($recentOrders->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-shopping-bag" style="font-size:48px;color:var(--border);"></i>
        <p class="mt-3 text-muted">No orders yet. Start shopping!</p>
        <a href="{{ route('shop.products') }}" class="btn-primary-custom mt-2 d-inline-block">
            Browse Products
        </a>
    </div>
@else
    <div class="table-responsive">
        <table class="table order-table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                <tr>
                    <td><strong>M-{{ $order->order_number }}</strong></td>
                    <td>{{ $order->orderItems->count() }} item{{ $order->orderItems->count() != 1 ? 's' : '' }}</td>
                    <td>৳{{ number_format($order->total_amount, 0) }}</td>
                    <td>
                        <span class="status-badge status-{{ $order->status }}">
                            {{ \App\Models\Order::STATUS_LIST[$order->status] ?? 'Unknown' }}
                        </span>
                    </td>
                    <td>{{ $order->order_date?->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('customer.order.detail', $order->id) }}"
                           class="btn btn-sm" style="background:var(--bg-light);color:var(--primary);border:1px solid var(--border);font-size:12px;">
                            <i class="fas fa-eye"></i> Track
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection

@php use Illuminate\Support\Str; @endphp
