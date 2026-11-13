<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice &amp; Packing Slip - {{ $order->order_number }}</title>
<style>
    @page {
        size: A4;
        margin: 0;
    }

    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        padding: 0;
    }

    body {
        background: #e9ecef;
        color: #111;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        line-height: 1.25;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .print-toolbar {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 10px 16px;
        background: #111;
        color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
    }

    .print-toolbar button,
    .print-toolbar a {
        display: inline-block;
        padding: 7px 14px;
        border: 0;
        border-radius: 5px;
        background: #027B50;
        color: #fff;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .print-toolbar a {
        background: #3a3a3a;
    }

    .print-toolbar span {
        font-size: 12px;
        color: #cfcfcf;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        margin: 10mm auto;
        padding: 10mm 16mm 2mm;
        background: #fff;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .14);
    }

    .doc-header {
        flex: 0 0 auto;
    }

    .brand-row {
        display: flex;
        align-items: flex-start;
        gap: 12.8pt;
        margin-left: -3pt;
    }

    .brand-logo {
        display: block;
        width: 50mm;
        height: auto;
    }

    .brand-domain {
        margin-top: 11.5pt;
        color: #0b7f4d;
        font-size: 10pt;
        font-weight: 400;
        line-height: 1;
        letter-spacing: .2px;
    }

    .brand-rule {
        margin-top: 6.7pt;
        margin-left: -3pt;
        margin-right: -3pt;
        border-top: .8pt solid #0b7f4d;
    }

    .doc-title {
        margin: 19.5pt 0 12pt;
        font-size: 14pt;
        font-weight: 700;
        line-height: 1.1;
    }

    .doc-title.packing-title {
        margin-top: 34.7pt;
        margin-bottom: 11.1pt;
    }

    table.meta {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 31.9pt;
    }

    table.meta.packing-meta {
        margin-bottom: 27.9pt;
    }

    table.meta td {
        padding: 0 0 0 3pt;
        vertical-align: top;
        font-size: 8pt;
        line-height: 1.25;
    }

    table.meta.packing-meta td {
        font-size: 11pt;
        line-height: 1.27;
    }

    table.meta td.meta-label {
        width: 126pt;
    }

    table.parties {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    table.parties td {
        vertical-align: top;
        padding: 0;
    }

    table.parties td.seller {
        width: 50%;
        padding-right: 4mm;
    }

    table.parties td.buyer {
        width: 50%;
        padding-left: 0;
    }

    table.parties td.buyer p {
        padding-left: 10pt;
    }

    .party-heading {
        margin: 0 0 1.2pt;
        font-size: 8pt;
        font-weight: 700;
        line-height: 1.1;
    }

    table.parties p {
        margin: 0;
        font-size: 8pt;
        line-height: 1.25;
        word-wrap: break-word;
    }

    table.parties p.party-address {
        margin-bottom: 1.8pt;
    }

    table.parties p.buyer-contact {
        margin-top: -1.8pt;
    }

    .bill-heading {
        margin: 20.2pt 0 15.2pt;
        text-align: center;
        font-size: 10pt;
        font-weight: 700;
        line-height: 1.1;
        text-decoration: underline;
        text-underline-offset: 1.5pt;
        letter-spacing: .2px;
    }

    table.items {
        width: 100%;
        border-collapse: collapse;
    }

    table.items th,
    table.items td {
        border: .75pt solid #efefef;
        padding: 4pt 3.5pt;
        vertical-align: top;
    }

    table.items th {
        font-size: 8pt;
        font-weight: 700;
        line-height: 1.25;
        text-align: left;
    }

    table.items td {
        font-size: 8pt;
        line-height: 1.25;
    }

    table.items .col-no { width: 5%; }
    table.items .col-product { width: 60%; }
    table.items .col-price { width: 15%; }
    table.items .col-qty { width: 5%; }
    table.items .col-total { width: 15%; }

    table.items.packing-items .col-no { width: 5%; }
    table.items.packing-items .col-product { width: 80%; }
    table.items.packing-items .col-qty { width: 15%; }

    table.items.invoice-items td:last-child,
    table.items.invoice-items th:last-child {
        text-align: right;
    }

    table.items.packing-items th,
    table.items.packing-items td {
        padding-left: 5.3pt;
        padding-right: 5.3pt;
    }

    .item-sku {
        padding-left: 2.3pt;
    }

    table.totals {
        width: 100%;
        border-collapse: collapse;
        font-family: "Gill Sans MT", "Gill Sans", Calibri, Arial, sans-serif;
    }

    table.totals td {
        border: .75pt solid #efefef;
        padding: 4.15pt 4pt;
        font-size: 8pt;
        line-height: 1.25;
    }

    table.totals td.totals-label {
        width: 85%;
        text-align: right;
    }

    table.totals td.totals-value {
        width: 15%;
        text-align: right;
        white-space: nowrap;
    }

    .included {
        font-weight: 700;
    }

    .payment-method {
        margin: 33.2pt 0 0 26.5pt;
        font-size: 8pt;
        line-height: 1.25;
    }

    .section-heading {
        margin: 0 0 10.3pt;
        font-size: 12pt;
        font-weight: 700;
        line-height: 1.1;
    }

    .section-heading.items-heading {
        margin-top: 26.8pt;
        margin-bottom: 15.2pt;
    }

    .address-block {
        margin: 0;
    }

    .address-block p {
        margin: 0;
        font-size: 11pt;
        line-height: 1.27;
    }

    .address-block p.contact {
        font-weight: 700;
    }

    .doc-footer {
        margin-top: auto;
        padding-top: 3.7pt;
        border-top: .8pt solid #cfcfcf;
        text-align: center;
        color: #222;
        font-size: 7pt;
        line-height: 1.45;
    }

    .doc-footer .vat-note {
        margin-bottom: 1.2pt;
        font-size: 7.5pt;
    }

    @media print {
        body {
            background: #fff;
        }

        .print-toolbar {
            display: none !important;
        }

        .page {
            width: 210mm;
            height: 297mm;
            min-height: 0;
            margin: 0;
            padding: 10mm 16mm 2mm;
            box-shadow: none;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }
    }
</style>
</head>
<body>

@php
    $items = $order->orderItems;

    $productTotal = (float) $order->product_price;
    if ($productTotal <= 0) {
        $productTotal = (float) $items->sum('subtotal');
    }

    $shipping    = (float) $order->delivery_charge;
    $discount    = (float) $order->discount_amount;
    $subtotal    = $productTotal + $shipping - $discount;
    $vatIncluded = round($productTotal * 0.075, 2);
    $orderTotal  = (float) $order->total_amount;

    $gatewayFee = ((int) $order->payment_method === \App\Models\Order::PAYMENT_METHOD_ONLINE)
        ? round($subtotal * 0.025, 2)
        : null;

    $orderDate    = $order->order_date ?? $order->created_at;
    $deliveryArea = \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—';

    $paymentText = ((int) $order->payment_method === \App\Models\Order::PAYMENT_METHOD_COD)
        ? 'Cash on delivery'
        : $order->getPaymentMethodText();

    $itemName = function ($item) {
        $name = trim((string) ($item->product_name ?: ('Product #' . $item->product_id)));
        $variation = trim((string) ($item->variation_label ?? ''));

        if ($variation !== '') {
            $variation = preg_replace('/^\s*(variation|variant|color|colour|size)\s*[:\-]\s*/i', '', $variation) ?? $variation;
            $variation = trim($variation);

            if ($variation !== '' && ! str_contains(mb_strtolower($name), mb_strtolower($variation))) {
                $name .= ' - ' . $variation;
            }
        }

        return $name;
    };

    $itemSku = function ($item) {
        return trim((string) optional($item->product)->sku);
    };
@endphp

<div class="print-toolbar">
    <button type="button" onclick="window.print()">Print Invoice &amp; Packing Slip</button>
    <a href="{{ route('orders.show', $order->id) }}">Back to order</a>
    <span>Use A4 paper and turn off browser headers/footers for the exact layout.</span>
</div>

{{-- ══════════════════════════ INVOICE ══════════════════════════ --}}
<section class="page">
    <header class="doc-header">
        <div class="brand-row">
            <img class="brand-logo" src="{{ asset('images/logo-web.png') }}" alt="{{ $company['name'] }}">
            <span class="brand-domain">{{ $company['website'] }}</span>
        </div>
        <div class="brand-rule"></div>
    </header>

    <h1 class="doc-title">Invoice</h1>

    <table class="meta invoice-meta">
        <tr>
            <td class="meta-label">Invoice No.</td>
            <td>{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label">Invoice Date</td>
            <td>{{ $orderDate?->format('F j, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Order No.</td>
            <td>{{ $order->order_number }}</td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td class="seller">
                <div class="party-heading">Seller</div>
                <p>{{ $company['name'] }}</p>
                <p class="party-address">{!! nl2br(e($company['address'])) !!}</p>
                <p>{{ $company['phone'] }}</p>
                <p>{{ $company['email'] }}</p>
                <p>BIN: {{ $company['bin'] }}</p>
                <p>{{ $company['mushak'] }}</p>
            </td>
            <td class="buyer">
                <div class="party-heading">Buyer</div>
                <p>{{ $order->customer_name }}</p>
                <p class="party-address">{!! nl2br(e($order->customer_address)) !!}</p>
                <p>{{ $deliveryArea }}</p>
                <p class="buyer-contact"><strong>{{ $order->customer_phone }}</strong></p>
            </td>
        </tr>
    </table>

    <div class="bill-heading">BILL</div>

    <table class="items invoice-items">
        <thead>
            <tr>
                <th class="col-no"></th>
                <th class="col-product">Product</th>
                <th class="col-price">Price</th>
                <th class="col-qty">Qty</th>
                <th class="col-total">Total (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $itemName($item) }}
                        @if($itemSku($item) !== '')
                            <div class="item-sku">{{ $itemSku($item) }}</div>
                        @endif
                    </td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}TK</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->subtotal, 2) }}TK</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="totals-label"><span class="included">Included</span> VAT (7.5%)</td>
            <td class="totals-value">{{ number_format($vatIncluded, 2) }}TK</td>
        </tr>
        <tr>
            <td class="totals-label">Shipping</td>
            <td class="totals-value">{{ number_format($shipping, 2) }}TK</td>
        </tr>
        @if($discount > 0)
            <tr>
                <td class="totals-label">Discount</td>
                <td class="totals-value">- {{ number_format($discount, 2) }}TK</td>
            </tr>
        @endif
        <tr>
            <td class="totals-label">Subtotal including shipping Payment</td>
            <td class="totals-value">{{ number_format($subtotal, 2) }}TK</td>
        </tr>
        <tr>
            <td class="totals-label">Gateway Fee (2.5%)</td>
            <td class="totals-value">{{ $gatewayFee !== null ? number_format($gatewayFee, 2) . 'TK' : '' }}</td>
        </tr>
        <tr>
            <td class="totals-label">Order Total</td>
            <td class="totals-value">{{ number_format($orderTotal, 2) }}TK</td>
        </tr>
    </table>

    <div class="payment-method">Payment method: {{ $paymentText }}</div>

    <footer class="doc-footer">
        <div class="vat-note">{{ $company['vat_note'] }}</div>
        <div>{{ $company['address'] }}, {{ $company['phone'] }}</div>
        <div>Email: {{ $company['email'] }}, website: {{ $company['website'] }}</div>
    </footer>
