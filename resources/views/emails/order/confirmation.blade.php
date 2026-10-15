@extends('emails.layouts.app')

@section('content')
    <h1 style="font-size: 22px; color: #222222; margin: 0 0 8px;">Thank you, {{ $order->customer_name }}!</h1>

    <p style="margin: 0 0 18px;">
        We have received your order
        <strong>#{{ $order->order_number }}</strong>
        placed on {{ $order->order_date?->format('d M Y, h:i A') }}.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr>
                <th align="left" style="border-bottom: 2px solid #e5e7eb; padding: 6px 0; font-size: 12px; text-transform: uppercase; color: #6b7280;">Item</th>
                <th align="center" style="border-bottom: 2px solid #e5e7eb; padding: 6px 0; font-size: 12px; text-transform: uppercase; color: #6b7280; width: 50px;">Qty</th>
                <th align="right" style="border-bottom: 2px solid #e5e7eb; padding: 6px 0; font-size: 12px; text-transform: uppercase; color: #6b7280; width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td style="border-bottom: 1px solid #f3f4f6; padding: 8px 0;">
                        {{ $item->product_name ?? 'Product #' . $item->product_id }}
                        @if($item->variation_label)
                            <br><span style="color: #6b7280; font-size: 12px;">{{ $item->variation_label }}</span>
                        @endif
                    </td>
                    <td align="center" style="border-bottom: 1px solid #f3f4f6; padding: 8px 0;">{{ $item->quantity }}</td>
                    <td align="right" style="border-bottom: 1px solid #f3f4f6; padding: 8px 0;">
                        ৳{{ number_format((float) $item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Products total</td>
            <td align="right" style="padding: 3px 0;">৳{{ number_format((float) $order->product_price, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Delivery charge</td>
            <td align="right" style="padding: 3px 0;">৳{{ number_format((float) $order->delivery_charge, 2) }}</td>
        </tr>
        @if((float) $order->discount_amount > 0)
            <tr>
                <td style="padding: 3px 0; color: #6b7280;">
                    Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}
                </td>
                <td align="right" style="padding: 3px 0; color: #047857;">
                    - ৳{{ number_format((float) $order->discount_amount, 2) }}
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding: 8px 0 3px; font-weight: bold; border-top: 1px solid #e5e7eb;">Grand total</td>
            <td align="right" style="padding: 8px 0 3px; font-weight: bold; border-top: 1px solid #e5e7eb;">
                ৳{{ number_format((float) $order->total_amount, 2) }}
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 4px;"><strong>Delivery address</strong></p>
    <p style="margin: 0 0 16px; color: #555555;">
        {{ $order->customer_name }}<br>
        {{ $order->customer_phone }}<br>
        {!! nl2br(e($order->customer_address)) !!}
    </p>

    <p style="margin: 0 0 16px;">
        <strong>Payment method:</strong> {{ $order->getPaymentMethodText() }}
        @if((float) $order->paid_amount > 0)
            <br><strong>Amount paid:</strong> ৳{{ number_format((float) $order->paid_amount, 2) }}
        @endif
    </p>

    <p style="margin: 0; color: #555555;">
        We will contact you shortly to confirm the delivery. If you have any questions,
        simply reply to this email.
    </p>
@endsection
