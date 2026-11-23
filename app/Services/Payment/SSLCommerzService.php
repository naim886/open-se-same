<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSLCommerzService
{
    public function enabled(): bool
    {
        return Setting::bool('payment.sslcommerz.enabled')
            && trim((string) Setting::effective('payment.sslcommerz.store_id', '')) !== ''
            && trim((string) Setting::effective('payment.sslcommerz.store_password', '')) !== '';
    }

    public function isLive(): bool
    {
        return Setting::effective('payment.sslcommerz.mode', 'sandbox') === 'live';
    }

    public function baseUrl(): string
    {
        $custom = trim((string) Setting::effective('payment.sslcommerz.base_url', ''));

        if ($custom !== '') {
            return rtrim($custom, '/');
        }

        return $this->isLive()
            ? 'https://securepay.sslcommerz.com'
            : 'https://sandbox.sslcommerz.com';
    }

    /**
     * Create an SSLCommerz session and return the gateway response.
     *
     * @return array<string, mixed>
     */
    public function initiate(Order $order): array
    {
        $order->loadMissing('orderItems.product');

        $items = $order->orderItems;

        $productNames = $items
            ->map(fn ($item) => $item->product_name ?: ($item->product?->name ?? 'Product'))
            ->filter()
            ->values();

        $totalQuantity = max(1, (int) $items->sum('quantity'));

        $cart = $items->map(fn ($item) => [
            'sku' => (string) ($item->variation_id ?: $item->product_id),
            'product' => Str::limit($item->product_name ?: ($item->product?->name ?? 'Product'), 255, ''),
            'quantity' => (string) max(1, (int) $item->quantity),
            'amount' => number_format((float) $item->subtotal, 2, '.', ''),
            'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
        ])->values()->all();

        $customerEmail = $order->customer_email
            ?: (string) Setting::get('invoice.company_email', 'no-reply@example.com');

        $payload = [
            'store_id' => trim((string) Setting::effective('payment.sslcommerz.store_id', '')),
            'store_passwd' => trim((string) Setting::effective('payment.sslcommerz.store_password', '')),
            'total_amount' => number_format((float) $order->total_amount, 2, '.', ''),
            'currency' => 'BDT',
            'tran_id' => (string) $order->order_number,
            'success_url' => route('payment.sslcommerz.success'),
            'fail_url' => route('payment.sslcommerz.fail'),
            'cancel_url' => route('payment.sslcommerz.cancel'),
            'ipn_url' => route('payment.sslcommerz.ipn'),

            'cus_name' => Str::limit((string) $order->customer_name, 50, ''),
            'cus_email' => Str::limit($customerEmail, 50, ''),
            'cus_add1' => Str::limit((string) $order->customer_address, 50, ''),
            'cus_add2' => '',
            'cus_city' => 'Dhaka',
            'cus_state' => 'Dhaka',
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => Str::limit((string) $order->customer_phone, 20, ''),
            'cus_fax' => '',

            'ship_name' => Str::limit((string) $order->customer_name, 50, ''),
            'ship_add1' => Str::limit((string) $order->customer_address, 50, ''),
            'ship_add2' => '',
            'ship_city' => 'Dhaka',
            'ship_state' => 'Dhaka',
            'ship_postcode' => '1000',
            'ship_country' => 'Bangladesh',
            'shipping_method' => 'Courier',

            'num_of_item' => $totalQuantity,
            'product_name' => Str::limit($productNames->implode(', '), 255, ''),
            'product_category' => 'general',
            'product_profile' => 'general',
            'product_amount' => number_format((float) $order->product_price + (float) $order->delivery_charge, 2, '.', ''),
            'vat' => '0.00',
            'discount_amount' => number_format((float) $order->discount_amount, 2, '.', ''),
            'convenience_fee' => '0.00',
            'cart' => json_encode($cart, JSON_UNESCAPED_UNICODE),

            'value_a' => (string) $order->id,
            'value_b' => (string) $order->order_number,
            'value_c' => (string) $order->customer_phone,
            'value_d' => 'sslcommerz',
        ];

        $response = Http::asForm()
            ->timeout(45)
            ->post($this->baseUrl().'/gwprocess/v4/api.php', $payload);

        $body = $response->json();

        if (! is_array($body)) {
            $body = [
                'status' => 'FAILED',
                'failedreason' => 'SSLCommerz returned an empty or non-JSON response.',
            ];
        }

        $body['_http_status'] = $response->status();

        return $body;
    }

    /**
     * Validate a successful transaction through the validation server API.
     *
     * @return array<string, mixed>
     */
    public function validate(string $valId): array
    {
        try {
            $response = Http::timeout(45)->get(
                $this->baseUrl().'/validator/api/validationserverAPI.php',
                [
                    'val_id' => $valId,
                    'store_id' => trim((string) Setting::effective('payment.sslcommerz.store_id', '')),
                    'store_passwd' => trim((string) Setting::effective('payment.sslcommerz.store_password', '')),
                    'format' => 'json',
                ]
            );

            $body = $response->json();
        } catch (\Throwable $e) {
            app_error_log('SSLCommerz Validation', $e);

            return [];
        }

        return is_array($body) ? $body : [];
    }
}
