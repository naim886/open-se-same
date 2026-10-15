@extends('backend.layouts.app')

@section('title', 'Product Performance')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Product Performance</h4>
            <div class="small text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>{{ $label }}
                &nbsp;·&nbsp; {{ number_format($totalSoldCount) }} sold &nbsp;·&nbsp;
                {{ number_format($totalNeverSold) }} never sold &nbsp;·&nbsp;
                ৳{{ number_format($totalRevenue, 0) }} revenue
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    {{-- ── Period Filter ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.product-performance') }}" id="reportForm"
                  class="d-flex flex-wrap gap-2 align-items-end">
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
                            data-period="{{ $val }}">{{ $lbl }}</button>
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
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Total Products</div>
                <div class="fw-bold fs-2 text-primary">{{ number_format($totalProducts) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Sold This Period</div>
                <div class="fw-bold fs-2 text-success">{{ number_format($totalSoldCount) }}</div>
                <div class="small text-muted">unique products</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <div class="text-muted small mb-1">Total Qty Sold</div>
                <div class="fw-bold fs-2 text-info">{{ number_format($totalQtySold) }}</div>
                <div class="small text-muted">units</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center p-3"
                 style="border:1.5px solid rgba(220,53,69,.3) !important; background:rgba(220,53,69,.05) !important;">
                <div class="text-muted small mb-1">Never Sold (all-time)</div>
                <div class="fw-bold fs-2 text-danger">{{ number_format($totalNeverSold) }}</div>
                <div class="small text-muted">products</div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <ul class="nav nav-tabs mb-0" id="perfTabs">
        <li class="nav-item">
            <button class="nav-link active perf-tab-btn" data-target="bestSelling"
                    style="border-bottom-color:transparent;">
                <i class="fas fa-fire text-warning me-1"></i>
                Best Selling
                <span class="badge bg-success ms-1">{{ $bestSelling->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link perf-tab-btn" data-target="neverSold">
                <i class="fas fa-box-open text-danger me-1"></i>
                Never Sold
                <span class="badge bg-danger ms-1">{{ $neverSold->count() }}</span>
            </button>
        </li>
    </ul>

    {{-- ══ Best Selling Tab ══ --}}
    <div id="bestSelling" class="perf-tab-pane">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
            <div class="card-body p-0">
                @if($bestSelling->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>No sales data for this period.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="thead-dm">
                            <tr>
                                <th class="ps-3" style="width:40px;">#</th>
                                <th style="min-width:60px;">Photo</th>
                                <th style="min-width:200px;">Product</th>
                                <th>Category</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end pe-3">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bestSelling as $i => $p)
                            @php
                                $rank   = $i + 1;
                                $rankClass = $rank === 1 ? 'text-warning' : ($rank === 2 ? 'text-secondary' : ($rank === 3 ? '#cd7f32' : ''));
                                $rankIcon  = $rank <= 3 ? 'fas fa-trophy' : 'fas fa-hashtag';
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold text-center">
                                    @if($rank <= 3)
                                        <i class="{{ $rankIcon }}"
                                           style="color: {{ $rank === 1 ? '#ffc107' : ($rank === 2 ? '#adb5bd' : '#cd7f32') }};"></i>
                                    @else
                                        <span class="text-muted small">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->primary_photo)
                                        <img src="{{ asset('storage/' . $p->primary_photo) }}"
                                             alt="{{ $p->name }}"
                                             class="rounded" style="width:42px;height:42px;object-fit:cover;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center text-muted"
                                             style="width:42px;height:42px;background:rgba(255,255,255,.06);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('product.edit', $p->id) }}"
                                       class="dm-link fw-semibold text-decoration-none"
                                       style="font-size:13px;">
                                        {{ $p->name }}
                                    </a>
                                    @if(!$p->status)
                                        <span class="badge bg-secondary ms-1" style="font-size:10px;">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $p->category_name ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary" style="font-size:12px;">{{ number_format($p->order_count) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-success" style="font-size:14px;">{{ number_format($p->qty_sold) }}</span>
                                    <div class="text-muted" style="font-size:10px;">units</div>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    ৳{{ number_format($p->revenue, 0) }}
                                </td>
                                <td class="text-center">
                                    @if($p->stock == 0)
                                        <span class="badge bg-danger" style="font-size:11px;">Out</span>
                                    @elseif($p->stock <= 2)
                                        <span class="badge bg-warning text-dark" style="font-size:11px;">{{ $p->stock }}</span>
                                    @else
                                        <span class="badge bg-success" style="font-size:11px;">{{ $p->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-muted small">
                                    @php $displayPrice = $p->sale_price > 0 ? $p->sale_price : $p->price; @endphp
                                    ৳{{ number_format($displayPrice, 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="border-top:2px solid var(--border-strong);">
                                <td colspan="4" class="ps-3 fw-bold text-uppercase small" style="letter-spacing:.05em;">Total</td>
                                <td class="text-center fw-bold text-primary">
                                    —
                                </td>
                                <td class="text-center fw-bold text-success" style="font-size:14px;">
                                    {{ number_format($totalQtySold) }}
                                </td>
                                <td class="text-end fw-bold text-success">৳{{ number_format($totalRevenue, 0) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Never Sold Tab ══ --}}
    <div id="neverSold" class="perf-tab-pane d-none">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
            <div class="card-body p-0">
                @if($neverSold->isEmpty())
                    <div class="text-center py-5 text-success">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                        Every product has been sold at least once!
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="thead-dm">
                            <tr>
                                <th class="ps-3" style="width:40px;">#</th>
                                <th style="min-width:60px;">Photo</th>
                                <th style="min-width:200px;">Product</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($neverSold as $i => $p)
                            <tr>
                                <td class="ps-3 text-muted small text-center">{{ $i + 1 }}</td>
                                <td>
                                    @if($p->primary_photo)
                                        <img src="{{ asset('storage/' . $p->primary_photo) }}"
                                             alt="{{ $p->name }}"
                                             class="rounded" style="width:42px;height:42px;object-fit:cover;opacity:.75;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center text-muted"
                                             style="width:42px;height:42px;background:rgba(255,255,255,.06);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('product.edit', $p->id) }}"
                                       class="dm-link fw-semibold text-decoration-none"
                                       style="font-size:13px;">
                                        {{ $p->name }}
                                    </a>
                                </td>
                                <td class="text-muted small">{{ $p->category_name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($p->stock == 0)
                                        <span class="badge bg-danger" style="font-size:11px;">Out</span>
                                    @elseif($p->stock <= 2)
                                        <span class="badge bg-warning text-dark" style="font-size:11px;">{{ $p->stock }}</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:11px;">{{ $p->stock }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($p->status)
                                        <span class="badge bg-success" style="font-size:11px;">Active</span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:11px;">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-muted small">
                                    @php $displayPrice = isset($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->price; @endphp
                                    ৳{{ number_format($displayPrice, 0) }}
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

@push('styles')
<style>
    .nav-tabs {
        border-bottom: none;
    }
    .nav-tabs .nav-link {
        color: var(--theme-link-color);
        background: rgba(255,255,255,.03);
        border: 1px solid var(--border-subtle);
        border-bottom: none;
        margin-right: 4px;
        border-radius: 6px 6px 0 0;
    }
    .nav-tabs .nav-link.active {
        color: var(--theme-text-color);
        background: rgba(2,123,80,.18);
        border-color: rgba(2,123,80,.5);
        border-bottom-color: transparent;
    }
    .perf-tab-pane > .card {
        border-radius: 0 6px 6px 6px;
        border: 1px solid var(--border-subtle) !important;
    }

    @media print {
        .sb-sidenav-toggler, .sb-topnav, nav.sb-sidenav, .btn, form, .nav-tabs { display: none !important; }
        #layoutSidenav_content { margin-left: 0 !important; }
        #neverSold { display: block !important; }
        body { background: #fff !important; color: #000 !important; }
        .card { border: 1px solid #ccc !important; background: #fff !important; color: #000 !important; }
        table th, table td { color: #000 !important; }
        .badge { border: 1px solid #999 !important; color: #000 !important; background: #eee !important; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    // Period filter
    const periodInput = document.getElementById('periodInput');
    const customRange = document.getElementById('customRange');
    const form        = document.getElementById('reportForm');

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const val = this.dataset.period;
            document.querySelectorAll('.period-btn').forEach(b =>
                b.classList.replace('btn-primary', 'btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary', 'btn-primary');
            periodInput.value = val;
            customRange.classList.toggle('d-flex', val === 'custom');
            customRange.classList.toggle('d-none', val !== 'custom');
            if (val !== 'custom') form.submit();
        });
    });

    // Tab switching
    document.querySelectorAll('.perf-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.perf-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.perf-tab-pane').forEach(p => p.classList.add('d-none'));
            this.classList.add('active');
            document.getElementById(this.dataset.target).classList.remove('d-none');
        });
    });
})();
</script>
@endpush
