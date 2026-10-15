@extends('backend.layouts.app')

@section('title', 'Category Sales')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Category Sales</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ number_format($categories->count()) }} categories
                &nbsp;·&nbsp; {{ number_format($totalQty) }} units
                &nbsp;·&nbsp; ৳{{ number_format($totalRevenue, 0) }} product revenue
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    @include('backend.modules.report.partials._period_filter', [
        'action'  => route('reports.category-sales'),
        'period'  => $period,
        'request' => $request,
    ])

    {{-- ── Summary ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Product Revenue</div>
                <div class="fw-bold fs-3 text-primary">৳{{ number_format($totalRevenue, 0) }}</div>
                <div class="small text-muted">excludes delivery &amp; cancelled orders</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Categories Sold</div>
                <div class="fw-bold fs-3 text-success">{{ number_format($categories->count()) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Units Sold</div>
                <div class="fw-bold fs-3 text-info">{{ number_format($totalQty) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Top Category</div>
                <div class="fw-bold fs-5 text-truncate" style="min-height:38px;">
                    {{ $categories->first()->category_name ?? '—' }}
                </div>
                <div class="small text-muted">
                    @if($categories->isNotEmpty())
                        ৳{{ number_format($categories->first()->revenue, 0) }}
                        ({{ number_format($categories->first()->share, 1) }}%)
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">
            <i class="fas fa-chart-simple me-1"></i>Revenue by Category
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="thead-dm">
                    <tr>
                        <th class="ps-3" style="width:46px">#</th>
                        <th>Category</th>
                        <th class="text-end">Products</th>
                        <th class="text-end">Orders</th>
                        <th class="text-end">Units</th>
                        <th class="text-end">Revenue</th>
                        <th style="min-width:170px;">Share</th>
                        <th class="text-end pe-3">Avg Order</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $i => $row)
                    <tr>
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td class="fw-600">{{ $row->category_name ?? 'Uncategorised' }}</td>
                        <td class="text-end">{{ number_format($row->product_count) }}</td>
                        <td class="text-end">{{ number_format($row->order_count) }}</td>
                        <td class="text-end">{{ number_format($row->qty_sold) }}</td>
                        <td class="text-end fw-semibold">৳{{ number_format($row->revenue, 0) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ min(100, $row->share) }}%"></div>
                                </div>
                                <span class="small text-muted" style="min-width:46px;">
                                    {{ number_format($row->share, 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="text-end pe-3">৳{{ number_format($row->aov, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No sales in this period.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($categories->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="ps-3" colspan="2">Total</td>
                        <td class="text-end">{{ number_format($categories->sum('product_count')) }}</td>
                        <td class="text-end">{{ number_format($categories->sum('order_count')) }}</td>
                        <td class="text-end">{{ number_format($totalQty) }}</td>
                        <td class="text-end">৳{{ number_format($totalRevenue, 0) }}</td>
                        <td></td>
                        <td class="pe-3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
