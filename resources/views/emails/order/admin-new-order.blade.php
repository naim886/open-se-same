@extends('emails.layouts.app')

@section('content')
    <h1 style="font-size: 20px; color: #222222; margin: 0 0 6px;">New order received</h1>

    <p style="margin: 0 0 18px;">
        Order <strong>#{{ $order->order_number }}</strong> was placed on
        {{ $order->order_date?->format('d M Y, h:i A') }}
        @if($order->source) via {{ ucfirst($order->source) }} @endif.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <tr>
            <td style="padding: 3px 0; color: #6b7280; width: 130px;">Customer</td>
            <td style="padding: 3px 0;">{{ $order->customer_name }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Phone</td>
            <td style="padding: 3px 0;">{{ $order->customer_phone }}</td>
        </tr>
        @if($order->customer_email)
            <tr>
                <td style="padding: 3px 0; color: #6b7280;">Email</td>
                <td style="padding: 3px 0;">{{ $order->customer_email }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Address</td>
            <td style="padding: 3px 0;">{!! nl2br(e($order->customer_address)) !!}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Delivery area</td>
            <td style="padding: 3px 0;">{{ \App\Models\Order::DELIVERY_LIST[$order->delivery_location] ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Payment</td>
            <td style="padding: 3px 0;">{{ $order->getPaymentMethodText() }} ({{ $order->getPaymentStatusText() }})</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
        <thead>
            <tr>
                <th align="left" style="border-bottom: 2px solid #e5e7eb; padding: 6px 0; font-size: 12px; text-transform: uppercase; color: #6b7280;">Item</th>
                <th align="center" style="border-bottom: 2px solid #e5e7eb; padding: 6px 0; font-size: 12px; text-transform: uppercase; color: #6b7280; width: 50px;">Qty</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 8px 0 3px; font-weight: bold; border-top: 1px solid #e5e7eb;">Order total</td>
            <td align="right" style="padding: 8px 0 3px; font-weight: bold; border-top: 1px solid #e5e7eb;">
                ৳{{ number_format((float) $order->total_amount, 2) }}
            </td>
        </tr>
    </table>

    @if($order->notes)
        <p style="margin: 0 0 4px;"><strong>Order note</strong></p>
        <p style="margin: 0 0 16px; color: #555555;">{!! nl2br(e($order->notes)) !!}</p>
    @endif

    <p style="margin: 0;">
        <a href="{{ url('/admin/orders/' . $order->id) }}"
           style="display: inline-block; padding: 10px 18px; background-color: #027b50; color: #ffffff; text-decoration: none; border-radius: 4px;">
            Open order in dashboard
        </a>
    </p>
@endsection
