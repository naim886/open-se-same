@extends('backend.layouts.app')

@section('content')
@php
    $cms_content['page_title'] = 'Dashboard';
@endphp

{{-- ══════════════════════════════════════════════════════════
     Period Filter Bar
══════════════════════════════════════════════════════════ --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Dashboard Overview</h4>
        <small class="text-muted">
            {{ $dateFrom->format('d M Y') }}
            @if($dateFrom->toDateString() !== $dateTo->toDateString())
                – {{ $dateTo->format('d M Y') }}
            @endif
        </small>
    </div>

    <form method="GET" action="{{ route('dashboard.index') }}" class="d-flex flex-wrap gap-2 align-items-center" id="periodForm">
        <div class="btn-group" role="group">
            @foreach(['today' => 'Today', 'weekly' => 'This Week', 'monthly' => 'This Month', 'last_month' => 'Last Month', 'custom' => 'Custom'] as $key => $label)
            <button type="submit" name="period" value="{{ $key }}"
                    class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <div id="customDateRange" class="{{ $period === 'custom' ? 'd-flex' : 'd-none' }} gap-2 align-items-center flex-wrap">
            <input type="date" name="date_from" class="form-control form-control-sm"
                   value="{{ $request->date_from ?? $dateFrom->toDateString() }}" style="width:145px;">
            <span class="text-muted small">to</span>
            <input type="date" name="date_to" class="form-control form-control-sm"
                   value="{{ $request->date_to ?? $dateTo->toDateString() }}" style="width:145px;">
            <button type="submit" name="period" value="custom" class="btn btn-sm btn-primary">Go</button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════
     Restock alert — raised whenever any product drops below
     the low-stock threshold (5 units).
══════════════════════════════════════════════════════════ --}}
@if($lowStockAlertCount > 0)
<div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 mb-4" role="alert">
    <i class="fas fa-triangle-exclamation"></i>
    <span>
        <strong>Low stock alert:</strong>
        {{ $lowStockAlertCount }} {{ Str::plural('product', $lowStockAlertCount) }}
        {{ $lowStockAlertCount === 1 ? 'has' : 'have' }} fewer than {{ $lowStockThreshold }} units remaining.
    </span>
    <a href="#low-stock-card" class="btn btn-sm theme-button-outline ms-auto">Review list</a>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     Row 1: Key Metrics
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-tint-primary">
                    <i class="fas fa-shopping-bag text-primary fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-muted small">Total Orders</div>
                    <div class="fw-bold fs-3 lh-1">{{ number_format($totalOrders) }}</div>
                    @if($orderGrowth !== null)
                    <div class="small mt-1 {{ $orderGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $orderGrowth >= 0 ? 'up' : 'down' }} me-1" style="font-size:10px;"></i>
                        {{ abs($orderGrowth) }}% vs prev
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-tint-success">
                    <i class="fas fa-money-bill-wave text-success fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-muted small">Total Revenue</div>
                    <div class="fw-bold fs-3 lh-1">৳{{ number_format($totalRevenue, 0) }}</div>
                    @if($revenueGrowth !== null)
                    <div class="small mt-1 {{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }} me-1" style="font-size:10px;"></i>
                        {{ abs($revenueGrowth) }}% vs prev
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-tint-warning">
                    <i class="fas fa-calculator text-warning fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-muted small">Avg. Order Value</div>
                    <div class="fw-bold fs-3 lh-1">৳{{ number_format($avgOrderValue, 0) }}</div>
                    @if($aovGrowth !== null)
                    <div class="small mt-1 {{ $aovGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $aovGrowth >= 0 ? 'up' : 'down' }} me-1" style="font-size:10px;"></i>
                        {{ abs($aovGrowth) }}% vs prev
                    </div>
                    @else
                    <div class="small text-muted mt-1">revenue ÷ {{ number_format($activeOrderCount) }} orders</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-tint-info">
                    <i class="fas fa-circle-check text-info fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-muted small">Paid</div>
                    <div class="fw-bold fs-3 lh-1">৳{{ number_format($paidRevenue, 0) }}</div>
                    <div class="small text-muted mt-1">{{ $totalRevenue > 0 ? round(($paidRevenue/$totalRevenue)*100) : 0 }}% collected</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-tint-danger">
                    <i class="fas fa-hourglass-half text-danger fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-muted small">Due / Unpaid</div>
                    <div class="fw-bold fs-3 lh-1 text-danger">৳{{ number_format($dueRevenue, 0) }}</div>
                    <div class="small text-muted mt-1">pending collection</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     Row 2: Order Status Breakdown
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom fw-semibold py-3 d-flex align-items-center gap-2">
        <i class="fas fa-layer-group text-primary"></i> Order Status Breakdown
        <span class="badge bg-secondary fw-normal ms-1">{{ $totalOrders }} total</span>
    </div>
    <div class="card-body py-3">
        <div class="row g-2">
            @foreach($statusStats as $stat)
            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <a href="{{ route('orders.index', ['status' => $stat['id']]) }}"
                   class="text-decoration-none d-block text-center p-3 rounded-3 h-100 status-stat-card"
                   style="border:1.5px solid var(--border-subtle); background:rgba(2,123,80,.10); transition:.2s;"
                   onmouseover="this.style.background='rgba(2,123,80,.22)';this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(2,123,80,.10)';this.style.transform=''">
                    <div class="fw-bold fs-3 text-{{ $stat['color'] }}">{{ number_format($stat['count']) }}</div>
                    <div class="small text-muted">{{ $stat['label'] }}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     Row 3: Charts
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <span class="fw-semibold"><i class="fas fa-chart-bar me-2 text-primary"></i>Orders & Revenue Over Time</span>
                <div class="btn-group btn-group-sm" id="daywiseToggle">
                    <button class="btn btn-primary active" data-ds="orders">Orders</button>
                    <button class="btn btn-outline-secondary" data-ds="revenue">Revenue</button>
                    <button class="btn btn-outline-secondary" data-ds="both">Both</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="daywiseChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3 fw-semibold">
                <i class="fas fa-chart-pie me-2 text-warning"></i>By Delivery Type
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="deliveryPieChart" style="max-height:180px;"></canvas>
                <div class="mt-3 d-flex flex-column gap-1 w-100" id="pieLegend"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3 fw-semibold">
                <i class="fas fa-user-tag me-2 text-info"></i>By Who Placed
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="placedByPieChart" style="max-height:180px;"></canvas>
                <div class="mt-3 d-flex flex-column gap-1 w-100" id="placedByLegend"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     Row 4: Inventory + Customer Comparison
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3 fw-semibold">
                <i class="fas fa-boxes-stacked me-2 text-success"></i>Inventory Overview
            </div>
            <div class="card-body">
                <div class="row g-3 text-center mb-3">
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-tint-success">
                            <div class="fw-bold fs-3 text-success">{{ number_format($inStockCount) }}</div>
                            <div class="small text-muted">In Stock</div>
                            <div class="tiny text-muted" style="font-size:11px;">stock ≥ {{ $lowStockThreshold }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-tint-warning">
                            <div class="fw-bold fs-3 text-warning">{{ number_format($lowStockCount) }}</div>
                            <div class="small text-muted">Low Stock</div>
                            <div class="tiny text-muted" style="font-size:11px;">1 – {{ $lowStockThreshold - 1 }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded-3 bg-tint-danger">
                            <div class="fw-bold fs-3 text-danger">{{ number_format($outOfStockCount) }}</div>
                            <div class="small text-muted">Out of Stock</div>
                            <div class="tiny text-muted" style="font-size:11px;">stock = 0</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-3 text-center accent-panel">
                    <div class="text-muted small mb-1">Total Inventory Value</div>
                    <div class="fw-bold fs-2">৳{{ number_format($totalInventoryValue, 0) }}</div>
                    <div class="text-muted small">stock × cost price</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-3 fw-semibold">
                <i class="fas fa-users me-2 text-info"></i>Customer Comparison
                <small class="text-muted fw-normal ms-1">(period)</small>
            </div>
            <div class="card-body">
                @php
                    $totalCust = $newCount + $returningCount;
                    $newPct    = $totalCust > 0 ? round(($newCount / $totalCust) * 100) : 0;
                    $retPct    = $totalCust > 0 ? round(($returningCount / $totalCust) * 100) : 0;
                @endphp
                <div class="row g-3 text-center mb-3">
                    <div class="col-6">
                        <div class="p-4 rounded-3 bg-tint-info">
                            <i class="fas fa-user-plus text-info mb-2 d-block fs-4"></i>
                            <div class="fw-bold fs-3 text-info">{{ number_format($newCount) }}</div>
                            <div class="small text-muted">New Customers</div>
                            <span class="badge mt-1" style="background:rgba(56,189,248,.22);color:#38BDF8;">{{ $newPct }}%</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-4 rounded-3 bg-tint-success">
                            <i class="fas fa-user-check text-success mb-2 d-block fs-4"></i>
                            <div class="fw-bold fs-3 text-success">{{ number_format($returningCount) }}</div>
                            <div class="small text-muted">Returning</div>
                            <span class="badge mt-1" style="background:rgba(46,204,135,.22);color:#2ECC87;">{{ $retPct }}%</span>
                        </div>
                    </div>
                </div>
                @if($totalCust > 0)
                <div class="progress rounded-pill mb-1" style="height:14px;">
                    <div class="progress-bar" style="width:{{ $newPct }}%;background:#38BDF8;"
                         data-bs-toggle="tooltip" title="New: {{ $newCount }}"></div>
                    <div class="progress-bar bg-success" style="width:{{ $retPct }}%;"
                         data-bs-toggle="tooltip" title="Returning: {{ $returningCount }}"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span style="color:#38BDF8;">● New {{ $newPct }}%</span>
                    <span>{{ $totalCust }} unique</span>
                    <span class="text-success">● Returning {{ $retPct }}%</span>
                </div>
                @else
                    <p class="text-muted text-center mb-0 py-3">No customer data for this period.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     Row 5: Top Selling Products
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold">
                    <i class="fas fa-fire me-2 text-warning"></i>Top Selling Products
                    <small class="text-muted fw-normal">({{ $period }})</small>
                </span>
                <a href="{{ route('product.index') }}" class="btn btn-sm btn-outline-secondary">All Products</a>
            </div>
            <div class="card-body p-0">
                @if($topSellingProducts->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>No sales recorded in this period.
                    </div>
                @else
                @php $topQty = $topSellingProducts->max('qty') ?: 1; @endphp
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="thead-dm">
                            <tr>
                                <th class="ps-3" style="width:56px;">#</th>
                                <th>Product</th>
                                <th class="text-end">Units Sold</th>
                                <th class="text-end pe-3">Revenue</th>
                                <th class="pe-3" style="width:20%;">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSellingProducts as $i => $p)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge {{ $i === 0 ? 'bg-warning' : 'bg-secondary' }}">{{ $i + 1 }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($p->photo)
                                            <img src="{{ asset('storage/' . $p->photo) }}" alt="" class="rounded-2 flex-shrink-0" style="width:36px;height:36px;object-fit:cover;">
                                        @else
                                            <div class="rounded-2 flex-shrink-0 bg-tint-primary d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                <i class="fas fa-box text-primary" style="font-size:12px;"></i>
                                            </div>
                                        @endif
                                        @if($p->product_id)
                                            <a href="{{ route('product.edit', $p->product_id) }}" class="text-decoration-none dm-link" style="font-size:13px;">{{ Str::limit($p->name ?? 'Unknown product', 44) }}</a>
                                        @else
                                            <span class="dm-link" style="font-size:13px;">{{ Str::limit($p->name ?? 'Unknown product', 44) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($p->qty) }}</td>
                                <td class="text-end pe-3 fw-semibold">৳{{ number_format($p->revenue, 0) }}</td>
                                <td class="pe-3">
                                    <div class="progress rounded-pill" style="height:8px;">
                                        <div class="progress-bar" style="width:{{ round(($p->qty / $topQty) * 100) }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     Row 6: Low Stock Alerts + Recent Orders
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100" id="low-stock-card">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold">
                    <i class="fas fa-triangle-exclamation me-2 text-warning"></i>Low Stock Alerts
                    <small class="text-muted fw-normal ms-1">(&lt; {{ $lowStockThreshold }} units)</small>
                    @if($lowStockAlertCount > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $lowStockAlertCount }}</span>
                    @endif
                </span>
                <a href="{{ route('product.index') }}" class="btn btn-sm btn-outline-secondary">All Products</a>
            </div>
            <div class="card-body p-0">
                @if($lowStockProducts->isEmpty())
                    <div class="text-center py-5 text-success">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                        <span class="small">All products well stocked!</span>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="thead-dm">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Product</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end pe-3">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $i => $p)
                            <tr>
                                <td class="ps-3 text-muted small">{{ $i+1 }}</td>
                                <td>
                                    <a href="{{ route('product.edit', $p->id) }}" class="text-decoration-none dm-link" style="font-size:13px;">
                                        {{ Str::limit($p->name, 34) }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if($p->stock == 0)
                                        <span class="badge bg-danger">Out</span>
                                    @elseif($p->stock <= 2)
                                        <span class="badge bg-danger">{{ $p->stock }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $p->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-muted small">৳{{ number_format($p->price, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold">
                    <i class="fas fa-list-check me-2 text-primary"></i>Recent Orders
                    <small class="text-muted fw-normal">({{ $period }})</small>
                </span>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>No orders for this period.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="thead-dm">
                            <tr>
                                <th class="ps-3">Order</th>
                                <th>Customer</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-decoration-none fw-semibold small text-primary">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div style="font-size:13px;">{{ Str::limit($order->customer_name, 16) }}</div>
                                    <div class="text-muted" style="font-size:11px;">{{ $order->customer_phone }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $order->getStatusColor() }}" style="font-size:11px;">
                                        {{ $order->getStatusText() }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold small">৳{{ number_format($order->total_amount, 0) }}</td>
                                <td class="text-end pe-3 text-muted" style="font-size:11.5px;">
                                    {{ $order->order_date?->format('d M, h:i A') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    // ── Custom range toggle ──────────────────────────────────────────────
    document.querySelectorAll('#periodForm button').forEach(btn => {
        btn.addEventListener('click', function () {
            const isCustom = this.value === 'custom';
            const range = document.getElementById('customDateRange');
            range.classList.toggle('d-flex', isCustom);
            range.classList.toggle('d-none', !isCustom);
        });
    });

    // ── Daywise Chart ────────────────────────────────────────────────────
    const daywiseData = @json($days);
    const labels    = daywiseData.map(d => d.label);
    const ordersDs  = daywiseData.map(d => d.orders);
    const revenueDs = daywiseData.map(d => d.revenue);

    const daywiseChart = new Chart(document.getElementById('daywiseChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersDs,
                    backgroundColor: 'rgba(2,123,80,.75)',
                    borderRadius: 5,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Revenue (৳)',
                    data: revenueDs,
                    type: 'line',
                    borderColor: '#FFCF2F',
                    backgroundColor: 'rgba(255,207,47,.12)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#FFCF2F',
                    yAxisID: 'y2',
                    order: 1,
                    hidden: true,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label.includes('Revenue')
                            ? ' ৳' + ctx.parsed.y.toLocaleString()
                            : ' ' + ctx.parsed.y + ' orders'
                    }
                }
            },
            scales: {
                y:  { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, title: { display: true, text: 'Orders' } },
                y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue (৳)' }, display: false },
            }
        }
    });

    document.querySelectorAll('#daywiseToggle button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#daywiseToggle button').forEach(b => {
                b.classList.replace('btn-primary', 'btn-outline-secondary');
            });
            this.classList.replace('btn-outline-secondary', 'btn-primary');
            const ds = this.dataset.ds;
            daywiseChart.data.datasets[0].hidden = (ds === 'revenue');
            daywiseChart.data.datasets[1].hidden = (ds === 'orders');
            daywiseChart.options.scales.y2.display = (ds !== 'orders');
            daywiseChart.update();
        });
    });

    // ── Delivery Pie ─────────────────────────────────────────────────────
    const pieData   = @json($deliveryPie);
    const pieColors = ['#027B50','#FFCF2F','#04A871','#38BDF8','#2ECC87','#E0B100','#FF6B6B'];

    if (pieData.length > 0) {
        new Chart(document.getElementById('deliveryPieChart'), {
            type: 'doughnut',
            data: {
                labels: pieData.map(d => d.label),
                datasets: [{
                    data:            pieData.map(d => d.count),
                    backgroundColor: pieColors.slice(0, pieData.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} orders` } }
                }
            }
        });

        const total  = pieData.reduce((s, d) => s + d.count, 0);
        const legend = document.getElementById('pieLegend');
        pieData.forEach((d, i) => {
            const pct = total > 0 ? Math.round((d.count / total) * 100) : 0;
            legend.insertAdjacentHTML('beforeend',
                `<div class="d-flex justify-content-between align-items-center small py-1"
                      style="border-bottom:1px solid rgba(255,255,255,.07);">
                    <span>
                        <span class="rounded-1 me-2 d-inline-block align-middle"
                              style="width:11px;height:11px;background:${pieColors[i]};"></span>${d.label}
                    </span>
                    <span class="fw-semibold">${d.count} <span class="text-muted fw-normal">(${pct}%)</span></span>
                </div>`
            );
        });
    } else {
        document.getElementById('deliveryPieChart').parentElement.innerHTML =
            '<p class="text-muted text-center mb-0 py-4 small">No delivery data for this period.</p>';
    }

    // ── Placed-By Pie ─────────────────────────────────────────────────────
    const placedByData   = @json($placedByPie);
    const placedByColors = ['#38BDF8','#039C63','#FFCF2F','#2ECC87','#FF6B6B','#027B50','#E0B100','#015C3C','#8FC7AF','#adb5bd'];

    if (placedByData.length > 0) {
        new Chart(document.getElementById('placedByPieChart'), {
            type: 'doughnut',
            data: {
                labels: placedByData.map(d => d.label),
                datasets: [{
                    data:            placedByData.map(d => d.count),
                    backgroundColor: placedByColors.slice(0, placedByData.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} orders` } }
                }
            }
        });

        const pbTotal  = placedByData.reduce((s, d) => s + d.count, 0);
        const pbLegend = document.getElementById('placedByLegend');
        placedByData.forEach((d, i) => {
            const pct = pbTotal > 0 ? Math.round((d.count / pbTotal) * 100) : 0;
            pbLegend.insertAdjacentHTML('beforeend',
                `<div class="d-flex justify-content-between align-items-center small py-1"
                      style="border-bottom:1px solid rgba(255,255,255,.07);">
                    <span>
                        <span class="rounded-1 me-2 d-inline-block align-middle"
                              style="width:11px;height:11px;background:${placedByColors[i]};"></span>${d.label}
                    </span>
                    <span class="fw-semibold">${d.count} <span class="text-muted fw-normal">(${pct}%)</span></span>
                </div>`
            );
        });
    } else {
        document.getElementById('placedByPieChart').parentElement.innerHTML =
            '<p class="text-muted text-center mb-0 py-4 small">No data for this period.</p>';
    }

    // Bootstrap tooltips init
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
})();
</script>
@endpush
