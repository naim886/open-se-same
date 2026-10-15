<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 26px; }
    h1 { font-size: 19px; margin: 0 0 3px 0; }
    .muted { color: #6b7280; }
    .right { text-align: right; }
    .center { text-align: center; }
    table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: top; }
    .section-title {
        font-size: 9px; text-transform: uppercase; letter-spacing: .6px;
        color: #6b7280; margin: 0 0 4px 0;
    }
    .panel { border: 1px solid #d7dbe0; padding: 8px; }
    .items th {
        background: #f2f4f6; border: 1px solid #ccd2d8; padding: 6px;
        font-size: 9px; text-transform: uppercase; letter-spacing: .4px; text-align: left;
    }
    .items td { border: 1px solid #dde1e6; padding: 6px; }
    .totals td { padding: 4px 8px; }
    .totals .label { text-align: right; color: #4b5563; }
    .totals .value { text-align: right; width: 130px; }
    .grand td { border-top: 2px solid #222; font-size: 13px; font-weight: bold; }
    .stamp { display: inline-block; border: 1px solid #6b7280; padding: 2px 8px; font-size: 9px; }
</style>
</head>
<body>

{{-- ── Header ── --}}
<table class="header">
    <tr>
        <td style="width: 58%;">
            <h1>{{ $company['name'] }}</h1>
            @if($company['address'])
                <div class="muted">{!! nl2br(e($company['address'])) !!}</div>
            @endif
            <div class="muted" style="margin-top: 3px;">
                @if($company['phone'])Phone: {{ $company['phone'] }}@endif
                @if($company['email']) &nbsp;|&nbsp; Email: {{ $company['email'] }}@endif
            </div>
        </td>
        <td style="width: 42%;" class="right">
            <h1>INVOICE</h1>
            <div><strong>{{ $order->order_number }}</strong></div>
            <div class="muted">{{ $order->order_date?->format('d M Y, h:i A') }}</div>
            <div style="margin-top: 6px;">
                <span class="stamp">{{ \App\Models\Order::STATUS_LIST[$order->status] ?? '—' }}</span>
                <span class="stamp">{{ $order->getPaymentStatusText() }}</span>
            </div>
        </td>
    </tr>
</table>

<div style="border-top: 2px solid #222; margin: 14px 0 16px 0;"></div>

{{-- ── Customer + order meta ── --}}
<table class="header">
    <tr>
        <td style="width: 58%; padding-right: 12px;">
            <p class="section-title">Billed To</p>
            <div class="panel">
                <div style="font-size: 12px;"><strong>{{ $order->customer_name }}</strong></div>
                <div>{{ $order->customer_phone }}</div>
                @if($order->customer_email)<div class="muted">{{ $order->customer_email }}</div>@endif
                <div style="margin-top: 4px;">{!! nl2br(e($order->customer_address)) !!}</div>
            </div>
        </td>
        <td style="width: 42%;">
            <p class="section-title">Order Details</p>
            <div class="panel">
                <table>
                    <tr>
                        <td class="muted">Payment method</td>
                        <td class="right">{{ $order->getPaymentMethodText() }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Delivery area</td>
                        <td class="right">{{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }}</td>
                    </tr>
                    @if($order->consignment_id)
                    <tr>
                        <td class="muted">Consignment</td>
                        <td class="right">{{ $order->consignment_id }}</td>
                    </tr>
                    @endif
                    @if($order->source)
                    <tr>
                        <td class="muted">Source</td>
                        <td class="right">{{ ucfirst($order->source) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- ── Items ── --}}
<p class="section-title" style="margin-top: 18px;">Items</p>
<table class="items">
    <thead>
        <tr>
            <th style="width: 34px;">#</th>
            <th>Description</th>
            <th style="width: 84px;" class="right">Unit Price</th>
            <th style="width: 46px;" class="center">Qty</th>
            <th style="width: 96px;" class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
    @foreach($order->orderItems as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>
                {{ $item->product_name ?? 'Product #' . $item->product_id }}
                @if($item->variation_label)
                    <span class="muted">— {{ $item->variation_label }}</span>
                @endif
            </td>
            <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
            <td class="center">{{ $item->quantity }}</td>
            <td class="right">{{ number_format((float) $item->subtotal, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ── Totals ── --}}
<table style="margin-top: 10px;">
    <tr>
        <td style="width: 58%; vertical-align: top;">
            @if($company['terms'])
                <p class="section-title">Terms</p>
                <div class="muted" style="font-size: 10px;">{!! nl2br(e($company['terms'])) !!}</div>
            @endif
            @if($order->notes)
                <p class="section-title" style="margin-top: 10px;">Order Note</p>
                <div class="muted" style="font-size: 10px;">{!! nl2br(e($order->notes)) !!}</div>
            @endif
        </td>
        <td style="width: 42%;">
            <table class="totals">
                <tr>
                    <td class="label">Products total</td>
                    <td class="value">Tk {{ number_format((float) $order->product_price, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Delivery charge</td>
                    <td class="value">Tk {{ number_format((float) $order->delivery_charge, 2) }}</td>
                </tr>
                @if((float) $order->discount_amount > 0)
                <tr>
                    <td class="label">
                        Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}
                    </td>
                    <td class="value">- Tk {{ number_format((float) $order->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand">
                    <td class="label">Grand total</td>
                    <td class="value">Tk {{ number_format((float) $order->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Paid</td>
                    <td class="value">Tk {{ number_format((float) $order->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Due</td>
                    <td class="value">
                        Tk {{ number_format(max(0, (float) $order->total_amount - (float) $order->paid_amount), 2) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ── Footer ── --}}
<div style="border-top: 1px solid #d7dbe0; margin-top: 22px; padding-top: 8px;" class="muted">
    <div>{{ $company['footer'] }}</div>
    <div style="margin-top: 2px; font-size: 10px;">
        Generated on {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp; {{ $order->order_number }}
    </div>
</div>

</body>
</html>