</section>

{{-- ══════════════════════════ PACKING SLIP ══════════════════════════ --}}
<section class="page">
    <header class="doc-header">
        <div class="brand-row">
            <img class="brand-logo" src="{{ asset('images/logo-web.png') }}" alt="{{ $company['name'] }}">
            <span class="brand-domain">{{ $company['website'] }}</span>
        </div>
        <div class="brand-rule"></div>
    </header>

    <h1 class="doc-title packing-title">Packing Slip</h1>

    <table class="meta packing-meta">
        <tr>
            <td class="meta-label">Shipping method</td>
            <td>{{ $deliveryArea }}</td>
        </tr>
        <tr>
            <td class="meta-label">Invoice No.</td>
            <td>{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label">Order No.</td>
            <td>{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label">Order Date</td>
            <td>{{ $orderDate?->format('F j, Y') }}</td>
        </tr>
    </table>

    <h2 class="section-heading">Shipping address</h2>

    <div class="address-block">
        <p>{{ $order->customer_name }}</p>
        <p>{!! nl2br(e($order->customer_address)) !!}</p>
        <p>{{ $deliveryArea }}</p>
        <p class="contact">{{ $order->customer_phone }}</p>
    </div>

    <h2 class="section-heading items-heading">Items</h2>

    <table class="items packing-items">
        <thead>
            <tr>
                <th class="col-no"></th>
                <th class="col-product">Item</th>
                <th class="col-qty" style="text-align:right;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $itemName($item) }}
                        @if($itemSku($item) !== '')
                            <div class="item-sku">{{ $itemSku($item) }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">{{ $item->quantity }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer class="doc-footer">
        <div class="vat-note">{{ $company['vat_note'] }}</div>
        <div>{{ $company['address'] }}, {{ $company['phone'] }}</div>
        <div>Email: {{ $company['email'] }}, website: {{ $company['website'] }}</div>
    </footer>
</section>

<script>
    window.addEventListener('load', function () {
        window.setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
</body>
</html>
