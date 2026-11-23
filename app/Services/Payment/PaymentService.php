<?php

namespace App\Services\Payment;

use App\Jobs\SendFacebookCapiEvent;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function sslcommerzEnabled(): bool
    {
        return (new SSLCommerzService)->enabled();
    }

    public function nagadEnabled(): bool
    {
        return (new NagadService)->enabled();
    }

    public function paymentMethodId(string $method): int
    {
        return match ($method) {
            'sslcommerz' => Order::PAYMENT_METHOD_SSLCOMMERZ,
            'nagad' => Order::PAYMENT_METHOD_NAGAD,
            default => Order::PAYMENT_METHOD_COD,
        };
    }

    /**
     * Start the selected online gateway for an existing order.
     *
     * @return array<string, mixed>
     */
    public function start(Order $order): array
    {
        $method = (int) $order->payment_method;

        if ($method === Order::PAYMENT_METHOD_NAGAD) {
            $gateway = 'nagad';
            $service = new NagadService;

            if (! $service->enabled()) {
                return ['status' => 'FAILED', 'message' => 'Nagad is not configured or enabled in Settings.'];
            }

            $result = $service->initiate($order);
            $url = $result['callBackUrl'] ?? null;
            $reference = $result['paymentReferenceId'] ?? null;
        } else {
            $gateway = 'sslcommerz';
            $service = new SSLCommerzService;

            if (! $service->enabled()) {
                return ['status' => 'FAILED', 'message' => 'SSLCommerz is not configured or enabled in Settings.'];
            }

            $result = $service->initiate($order);
            $url = $result['GatewayPageURL'] ?? null;
            $reference = (string) $order->order_number;
        }

        if (empty($url)) {
            $message = $result['failedreason']
                ?? $result['message']
                ?? 'The payment gateway did not return a payment URL.';

            $order->forceFill([
                'payment_gateway' => $gateway,
                'payment_reference' => $reference ?: $order->payment_reference,
            ])->save();

            return [
                'status' => 'FAILED',
                'gateway' => $gateway,
                'message' => (string) $message,
                'response' => $result,
            ];
        }

        // Keep the gateway visible in admin even before the payment completes,
        // and give Nagad callbacks a reference even if they omit the order id.
        $order->forceFill([
            'payment_gateway' => $gateway,
            'payment_reference' => $reference ?: $order->payment_reference,
        ])->save();

        return [
            'status' => 'SUCCESS',
            'gateway' => $gateway,
            'url' => (string) $url,
            'reference' => $reference,
            'response' => $result,
        ];
    }

    /**
     * Mark an order paid and dispatch the server-side Purchase event once.
     *
     * The row is locked and re-read so a success callback racing with the IPN
     * cannot dispatch two Purchase events or overwrite the transaction id.
     */
    public function markPaid(
        Order $order,
        string $gateway,
        ?string $transactionId,
        ?string $reference,
        array $payload = []
    ): string {
        [$purchaseEventId, $shouldDispatch] = DB::transaction(function () use ($order, $gateway, $transactionId, $reference, $payload): array {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            $alreadyPaid = (int) $locked->payment_status === Order::PAYMENT_STATUS_PAID;

            if (! $alreadyPaid) {
                $previous = [
                    'payment_status' => (int) $locked->payment_status,
                    'paid_amount' => (float) $locked->paid_amount,
                ];

                $locked->update([
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                    'paid_amount' => $locked->total_amount,
                    'payment_gateway' => $gateway,
                    'payment_transaction_id' => $transactionId,
                    'payment_reference' => $reference ?: $locked->payment_reference,
                    'paid_at' => now(),
                ]);

                OrderHistory::create([
                    'order_id' => $locked->id,
                    'user_id' => auth()->id(),
                    'action' => 'payment_paid',
                    'previous' => $previous,
                    'changes' => [
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                        'payment_reference' => $reference,
                        'amount' => (float) $locked->total_amount,
                        'status' => 'paid',
                        'payload' => $payload,
                    ],
                    'items_previous' => null,
                    'items_new' => null,
                ]);
            }

            $eventId = (string) ($locked->purchase_event_id ?? '');
            $shouldDispatch = false;

            if ($eventId === '') {
                $eventId = Str::uuid()->toString();
                $locked->update(['purchase_event_id' => $eventId]);
                $shouldDispatch = true;
            }

            return [$eventId, $shouldDispatch];
        });

        // Keep the caller's instance in sync for the success-page redirect.
        $order->refresh();

        // Tracking must never be able to roll back or fail a confirmed payment.
        if ($shouldDispatch) {
            try {
                $this->dispatchPurchase($order, $purchaseEventId);
            } catch (\Throwable $e) {
                app_error_log('Purchase CAPI Dispatch', $e);
            }
        }

        return $purchaseEventId;
    }

    public function markFailed(
        Order $order,
        string $gateway,
        string $status,
        ?string $reference = null,
        array $payload = []
    ): void {
        if ((int) $order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return;
        }

        if ($reference) {
            $order->update(['payment_reference' => $reference]);
        }

        OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => null,
            'action' => 'payment_failed',
            'previous' => ['payment_status' => (int) $order->payment_status],
            'changes' => [
                'gateway' => $gateway,
                'status' => $status,
                'reference' => $reference,
                'payload' => $payload,
            ],
            'items_previous' => null,
            'items_new' => null,
        ]);
    }

    /**
     * Order details used by the order success page and analytics.
     *
     * @return array<string, mixed>
     */
    public function orderDetails(Order $order): array
    {
        $order->loadMissing('orderItems.product');

        $contentIds = $order->orderItems
            ->map(fn ($item) => (string) ($item->variation_id ?: $item->product_id))
            ->filter()
            ->values()
            ->all();

        $items = $order->orderItems->map(fn ($item) => [
            'item_id' => (string) $item->product_id,
            'item_name' => $item->product_name ?: ($item->product?->name ?? 'Product'),
            'item_variant' => (string) ($item->variation_label ?? ''),
            'price' => (float) $item->unit_price,
            'quantity' => (int) $item->quantity,
        ])->values()->all();

        return [
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email ?? '',
            'customer_address' => $order->customer_address,
            'product_price' => (float) $order->product_price,
            'total_amount' => (float) $order->total_amount,
            'content_ids' => $contentIds,
            'items' => $items,
            'delivery_charge' => (float) $order->delivery_charge,
            'discount_amount' => (float) $order->discount_amount,
            'coupon_code' => $order->coupon_code,
        ];
    }

    public function dispatchPurchase(Order $order, string $eventId): void
    {
        $order->loadMissing('orderItems.product');

        $context = is_array($order->analytics_context ?? null) ? $order->analytics_context : [];

        $nameParts = explode(' ', trim((string) $order->customer_name), 2);

        $userData = [
            'ip' => $context['ip'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? (request()->userAgent() ?? ''),
            'fbc' => $context['fbc'] ?? request()->cookie('_fbc'),
            'fbp' => $context['fbp'] ?? request()->cookie('_fbp'),
            'external_id' => $this->externalId($order),
            'email' => $order->customer_email ?? null,
            'phone' => $order->customer_phone ?? null,
            'first_name' => $nameParts[0] ?? null,
            'last_name' => $nameParts[1] ?? null,
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip' => null,
            'country' => 'bd',
        ];

        $contents = $order->orderItems->map(fn ($item) => [
            'id' => (string) ($item->variation_id ?: $item->product_id),
            'title' => $item->product_name ?: ($item->product?->name ?? 'Product'),
            'quantity' => (int) $item->quantity,
            'item_price' => (float) $item->unit_price,
        ])->values()->all();

        $customData = [
            'value' => round((float) $order->total_amount, 2),
            'content_ids' => $order->orderItems
                ->map(fn ($item) => (string) ($item->variation_id ?: $item->product_id))
                ->filter()
                ->values()
                ->all(),
            'contents' => $contents,
            'content_type' => 'product',
            'num_items' => (int) $order->orderItems->sum('quantity'),
            'order_id' => (string) $order->order_number,
        ];

        SendFacebookCapiEvent::dispatch(
            'Purchase',
            $userData,
            $customData,
            $eventId,
            $context['event_source_url'] ?? request()->url()
        );
    }

    private function externalId(Order $order): string
    {
        $phone = preg_replace('/\D/', '', (string) $order->customer_phone);

        if ($phone !== '' && strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = '880'.substr($phone, 1);
        }

        return hash('sha256', $phone !== '' ? $phone : (string) $order->id);
    }
}
