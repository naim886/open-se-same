@extends('backend.layouts.app')

@section('title', 'Profit & Margin')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-warning"></i>Profit &amp; Margin</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ number_format($totalQty) }} units
                &nbsp;·&nbsp; ৳{{ number_format($totalRevenue, 0) }} product revenue
                &nbsp;·&nbsp; ৳{{ number_format($totalProfit, 0) }} gross profit
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    @include('backend.modules.report.partials._period_filter', [
        'action'  => route('reports.margin'),
        'period'  => $period,
        'request' => $request,
    ])

    @if($missingCost > 0)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="fas fa-triangle-exclamation mt-1"></i>
            <div>
                <strong>{{ number_format($missingCost) }}</strong> sold line(s) in this period have no
                cost recorded, so their cost is counted as ৳0 and profit is overstated.
                Add a cost to those products (or their variations) to fix this — note that
                corrections only affect <em>future</em> orders, since costs are snapshotted at sale time.
            </div>
        </div>
    @endif

    {{-- ── Summary ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Product Revenue</div>
                <div class="fw-bold fs-3 text-primary">৳{{ number_format($totalRevenue, 0) }}</div>
                <div class="small text-muted">excludes delivery</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Cost of Goods</div>
                <div class="fw-bold fs-3 text-secondary">৳{{ number_format($totalCost, 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Gross Profit</div>
                <div class="fw-bold fs-3 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    ৳{{ number_format($totalProfit, 0) }}
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Margin</div>
                <div class="fw-bold fs-3 {{ $totalMargin >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($totalMargin, 1) }}%
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <ul class="nav nav-tabs mb-0" id="marginTabs">
        <li class="nav-item">
            <button class="nav-link active margin-tab-btn" data-target="byProductPane">
                <i class="fas fa-box me-1"></i>By Product
                <span class="badge bg-secondary ms-1">{{ $byProduct->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link margin-tab-btn" data-target="byCategoryPane">
                <i class="fas fa-tags me-1"></i>By Category
                <span class="badge bg-secondary ms-1">{{ $byCategory->count() }}</span>
            </button>
        </li>
    </ul>

    {{-- ── By Product ── --}}
    <div class="card border-0 shadow-sm mb-4 margin-tab-pane" id="byProductPane">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="thead-dm">
                    <tr>
                        <th class="ps-3" style="width:46px">#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-end">Units</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                        <th class="text-end pe-3">Margin</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($byProduct as $i => $row)
                    <tr>
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td class="fw-600">{{ $row->product_name ?? 'Deleted product #'.$row->product_id }}</td>
                        <td class="text-muted small">{{ $row->category_name ?? '—' }}</td>
                        <td class="text-end">{{ number_format($row->qty_sold) }}</td>
                        <td class="text-end">৳{{ number_format($row->revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($row->cost_total, 0) }}</td>
                        <td class="text-end fw-semibold {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($row->profit, 0) }}
                        </td>
                        <td class="text-end pe-3 {{ $row->margin_pct >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($row->margin_pct, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales in this period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── By Category ── --}}
    <div class="card border-0 shadow-sm mb-4 margin-tab-pane d-none" id="byCategoryPane">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="thead-dm">
                    <tr>
                        <th class="ps-3" style="width:46px">#</th>
                        <th>Category</th>
                        <th class="text-end">Units</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                        <th class="text-end pe-3">Margin</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($byCategory as $i => $row)
                    <tr>
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td class="fw-600">{{ $row->category_name ?? 'Uncategorised' }}</td>
                        <td class="text-end">{{ number_format($row->qty_sold) }}</td>
                        <td class="text-end">৳{{ number_format($row->revenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($row->cost_total, 0) }}</td>
                        <td class="text-end fw-semibold {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($row->profit, 0) }}
                        </td>
                        <td class="text-end pe-3 {{ $row->margin_pct >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($row->margin_pct, 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales in this period.</td></tr>
                @endforelse
                </tbody>
                @if($byCategory->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="ps-3" colspan="2">Total</td>
                        <td class="text-end">{{ number_format($totalQty) }}</td>
                        <td class="text-end">৳{{ number_format($totalRevenue, 0) }}</td>
                        <td class="text-end">৳{{ number_format($totalCost, 0) }}</td>
                        <td class="text-end {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($totalProfit, 0) }}
                        </td>
                        <td class="text-end pe-3 {{ $totalMargin >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($totalMargin, 1) }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.margin-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.margin-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.margin-tab-pane').forEach(p => p.classList.add('d-none'));

            this.classList.add('active');
            const pane = document.getElementById(this.dataset.target);
            if (pane) pane.classList.remove('d-none');
        });
    });
})();
</script>
@endpush
