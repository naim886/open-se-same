@extends('backend.layouts.app')

@section('title', 'Stock Value Report')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-warehouse me-2 text-primary"></i>Stock Value Report</h4>
            <div class="small text-muted mt-1">All active products &nbsp;·&nbsp; {{ number_format($rows->count()) }} products &nbsp;·&nbsp; {{ number_format($totalQty) }} units in stock</div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(13,110,253,.12);">
                        <i class="fas fa-box-open text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Products</div>
                        <div class="fw-bold fs-4 lh-1">{{ $rows->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(25,135,84,.12);">
                        <i class="fas fa-cubes text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Units</div>
                        <div class="fw-bold fs-4 lh-1">{{ number_format($totalQty) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(255,193,7,.12);">
                        <i class="fas fa-tags text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Selling Value</div>
                        <div class="fw-bold fs-4 lh-1">৳{{ number_format($totalValue, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:rgba(220,53,69,.12);">
                        <i class="fas fa-coins text-danger fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cost Value</div>
                        <div class="fw-bold fs-4 lh-1">৳{{ number_format($totalCost, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom fw-semibold py-3 d-flex align-items-center gap-2">
            <i class="fas fa-table text-primary"></i> Stock Value Detail
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle" id="stockTable">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3" style="width:46px">#</th>
                            <th>Product / Variation</th>
                            <th>Category</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Cost Value</th>
                            <th class="text-end pe-3">Selling Value</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $i = 0; @endphp
                    @foreach($rows as $row)
                        @php $i++; @endphp

                        @if($row['is_variant'])
                            {{-- ── Variant product header row ── --}}
                            <tr style="background:rgba(13,110,253,.08);">
                                <td class="ps-3 text-muted small">{{ $i }}</td>
                                <td colspan="2">
                                    <a href="{{ route('product.edit', $row['product']->id) }}" class="text-decoration-none fw-bold">
                                        {{ $row['product']->name }}
                                    </a>
                                    <span class="badge ms-2" style="background:rgba(13,110,253,.25);color:#6ea8fe;font-size:10px;">Variant</span>
                                    <span class="text-muted small ms-1">· {{ $row['variations']->count() }} variations</span>
                                </td>
                                <td class="text-end text-muted small">—</td>
                                <td class="text-end text-muted small">—</td>
                                <td class="text-end fw-semibold">{{ number_format($row['qty']) }}</td>
                                <td class="text-end text-muted">
                                    @if($row['cost_value'] > 0) ৳{{ number_format($row['cost_value'], 0) }}
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 fw-bold {{ $row['value'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $row['value'] > 0 ? '৳'.number_format($row['value'], 0) : '—' }}
                                </td>
                            </tr>
                            {{-- ── Variation sub-rows ── --}}
                            @foreach($row['variations'] as $var)
                            <tr style="{{ $var['qty'] == 0 ? 'opacity:.55;' : '' }}">
                                <td class="ps-3"></td>
                                <td class="ps-4">
                                    <span class="text-muted me-1" style="font-size:11px;">↳</span>
                                    <span class="small">{{ $var['label'] }}</span>
                                    @if($var['qty'] == 0)
                                        <span class="badge bg-danger ms-1" style="font-size:10px;">Out of stock</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $row['product']->category->name ?? '—' }}</td>
                                <td class="text-end small">
                                    @if($var['cost_price'] > 0) ৳{{ number_format($var['cost_price'], 0) }}
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold small">৳{{ number_format($var['selling_price'], 0) }}</td>
                                <td class="text-end small {{ $var['qty'] == 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($var['qty']) }}</td>
                                <td class="text-end small text-muted">
                                    @if($var['cost_value'] > 0) ৳{{ number_format($var['cost_value'], 0) }}
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 small fw-semibold {{ $var['value'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $var['value'] > 0 ? '৳'.number_format($var['value'], 0) : '—' }}
                                </td>
                            </tr>
                            @endforeach

                        @else
                            {{-- ── Simple product row ── --}}
                            <tr style="{{ $row['qty'] == 0 ? 'opacity:.55;' : '' }}">
                                <td class="ps-3 text-muted small">{{ $i }}</td>
                                <td>
                                    <a href="{{ route('product.edit', $row['product']->id) }}" class="text-decoration-none fw-semibold">
                                        {{ $row['product']->name }}
                                    </a>
                                    @if($row['qty'] == 0)
                                        <span class="badge bg-danger ms-1" style="font-size:10px;">Out of stock</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $row['product']->category->name ?? '—' }}</td>
                                <td class="text-end">
                                    @if($row['cost_price'] > 0) ৳{{ number_format($row['cost_price'], 0) }}
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">৳{{ number_format($row['selling_price'], 0) }}</td>
                                <td class="text-end {{ $row['qty'] == 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($row['qty']) }}</td>
                                <td class="text-end text-muted">
                                    @if($row['cost_value'] > 0) ৳{{ number_format($row['cost_value'], 0) }}
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 fw-bold {{ $row['value'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $row['value'] > 0 ? '৳'.number_format($row['value'], 0) : '—' }}
                                </td>
                            </tr>
                        @endif

                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="5" class="ps-3">TOTAL</td>
                            <td class="text-end">{{ number_format($totalQty) }}</td>
                            <td class="text-end">৳{{ number_format($totalCost, 0) }}</td>
                            <td class="text-end pe-3">৳{{ number_format($totalValue, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
