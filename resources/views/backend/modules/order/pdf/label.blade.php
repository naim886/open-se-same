<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Label {{ $order->order_number }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; margin: 0; padding: 8px; }
    table { width: 100%; border-collapse: collapse; }
    .muted { color: #555; }
    .center { text-align: center; }
    .right { text-align: right; }
    .rule { border-top: 1px solid #000; margin: 6px 0; }
    .rule-dashed { border-top: 1px dashed #666; margin: 6px 0; }
    .cod-box { border: 2px solid #000; padding: 6px; text-align: center; }
    .cod-amount { font-size: 20px; font-weight: bold; }
    .item-row td { padding: 1px 0; }
    .section-title {
        font-size: 8px; text-transform: uppercase; letter-spacing: .5px; color: #555;
    }
</style>
</head>
<body>

{{-- ── From / order ── --}}
<table>
    <tr>
        <td>
            <div style="font-size: 12px;"><strong>{{ $company['name'] }}</strong></div>
            <div class="muted">
                @if($company['phone']){{ $company['phone'] }}@endif
            </div>
        </td>
        <td class="right">
            <div class="section-title">Order</div>
            <div style="font-size: 13px;"><strong>{{ $order->order_number }}</strong></div>
        </td>
    </tr>
</table>

<div class="rule"></div>

{{-- ── Collect amount ── --}}
<div class="cod-box">
    <div class="section-title" style="color: #000;">Collect on delivery</div>
    <div class="cod-amount">Tk {{ number_format((float) $order->total_amount, 0) }}</div>
    @if((float) $order->paid_amount > 0)
        <div class="muted" style="font-size: 9px;">
            Already paid: Tk {{ number_format((float) $order->paid_amount, 0) }}
        </div>
    @endif
</div>

<div class="rule"></div>

{{-- ── Deliver to ── --}}
<div class="section-title">Deliver to</div>
<div style="font-size: 14px;"><strong>{{ $order->customer_name }}</strong></div>
<div style="font-size: 13px; margin: 2px 0;"><strong>{{ $order->customer_phone }}</strong></div>
<div style="font-size: 11px;">{!! nl2br(e($order->customer_address)) !!}</div>

<div class="rule-dashed"></div>

{{-- ── Delivery meta ── --}}
<table>
    <tr>
        <td>
            <span class="section-title">Area</span><br>
            {{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }}
        </td>
        <td>
            <span class="section-title">Payment</span><br>
            {{ $order->getPaymentMethodText() }}
        </td>
        <td class="right">
            <span class="section-title">Date</span><br>
            {{ $order->order_date?->format('d M Y') }}
        </td>
    </tr>
</table>

@if($order->consignment_id)
    <div class="rule-dashed"></div>
    <div>
        <span class="section-title">Consignment</span>
        <strong style="font-size: 11px;">{{ $order->consignment_id }}</strong>
    </div>
@endif

<div class="rule-dashed"></div>

{{-- ── Parcel contents ── --}}
@php $totalQty = $order->orderItems->sum('quantity'); @endphp
<div class="section-title">
    Parcel contents ({{ $totalQty }} item{{ $totalQty === 1 ? '' : 's' }})
</div>
<table>
    @foreach($order->orderItems as $item)
        <tr class="item-row">
            <td>
                {{ \Illuminate\Support\Str::limit($item->product_name ?? 'Product #' . $item->product_id, 28) }}
                @if($item->variation_label)<span class="muted">({{ $item->variation_label }})</span>@endif
            </td>
            <td class="right" style="width: 34px;">x{{ $item->quantity }}</td>
        </tr>
    @endforeach
</table>

<div class="rule"></div>
<div class="center muted" style="font-size: 8px;">
    {{ $company['footer'] }}
</div>

</body>
</html>
