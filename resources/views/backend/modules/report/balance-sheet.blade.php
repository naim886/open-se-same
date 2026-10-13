@extends('backend.layouts.app')

@section('title', 'Balance Sheet')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-balance-scale me-2 text-primary"></i>Balance Sheet</h4>
            <div class="small text-muted mt-1"><i class="fas fa-calendar-alt me-1"></i>{{ $label }}</div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    {{-- ── Period Filter ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.balance-sheet') }}" id="bsForm" class="d-flex flex-wrap gap-2 align-items-end">
                @php $periods = ['today'=>'Today','last_7'=>'Last 7 Days','this_month'=>'This Month','last_month'=>'Last Month','custom'=>'Custom']; @endphp
                <div class="btn-group btn-group-sm me-2">
                    @foreach($periods as $val => $lbl)
                        <button type="button"
                            class="btn {{ $period === $val ? 'btn-primary' : 'btn-outline-secondary' }} period-btn"
                            data-period="{{ $val }}">{{ $lbl }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
                <div id="customRange" class="{{ $period === 'custom' ? 'd-flex' : 'd-none' }} gap-2 align-items-end flex-wrap">
                    <div>
                        <label class="form-label mb-1 small">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $period === 'custom' ? $request->date_from : '' }}">
                    </div>
                    <div>
                        <label class="form-label mb-1 small">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $period === 'custom' ? $request->date_to : '' }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── LEFT: Income & Expenses ── --}}
        <div class="col-lg-8">

            {{-- Income Section --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success bg-opacity-10 border-0 fw-semibold py-3">
                    <i class="fas fa-arrow-down text-success me-2"></i>Income (Orders)
                    <span class="badge bg-secondary fw-normal ms-2">{{ $totalOrders }} orders</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Description</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end pe-3">Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3">Total Orders (excl. Cancelled/Returned)</td>
                                <td class="text-end">{{ $totalOrders }}</td>
                                <td class="text-end">৳{{ number_format($totalRevenue, 0) }}</td>
                                <td class="text-end pe-3 fw-bold text-success">৳{{ number_format($collectedAmount, 0) }}</td>
                            </tr>
                            <tr class="table-warning">
                                <td class="ps-3 text-muted">Due / Uncollected</td>
                                <td class="text-end">—</td>
                                <td class="text-end">—</td>
                                <td class="text-end pe-3 fw-bold text-danger">৳{{ number_format($dueAmount, 0) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-success fw-bold">
                                <td class="ps-3">TOTAL INCOME (Collected)</td>
                                <td colspan="2" class="text-end"></td>
                                <td class="text-end pe-3">৳{{ number_format($collectedAmount, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Expenses Section --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger bg-opacity-10 border-0 fw-semibold py-3">
                    <i class="fas fa-arrow-up text-danger me-2"></i>Expenses (Approved)
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Category</th>
                                <th class="text-end pe-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expByCategory as $exp)
                            <tr>
                                <td class="ps-3">{{ \App\Models\Expense::CATEGORY_LIST[$exp->category] ?? ucfirst($exp->category) }}</td>
                                <td class="text-end pe-3">৳{{ number_format($exp->total, 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No approved expenses for this period.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-danger fw-bold">
                                <td class="ps-3">TOTAL EXPENSES</td>
                                <td class="text-end pe-3">৳{{ number_format($totalExpenses, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Net P&L --}}
            <div class="card border-0 shadow-sm mb-4 {{ $grossProfit >= 0 ? 'border-success' : 'border-danger' }}" style="border-left: 4px solid !important;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-bold fs-5">
                            <i class="fas fa-calculator me-2 text-primary"></i>Net Profit / Loss
                            <div class="text-muted small fw-normal">Collected - Approved Expenses</div>
                        </div>
                        <div class="fw-bold fs-3 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $grossProfit >= 0 ? '+' : '' }}৳{{ number_format($grossProfit, 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Account Balances ── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary bg-opacity-10 border-0 fw-semibold py-3">
                    <i class="fas fa-wallet text-primary me-2"></i>Account Balances (All-Time)
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <tbody>
                            @foreach(\App\Models\AccountTransaction::ACCOUNT_LIST as $key => $accLabel)
                            @php $bal = $accountBalances[$key] ?? 0; @endphp
                            <tr>
                                <td class="ps-3">
                                    <i class="{{ \App\Models\AccountTransaction::ACCOUNT_ICONS[$key] }} me-2 text-{{ \App\Models\AccountTransaction::ACCOUNT_COLORS[$key] }}"></i>
                                    {{ $accLabel }}
                                </td>
                                <td class="text-end pe-3 fw-bold {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">
                                    ৳{{ number_format($bal, 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td class="ps-3">TOTAL</td>
                                <td class="text-end pe-3 {{ $totalAccountBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                    ৳{{ number_format($totalAccountBalance, 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer bg-transparent border-0 text-end">
                    <a href="{{ route('accounts.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Manage Accounts
                    </a>
                </div>
            </div>

            {{-- Quick summary --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom fw-semibold py-3">
                    <i class="fas fa-info-circle text-secondary me-2"></i>Summary
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><td class="ps-3 text-muted">Total Revenue (invoiced)</td><td class="text-end pe-3 fw-semibold">৳{{ number_format($totalRevenue, 0) }}</td></tr>
                        <tr><td class="ps-3 text-muted">Collected</td><td class="text-end pe-3 text-success fw-semibold">৳{{ number_format($collectedAmount, 0) }}</td></tr>
                        <tr><td class="ps-3 text-muted">Due / Unpaid</td><td class="text-end pe-3 text-warning fw-semibold">৳{{ number_format($dueAmount, 0) }}</td></tr>
                        <tr><td class="ps-3 text-muted">Expenses</td><td class="text-end pe-3 text-danger fw-semibold">৳{{ number_format($totalExpenses, 0) }}</td></tr>
                        <tr class="table-light fw-bold"><td class="ps-3">Net P&L</td><td class="text-end pe-3 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ $grossProfit >= 0 ? '+' : '' }}৳{{ number_format($grossProfit, 0) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('extra_js')
<script>
document.querySelectorAll('.period-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('periodInput').value = this.dataset.period;
        document.getElementById('customRange').classList.toggle('d-flex', this.dataset.period === 'custom');
        document.getElementById('customRange').classList.toggle('d-none', this.dataset.period !== 'custom');
        if (this.dataset.period !== 'custom') document.getElementById('bsForm').submit();
    });
});
</script>
@endsection
@endsection
