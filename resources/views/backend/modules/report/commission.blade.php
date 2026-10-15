@extends('backend.layouts.app')

@section('title', 'Commission Report')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-hand-holding-usd me-2 text-warning"></i>Commission Report</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ number_format($grandDelivered) }} delivered &nbsp;·&nbsp;
                Total Payable: <span class="fw-semibold text-warning">৳{{ number_format($grandCommission, 0) }}</span>
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    {{-- ── Period Filter ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.commission') }}" id="reportForm" class="d-flex flex-wrap gap-2 align-items-end">
                @php
                    $periods = [
                        'today'      => 'Today',
                        'last_7'     => 'Last 7 Days',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'custom'     => 'Custom',
                    ];
                @endphp
                <div class="btn-group btn-group-sm me-2" role="group">
                    @foreach($periods as $val => $lbl)
                        <button type="button"
                            class="btn {{ $period === $val ? 'btn-primary' : 'btn-outline-secondary' }} period-btn"
                            data-period="{{ $val }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

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
                <div class="text-muted small mb-1">Delivered Orders</div>
                <div class="fw-bold fs-2 text-success">{{ number_format($grandDelivered) }}</div>
                <div class="small text-muted">Delivered + Partial</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Rate per Delivery</div>
                <div class="fw-bold fs-2 text-info">৳10</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3"
                 style="border:1.5px solid rgba(255,193,7,.4) !important; background:rgba(255,193,7,.06) !important;">
                <div class="text-muted small mb-1">Total Payable</div>
                <div class="fw-bold fs-2 text-warning">৳{{ number_format($grandCommission, 0) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Commission Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
            <span class="fw-semibold">
                <i class="fas fa-table me-2 text-warning"></i>Commission Breakdown by Staff
            </span>
            <span class="badge bg-secondary fw-normal">
                {{ collect($userReport)->where('is_system', false)->count() }} staff
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle commission-table" id="commissionTable">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3" style="min-width:150px;">Staff / Source</th>
                            <th class="text-center" style="min-width:70px;">Total</th>
                            @foreach(\App\Models\Order::STATUS_LIST as $sid => $slabel)
                                <th class="text-center" style="min-width:95px;">
                                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$sid] ?? 'secondary' }}"
                                          style="font-size:12px;white-space:normal;line-height:1.4;">
                                        {{ $slabel }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="text-center" style="min-width:90px; background:rgba(25,135,84,.08);">
                                <span class="text-success fw-semibold" style="font-size:12px;">Delivered<br><small class="text-muted fw-normal" style="font-size:10px;">(+Partial)</small></span>
                            </th>
                            <th class="text-center pe-3" style="min-width:120px; background:rgba(255,193,7,.08);">
                                <span class="text-warning fw-bold" style="font-size:13px;">Payable<br><small class="text-muted fw-normal" style="font-size:10px;">delivered × ৳10</small></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userReport as $user)
                        <tr class="{{ $user['is_system'] ? 'opacity-75' : '' }}">
                            <td class="ps-3 fw-semibold">
                                @if($user['is_system'])
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

                            {{-- Delivered count (delivered + partial) ──────── --}}
                            <td class="text-center fw-bold text-success" style="background:rgba(25,135,84,.05);">
                                {{ $user['delivered'] > 0 ? number_format($user['delivered']) : '—' }}
                            </td>

                            {{-- Payable commission ────────────────────────── --}}
                            <td class="text-center pe-3 fw-bold" style="background:rgba(255,193,7,.06);">
                                @if($user['is_system'])
                                    <span class="text-muted small">N/A</span>
                                @elseif($user['commission'] > 0)
                                    <span class="text-warning" style="font-size:15px;">
                                        ৳{{ number_format($user['commission'], 0) }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count(\App\Models\Order::STATUS_LIST) + 4 }}"
                                class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No orders for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- Grand total row --}}
                    @if(count($userReport) > 1)
                    <tfoot>
                        <tr style="border-top:2px solid var(--border-strong);">
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
                            <td class="text-center fw-bold text-success" style="background:rgba(25,135,84,.05);">
                                {{ $grandDelivered > 0 ? number_format($grandDelivered) : '—' }}
                            </td>
                            <td class="text-center pe-3 fw-bold text-warning" style="background:rgba(255,193,7,.06);font-size:15px;">
                                ৳{{ number_format($grandCommission, 0) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ── Commission note ── --}}
    <div class="text-muted small mt-3">
        <i class="fas fa-info-circle me-1"></i>
        Commission is calculated as <strong>Delivered + Partial Delivered</strong> orders × ৳10 per delivery.
        System-placed orders are excluded from commission.
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
        .commission-table th, .commission-table td { color: #000 !important; }
        .badge { border: 1px solid #999 !important; color: #000 !important; background: #eee !important; }
    }

    .commission-table tfoot td {
        background: rgba(2,123,80,.12);
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const periodInput = document.getElementById('periodInput');
    const customRange = document.getElementById('customRange');
    const form        = document.getElementById('reportForm');

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const val = this.dataset.period;
            document.querySelectorAll('.period-btn').forEach(b => {
                b.classList.replace('btn-primary', 'btn-outline-secondary');
            });
            this.classList.replace('btn-outline-secondary', 'btn-primary');
            periodInput.value = val;
            customRange.classList.toggle('d-flex', val === 'custom');
            customRange.classList.toggle('d-none', val !== 'custom');
            if (val !== 'custom') form.submit();
        });
    });
})();
</script>
@endpush
