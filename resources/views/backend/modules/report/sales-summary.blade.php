@extends('backend.layouts.app')

@section('title', 'Sales Summary')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-chart-column me-2 text-info"></i>Sales Summary</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ $granularityLabels[$granularity] ?? '' }} breakdown
                &nbsp;·&nbsp; {{ number_format($totals->order_count) }} orders
                &nbsp;·&nbsp; ৳{{ number_format($totals->revenue, 0) }} revenue
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    @include('backend.modules.report.partials._period_filter', [
        'action'            => route('reports.sales-summary'),
        'period'            => $period,
        'request'           => $request,
        'showGranularity'   => true,
        'granularity'       => $granularity,
        'granularityLabels' => $granularityLabels,
    ])

    {{-- ── Summary ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Total Revenue</div>
                <div class="fw-bold fs-3 text-primary">৳{{ number_format($totals->revenue, 0) }}</div>
                <div class="small text-muted">incl. delivery</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Orders</div>
                <div class="fw-bold fs-3 text-info">{{ number_format($totals->order_count) }}</div>
                <div class="small text-muted">AOV ৳{{ number_format($totals->aov, 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Units Sold</div>
                <div class="fw-bold fs-3 text-secondary">{{ number_format($totals->qty_sold) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Gross Profit</div>
                <div class="fw-bold fs-3 {{ $totals->profit >= 0 ? 'text-success' : 'text-danger' }}">
                    ৳{{ number_format($totals->profit, 0) }}
                </div>
                <div class="small text-muted">
                    @if($best)
                        Best: {{ $best->label }} (৳{{ number_format($best->revenue, 0) }})
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">
            <i class="fas fa-table-list me-1"></i>{{ $granularityLabels[$granularity] ?? '' }} Breakdown
            <span class="text-muted fw-normal small ms-2">cancelled &amp; returned orders excluded</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="thead-dm">
                    <tr>
                        <th class="ps-3">{{ $granularityLabels[$granularity] ?? 'Period' }}</th>
                        <th class="text-end">Orders</th>
                        <th class="text-end">Customers</th>
                        <th class="text-end">Units</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">AOV</th>
                        <th class="text-end">Product Rev.</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end pe-3">Profit</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($buckets as $row)
                    <tr>
                        <td class="ps-3 fw-600">{{ $row->label }}</td>
                        <td class="text-end">{{ number_format($row->order_count) }}</td>
                        <td class="text-end">{{ number_format($row->customer_count) }}</td>
                        <td class="text-end">{{ number_format($row->qty_sold) }}</td>
                        <td class="text-end fw-semibold">৳{{ number_format($row->revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($row->aov, 0) }}</td>
                        <td class="text-end">৳{{ number_format($row->product_revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($row->cost_total, 0) }}</td>
                        <td class="text-end pe-3 {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($row->profit, 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No orders in this period.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($buckets->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="ps-3">Total</td>
                        <td class="text-end">{{ number_format($totals->order_count) }}</td>
                        <td class="text-end"></td>
                        <td class="text-end">{{ number_format($totals->qty_sold) }}</td>
                        <td class="text-end">৳{{ number_format($totals->revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($totals->aov, 0) }}</td>
                        <td class="text-end">৳{{ number_format($totals->product_revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($totals->cost_total, 0) }}</td>
                        <td class="text-end pe-3 {{ $totals->profit >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($totals->profit, 0) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
