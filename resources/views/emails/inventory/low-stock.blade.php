@extends('emails.layouts.app')

@section('content')
    <h1 style="font-size: 20px; color: #222222; margin: 0 0 6px;">Low stock warning</h1>

    <p style="margin: 0 0 18px;">
        <strong>{{ $product->name }}</strong> has fallen below your stock threshold.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="padding: 3px 0; color: #6b7280; width: 150px;">Remaining stock</td>
            <td style="padding: 3px 0; font-weight: bold; color: #b45309;">{{ (int) $product->stock }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Alert threshold</td>
            <td style="padding: 3px 0;">{{ $threshold }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 0; color: #6b7280;">Product ID</td>
            <td style="padding: 3px 0;">#{{ $product->id }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 16px; color: #555555;">
        You will not receive another alert for this product until its stock is
        replenished above the threshold.
    </p>

    <p style="margin: 0;">
        <a href="{{ url('/admin/product/' . $product->id . '/edit') }}"
           style="display: inline-block; padding: 10px 18px; background-color: #027b50; color: #ffffff; text-decoration: none; border-radius: 4px;">
            Restock this product
        </a>
    </p>
@endsection
