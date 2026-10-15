@extends('backend.layouts.app')

@section('title', $customer->customer_name . ' – Customer Detail')

@section('content')
@php use App\Models\Order; @endphp
<div class="container-fluid my-4">

    {{-- ── Back + Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <div>
                <h4 class="mb-0 fw-bold">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white me-2"
                         style="width:36px;height:36px;background:{{ '#' . substr(md5($customer->customer_phone),0,6) }};font-size:15px;vertical-align:middle;">
                        {{ strtoupper(substr($customer->customer_name ?? '?',0,1)) }}
                    </div>
                    {{ $customer->customer_name }}
                </h4>
                <div class="small text-muted mt-1">
                    <i class="fas fa-phone me-1"></i>{{ $customer->customer_phone }}
                    @if($customer->customer_email)
                        &nbsp;·&nbsp; <i class="fas fa-envelope me-1"></i>{{ $customer->customer_email }}
                    @endif
                </div>
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Stats Row ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <div class="text-muted small mb-1">Total Orders</div>
                <div class="fw-bold fs-2 text-primary">{{ $stats->order_count }}</div>
                @if($stats->order_count > 1)
                    <div class="small" style="color:#20c997;">Repeat buyer</div>
                @endif
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <div class="text-muted small mb-1">Products Ordered</div>
                <div class="fw-bold fs-2 text-info">{{ number_format($stats->total_products) }}</div>
                <div class="small text-muted">units</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100"
                 style="border:1.5px solid rgba(25,135,84,.3)!important;background:rgba(25,135,84,.05)!important;">
                <div class="text-muted small mb-1">Total Spent</div>
                <div class="fw-bold fs-2 text-success">৳{{ number_format($stats->total_spent, 0) }}</div>
                <div class="small text-muted">৳{{ number_format($stats->total_paid, 0) }} paid</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <div class="text-muted small mb-1">Delivery Stats</div>
                <div class="d-flex justify-content-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-success">{{ $stats->delivered_count }} Delivered</span>
                    <span class="badge bg-danger">{{ $stats->cancelled_count }} Cancelled</span>
                    @if($stats->returned_count > 0)
                        <span class="badge bg-dark">{{ $stats->returned_count }} Returned</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- ── Timeline ── --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-3 small text-uppercase"
                         style="letter-spacing:.07em;color:var(--theme-link-color);">
                        <i class="fas fa-history me-1"></i>Customer Timeline
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">First Order</span>
                            <span class="fw-semibold small">
                                @if($stats->first_order_at)
                                    {{ \Carbon\Carbon::parse($stats->first_order_at)->format('d M Y, h:i A') }}
                                    <span class="text-muted">({{ \Carbon\Carbon::parse($stats->first_order_at)->diffForHumans() }})</span>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Last Order</span>
                            <span class="fw-semibold small">
                                @if($stats->last_order_at)
                                    {{ \Carbon\Carbon::parse($stats->last_order_at)->format('d M Y, h:i A') }}
                                    <span class="text-muted">({{ \Carbon\Carbon::parse($stats->last_order_at)->diffForHumans() }})</span>
                                @else —
                                @endif
                            </span>
                        </div>
                        @if($stats->order_count > 1 && $stats->first_order_at && $stats->last_order_at)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Customer Since</span>
                            <span class="fw-semibold small">
                                {{ \Carbon\Carbon::parse($stats->first_order_at)->longAbsoluteDiffForHumans(\Carbon\Carbon::parse($stats->last_order_at)) }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Source Distribution ── --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-3 small text-uppercase"
                         style="letter-spacing:.07em;color:var(--theme-link-color);">
                        <i class="fas fa-bullseye me-1"></i>Order Sources
                    </div>
                    @if($sourceDist->isEmpty())
                        <div class="text-muted small">No source data recorded.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach($sourceDist as $srcKey => $count)
                            @php $total = $stats->order_count ?: 1; $pct = round($count / $total * 100); @endphp
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">
                                        <span class="badge me-1"
                                              style="background:{{ $sourceColors[$srcKey] ?? '#6c757d' }};">
                                            {{ $sourceList[$srcKey] ?? $srcKey }}
                                        </span>
                                    </span>
                                    <span class="small fw-semibold">{{ $count }} order{{ $count>1?'s':'' }} ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height:5px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:{{ $pct }}%;background:{{ $sourceColors[$srcKey] ?? '#6c757d' }};">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── CRM: Tags & Notes ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 py-3" style="background:rgba(255,255,255,.03);">
                    <span class="fw-semibold">
                        <i class="fas fa-tags me-2 text-primary"></i>Tags
                        @if($isRepeat)
                            <span class="badge bg-success ms-1" style="font-size:10px;"
                                  title="{{ $stats->order_count }} orders">REPEAT BUYER</span>
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        @forelse($assignedTags as $tag)
                            <span class="badge d-inline-flex align-items-center gap-1 mb-1"
                                  style="background:{{ $tag->color }};font-size:12px;padding:6px 8px 6px 10px;">
                                {{ $tag->name }}
                                <form action="{{ route('customers.tags.detach', [$customer->customer_phone, $tag->id]) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-close btn-close-white align-middle"
                                            style="font-size:8px;" title="Remove tag" aria-label="Remove tag"></button>
                                </form>
                            </span>
                        @empty
                            <span class="text-muted small">No tags assigned.</span>
                        @endforelse
                    </div>

                    @php $availableTags = $allTags->whereNotIn('id', $assignedTagIds); @endphp
                    @if($availableTags->isNotEmpty())
                        <form action="{{ route('customers.tags.attach', $customer->customer_phone) }}"
                              method="POST" class="d-flex gap-2">
                            @csrf
                            <select name="customer_tag_id" class="form-select form-select-sm">
                                @foreach($availableTags as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                <i class="fas fa-plus me-1"></i>Add
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 py-3" style="background:rgba(255,255,255,.03);">
                    <span class="fw-semibold">
                        <i class="fas fa-note-sticky me-2 text-warning"></i>Internal Notes
                        <span class="badge bg-secondary ms-1">{{ $notes->count() }}</span>
                    </span>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.notes.store', $customer->customer_phone) }}"
                          method="POST" class="mb-3">
                        @csrf
                        <textarea name="note" rows="2" class="form-control form-control-sm"
                                  placeholder="Add an internal note (not visible to the customer)…"
                                  required maxlength="2000">{{ old('note') }}</textarea>
                        <button type="submit" class="btn btn-sm btn-primary mt-2">
                            <i class="fas fa-plus me-1"></i>Add Note
                        </button>
                    </form>

                    @forelse($notes as $note)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div style="font-size:13px; white-space:pre-line;">{{ $note->note }}</div>
                                <form action="{{ route('customers.notes.destroy', [$customer->customer_phone, $note->id]) }}"
                                      method="POST" onsubmit="return confirm('Delete this note?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                            title="Delete note"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                {{ $note->author?->name ?? 'System' }} ·
                                {{ $note->created_at?->format('d M Y, h:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">No notes yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Order History ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header border-0 py-3"
             style="background:rgba(255,255,255,.03);">
            <span class="fw-semibold">
                <i class="fas fa-shopping-bag me-2 text-primary"></i>
                Order History
                <span class="badge bg-primary ms-1">{{ $stats->order_count }}</span>
            </span>
        </div>
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="text-center py-5 text-muted">No orders found.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="thead-dm">
                        <tr>
                            <th class="ps-3">Order #</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Source</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        @php $statusColor = Order::STATUS_COLORS[$order->status] ?? 'secondary'; @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold" style="font-size:13px; font-family:monospace;">
                                    #{{ $order->order_number }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $order->order_date?->format('d M Y') }}
                                <div style="font-size:10px;">{{ $order->order_date?->format('h:i A') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary" style="font-size:11px;">{{ $order->items_count }}</span>
                            </td>
                            <td class="text-end fw-semibold" style="font-size:13px;">
                                ৳{{ number_format($order->total_amount, 0) }}
                            </td>
                            <td class="text-end text-muted small">
                                ৳{{ number_format($order->paid_amount, 0) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColor }}" style="font-size:11px;">
                                    {{ Order::STATUS_LIST[$order->status] ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{-- Inline source update --}}
                                <form method="POST"
                                      action="{{ route('customers.order-source', $order->id) }}"
                                      class="d-inline-flex align-items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="source"
                                            class="form-select form-select-sm"
                                            style="font-size:11px;min-width:110px;padding:2px 4px;"
                                            onchange="this.form.submit()">
                                        @foreach($sourceList as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ ($order->source ?? 'website') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="btn btn-xs btn-outline-primary"
                                   style="font-size:11px;padding:2px 8px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--border-strong);">
                            <td class="ps-3 fw-bold text-uppercase small" style="letter-spacing:.05em;" colspan="3">Total</td>
                            <td class="text-end fw-bold text-success">৳{{ number_format($orders->sum('total_amount'), 0) }}</td>
                            <td class="text-end fw-bold text-muted">৳{{ number_format($orders->sum('paid_amount'), 0) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    @media print {
        .sb-sidenav-toggler, .sb-topnav, nav.sb-sidenav, .btn, form { display: none!important; }
        #layoutSidenav_content { margin-left: 0!important; }
        body { background:#fff!important; color:#000!important; }
        .card { border:1px solid #ccc!important; background:#fff!important; color:#000!important; }
        table th, table td { color:#000!important; }
        .badge { border:1px solid #999!important; color:#000!important; background:#eee!important; }
        select { display:none!important; }
    }
</style>
@endpush
