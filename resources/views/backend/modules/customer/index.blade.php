@extends('backend.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid my-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Customers</h4>
            <div class="small text-muted mt-1">
                {{ number_format($totalCustomers) }} unique customers &nbsp;·&nbsp;
                {{ number_format($totalOrders) }} total orders &nbsp;·&nbsp;
                {{ number_format($repeatCustomers) }} repeat buyers
                ({{ number_format($repeatRate, 1) }}%)
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.tags.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-tags me-1"></i>Manage Tags
            </a>
            <a href="{{ route('customers.export', request()->query()) }}"
               class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Total Customers</div>
                <div class="fw-bold fs-2 text-primary">{{ number_format($totalCustomers) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Total Orders</div>
                <div class="fw-bold fs-2 text-success">{{ number_format($totalOrders) }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-0 shadow-sm text-center p-3"
                 style="border:1.5px solid rgba(32,201,151,.3) !important; background:rgba(32,201,151,.05)!important;">
                <div class="text-muted small mb-1">Repeat Buyers</div>
                <div class="fw-bold fs-2 text-success" style="color:#20c997!important;">{{ number_format($repeatCustomers) }}</div>
                <div class="small text-muted">
                    {{ number_format($repeatRate, 1) }}% of customers &nbsp;·&nbsp;
                    {{ $repeatThreshold }}+ orders
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('customers.index') }}"
                  class="d-flex flex-wrap align-items-end gap-2">

                {{-- Search --}}
                <div style="min-width:220px; flex:1;">
                    <label class="form-label mb-1 small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Name, email or phone…"
                           value="{{ request('search') }}">
                </div>

                {{-- Source filter --}}
                <div>
                    <label class="form-label mb-1 small">Source</label>
                    <select name="source" class="form-select form-select-sm" style="min-width:140px;">
                        <option value="">All Sources</option>
                        @foreach($sourceList as $key => $label)
                            <option value="{{ $key }}" {{ request('source') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="form-label mb-1 small">Sort By</label>
                    <select name="sort" class="form-select form-select-sm" style="min-width:160px;">
                        <option value="last_order"   {{ request('sort','last_order') === 'last_order'   ? 'selected':'' }}>Most Recent Order</option>
                        <option value="most_orders"  {{ request('sort') === 'most_orders'  ? 'selected':'' }}>Most Orders</option>
                        <option value="most_spent"   {{ request('sort') === 'most_spent'   ? 'selected':'' }}>Most Spent</option>
                        <option value="most_products"{{ request('sort') === 'most_products'? 'selected':'' }}>Most Products Ordered</option>
                        <option value="first_order"  {{ request('sort') === 'first_order'  ? 'selected':'' }}>Oldest Customer</option>
                    </select>
                </div>

                {{-- Tag filter --}}
                <div>
                    <label class="form-label mb-1 small">Tag</label>
                    <select name="tag" class="form-select form-select-sm" style="min-width:150px;">
                        <option value="">All Tags</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag->id }}" {{ (int) request('tag') === $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Repeat filter --}}
                <div>
                    <label class="form-label mb-1 small d-block">Buyer Type</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="repeatFilter" name="repeat" value="1" @checked($repeat)>
                        <label class="form-check-label small" for="repeatFilter">Repeat only</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                @if(request()->hasAny(['search','source','sort','tag','repeat']))
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($customers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-users fa-2x mb-2 d-block"></i>No customers found.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3" style="width:40px;">#</th>
                            <th style="min-width:180px;">Customer</th>
                            <th>Source</th>
                            <th class="text-center">First Order</th>
                            <th class="text-center">Orders</th>
                            <th class="text-center">Products Ordered</th>
                            <th class="text-end">Total Spent</th>
                            <th class="text-center">Last Order</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $i => $customer)
                        <tr>
                            <td class="ps-3 text-muted small text-center">
                                {{ ($customers->currentPage() - 1) * $customers->perPage() + $i + 1 }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                         style="width:34px;height:34px;background:{{ '#' . substr(md5($customer->customer_phone),0,6) }};font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($customer->customer_name ?? '?',0,1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold d-flex align-items-center gap-1 flex-wrap" style="font-size:13px;">
                                            <span>{{ $customer->customer_name }}</span>
                                            @if(($customer->order_count ?? 0) >= $repeatThreshold)
                                                <span class="badge bg-success" style="font-size:9px;"
                                                      title="{{ $repeatThreshold }}+ orders">REPEAT</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size:11px;">{{ $customer->customer_phone }}</div>
                                        @if($customer->customer_email)
                                            <div class="text-muted" style="font-size:10px;">{{ $customer->customer_email }}</div>
                                        @endif
                                        @php $rowTags = $customerTags[$customer->customer_phone] ?? []; @endphp
                                        @if(!empty($rowTags))
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($rowTags as $rowTag)
                                                    <span class="badge"
                                                          style="background:{{ $rowTag->color }};font-size:9px;padding:2px 6px;">
                                                        {{ $rowTag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php $srcKey = $customer->source ?? 'website'; @endphp
                                <span class="badge"
                                      style="background:{{ $sourceColors[$srcKey] ?? '#6c757d' }};font-size:11px;">
                                    {{ $sourceList[$srcKey] ?? 'Website' }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ \Carbon\Carbon::parse($customer->first_order_at)->format('d M Y') }}
                                <div style="font-size:10px;">{{ \Carbon\Carbon::parse($customer->first_order_at)->diffForHumans() }}</div>
                            </td>
                            <td class="text-center">
                                @if($customer->order_count > 0)
                                    <span class="badge bg-primary" style="font-size:12px;">{{ $customer->order_count }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->total_products_ordered > 0)
                                    <span class="fw-semibold text-success">{{ number_format($customer->total_products_ordered) }}</span>
                                    <span class="text-muted small"> units</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold {{ $customer->total_spent > 0 ? 'text-success' : 'text-muted' }}">
                                @if($customer->total_spent > 0)
                                    ৳{{ number_format($customer->total_spent, 0) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center text-muted small">
                                @if($customer->last_order_at)
                                    {{ \Carbon\Carbon::parse($customer->last_order_at)->format('d M Y') }}
                                    <div style="font-size:10px;">{{ \Carbon\Carbon::parse($customer->last_order_at)->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('customers.show', ['phone' => $customer->customer_phone]) }}"
                                   class="btn btn-xs btn-outline-primary"
                                   style="font-size:11px;padding:2px 8px;">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($customers->hasPages())
            <div class="px-3 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}
                </div>
                <div>{{ $customers->links() }}</div>
            </div>
            @endif
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        .sb-sidenav-toggler, .sb-topnav, nav.sb-sidenav, .btn, form, .card:first-of-type { display: none!important; }
        #layoutSidenav_content { margin-left: 0!important; }
        body { background:#fff!important; color:#000!important; }
        table th, table td { color:#000!important; }
    }
</style>
@endpush
