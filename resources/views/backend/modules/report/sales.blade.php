@extends('backend.layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Sales Report</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ number_format($grandTotal) }} orders &nbsp;·&nbsp; ৳{{ number_format($grandRevenue, 0) }} revenue
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    {{-- ── Period Filter ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.sales') }}" id="reportForm" class="d-flex flex-wrap gap-2 align-items-end">

                {{-- Quick period buttons --}}
                <div class="btn-group btn-group-sm me-2" role="group">
                    @php
                        $periods = [
                            'today'      => 'Today',
                            'last_7'     => 'Last 7 Days',
                            'this_month' => 'This Month',
                            'last_month' => 'Last Month',
                            'custom'     => 'Custom',
                        ];
                    @endphp
                    @foreach($periods as $val => $lbl)
                        <button type="button"
                            class="btn {{ $period === $val ? 'btn-primary' : 'btn-outline-secondary' }} period-btn"
                            data-period="{{ $val }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

                {{-- Custom range --}}
                <div id="customRange" class="{{ $period === 'custom' ? 'd-flex' : 'd-none' }} gap-2 align-items-end flex-wrap">
                    <div>
                        <label class="form-label mb-1 small">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ $period === 'custom' ? $request->date_from : '' }}">
                    </div>
                    <div>
                        <label class="form-label mb-1 small">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ $period === 'custom' ? $request->date_to : '' }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search me-1"></i>Apply
                </button>
            </form>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Total Orders</div>
                <div class="fw-bold fs-2 text-primary">{{ number_format($grandTotal) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Total Revenue</div>
                <div class="fw-bold fs-2 text-success">৳{{ number_format($grandRevenue, 0) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Amount Collected</div>
                <div class="fw-bold fs-2 text-info">৳{{ number_format($grandPaid, 0) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Due / Unpaid</div>
                <div class="fw-bold fs-2 text-danger">৳{{ number_format($grandRevenue - $grandPaid, 0) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Main Report Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="fas fa-table me-2 text-primary"></i>Orders by Staff / Source
            </span>
            <span class="badge bg-secondary fw-normal">{{ count($userReport) }} users</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle report-table" id="salesTable">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3" style="min-width:140px;">Staff / Source</th>
                            <th class="text-center" style="min-width:75px;">Total</th>
                            @foreach(\App\Models\Order::STATUS_LIST as $sid => $slabel)
                                <th class="text-center status-col" style="min-width:90px;" data-sid="{{ $sid }}">
                                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$sid] ?? 'secondary' }}"
                                          style="font-size:12px;white-space:normal;line-height:1.4;">
                                        {{ $slabel }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="text-end" style="min-width:110px;">Revenue</th>
                            <th class="text-end" style="min-width:110px;">Collected</th>
                            <th class="text-end pe-3" style="min-width:100px;">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userReport as $user)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                @if($user['label'] === 'System')
                                    <i class="fas fa-robot text-muted me-1"></i>
                                @else
                                    <i class="fas fa-user-circle text-info me-1"></i>
                                @endif
                                {{ $user['label'] }}
                            </td>
                            <td class="text-center fw-bold text-primary">{{ number_format($user['total']) }}</td>
                            @foreach(\App\Models\Order::STATUS_LIST as $sid => $slabel)
                                <td class="text-center">
                                    @if($user['statuses'][$sid] > 0)
                                        <a href="{{ route('orders.index', ['status' => $sid]) }}"
                                           class="badge text-decoration-none bg-{{ \App\Models\Order::STATUS_COLORS[$sid] ?? 'secondary' }}"
                                           style="font-size:13px;" title="View {{ $slabel }} orders">
                                            {{ $user['statuses'][$sid] }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end text-success fw-semibold">৳{{ number_format($user['revenue'], 0) }}</td>
                            <td class="text-end text-info">৳{{ number_format($user['paid'], 0) }}</td>
                            <td class="text-end pe-3 text-danger">৳{{ number_format($user['revenue'] - $user['paid'], 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count(\App\Models\Order::STATUS_LIST) + 5 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No orders for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- Grand total row --}}
                    @if(count($userReport) > 1)
                    <tfoot>
                        <tr style="border-top:2px solid rgba(212,236,255,.3);">
                            <td class="ps-3 fw-bold text-uppercase small" style="letter-spacing:.05em;">Grand Total</td>
                            <td class="text-center fw-bold text-primary">{{ number_format($grandTotal) }}</td>
                            @foreach(\App\Models\Order::STATUS_LIST as $sid => $slabel)
                                <td class="text-center fw-semibold">
                                    @if($grandStatuses[$sid] > 0)
                                        <span class="text-{{ \App\Models\Order::STATUS_COLORS[$sid] ?? 'secondary' }}">
                                            {{ $grandStatuses[$sid] }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end fw-bold text-success">৳{{ number_format($grandRevenue, 0) }}</td>
                            <td class="text-end fw-bold text-info">৳{{ number_format($grandPaid, 0) }}</td>
                            <td class="text-end pe-3 fw-bold text-danger">৳{{ number_format($grandRevenue - $grandPaid, 0) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        .sb-sidenav-toggler, .sb-topnav, nav.sb-sidenav, .btn, form { display: none !important; }
        #layoutSidenav_content { margin-left: 0 !important; }
        body { background: #fff !important; color: #000 !important; }
        .card { border: 1px solid #ccc !important; background: #fff !important; color: #000 !important; }
        .report-table th, .report-table td { color: #000 !important; }
        .badge { border: 1px solid #999 !important; color: #000 !important; background: #eee !important; }
    }

    .report-table tfoot td {
        background: rgba(13,110,253,.06);
    }

    .period-btn.btn-primary {
        pointer-events: none;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const periodInput  = document.getElementById('periodInput');
    const customRange  = document.getElementById('customRange');
    const form         = document.getElementById('reportForm');

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const val = this.dataset.period;

            // Toggle active state
            document.querySelectorAll('.period-btn').forEach(b => {
                b.classList.replace('btn-primary', 'btn-outline-secondary');
            });
            this.classList.replace('btn-outline-secondary', 'btn-primary');

            periodInput.value = val;

            // Show/hide custom range
            customRange.classList.toggle('d-flex', val === 'custom');
            customRange.classList.toggle('d-none', val !== 'custom');

            // Auto-submit non-custom immediately
            if (val !== 'custom') {
                form.submit();
            }
        });
    });
})();
</script>
@endpush
