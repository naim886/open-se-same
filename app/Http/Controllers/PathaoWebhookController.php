<?php

namespace App\Http\Controllers;

use App\Models\CourierLog;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PathaoWebhookController extends Controller
{
    /**
     * Pathao events that should change the internal order status.
     * Other events only update the courier status/history.
     */
    private array $statusMap = [
        'order.delivered'             => Order::STATUS_DELIVERED,
        'order.partial-delivery'      => Order::STATUS_PARTIAL_DELIVERED,
        'order.returned'              => Order::STATUS_RETURNED,
        'order.returned-to-merchant'  => Order::STATUS_RETURNED,
        'order.paid-return'           => Order::STATUS_RETURNED,
    ];

    /**
     * POST /webhooks/pathao
     */
    public function handle(Request $request): JsonResponse
    {
        $secret  = trim((string) Setting::effective('courier.pathao.webhook_secret', ''));
        $payload = $request->json()->all();
        $event   = (string) ($payload['event'] ?? '');

        Log::channel('pathao')->info('Pathao webhook received', [
            'ip'      => $request->ip(),
            'event'   => $event,
            'payload' => $payload,
        ]);

        if ($secret === '') {
            Log::channel('pathao')->warning('Pathao webhook secret is not configured.');

            return $this->respond('', [
                'status'  => 'error',
                'message' => 'Pathao webhook secret is not configured.',
            ], 500);
        }

        // Pathao's "verify webhook" button sends this event. Echo the secret
        // header expected by the merchant panel and return 202.
        if ($event === 'webhook_integration') {
            return $this->respond($secret, [
                'status'  => 'success',
                'message' => 'Webhook integration verified.',
            ]);
        }

        $signature = (string) $request->header('X-PATHAO-Signature', '');

        if ($signature === '' || ! hash_equals($secret, $signature)) {
            Log::channel('pathao')->warning('Pathao webhook unauthorized', ['ip' => $request->ip()]);

            return $this->respond($secret, [
                'status'  => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        if ($event === '') {
            Log::channel('pathao')->warning('Pathao webhook missing event', ['payload' => $payload]);

            return $this->respond($secret, [
                'status'  => 'error',
                'message' => 'Missing event.',
            ], 422);
        }

        $consignmentId   = (string) ($payload['consignment_id'] ?? '');
        $merchantOrderId = (string) ($payload['merchant_order_id'] ?? '');

        $order = null;

        if ($consignmentId !== '') {
            $order = Order::where('consignment_id', $consignmentId)->first();
        }

        if (! $order && $merchantOrderId !== '') {
            $order = Order::where('order_number', $merchantOrderId)->first();
        }

        if (! $order) {
            Log::channel('pathao')->warning('Pathao webhook: order not found', [
                'consignment_id'    => $consignmentId,
                'merchant_order_id' => $merchantOrderId,
            ]);

            return $this->respond($secret, [
                'status'  => 'error',
                'message' => 'Order not found.',
            ], 404);
        }

        $newStatus = $this->statusMap[$event] ?? null;
        $reason    = $payload['reason'] ?? null;

        $courierUpdate = [
            'courier_name'   => 'pathao',
            'courier_status' => $event,
        ];

        if ($consignmentId !== '') {
            $courierUpdate['consignment_id'] = $consignmentId;
        }

        // Pathao uses consignment_id only; clear any old Steadfast tracking code.
        $courierUpdate['tracking_code'] = null;

        $statusChanged = $newStatus !== null && (int) $order->status !== $newStatus;
        $prevStatus    = (int) $order->status;

        if ($statusChanged) {
            if (! Order::isStockRestoredStatus($prevStatus) && Order::isStockRestoredStatus($newStatus)) {
                $order->restoreStock();
            } elseif (Order::isStockRestoredStatus($prevStatus) && ! Order::isStockRestoredStatus($newStatus)) {
                $order->decrementStock();
            }

            $courierUpdate['status'] = $newStatus;

            if ($newStatus === Order::STATUS_DELIVERED) {
                $courierUpdate['delivery_date'] = now();
            }
        }

        $order->update($courierUpdate);

        CourierLog::create([
            'order_id'         => $order->id,
            'courier_name'     => 'pathao',
            'consignment_id'   => $consignmentId !== '' ? $consignmentId : $order->consignment_id,
            'tracking_code'    => null,
            'status'           => $event,
            'tracking_message' => $reason,
            'response'         => $payload,
            'sent_phone'       => null,
            'source'           => 'webhook',
        ]);

        if ($statusChanged) {
            OrderHistory::create([
                'order_id'       => $order->id,
                'user_id'        => null,
                'action'         => 'status_changed',
                'previous'       => ['status' => $prevStatus],
                'changes'        => ['status' => ['from' => $prevStatus, 'to' => $newStatus]],
                'items_previous' => null,
                'items_new'      => null,
            ]);
        }

        OrderHistory::create([
            'order_id'       => $order->id,
            'user_id'        => null,
            'action'         => 'courier_webhook',
            'previous'       => [],
            'changes'        => [
                'courier'             => 'pathao',
                'notification_type'   => $event,
                'courier_status'      => $event,
                'tracking_message'    => $reason,
                'collected_amount'    => $payload['collected_amount'] ?? null,
                'courier_updated_at'  => $payload['updated_at'] ?? null,
            ],
            'items_previous' => null,
            'items_new'      => null,
        ]);

        return $this->respond($secret, [
            'status'  => 'success',
            'message' => 'Webhook received successfully.',
        ]);
    }

    /**
     * Pathao expects a 202 response and this header while verifying the URL.
     */
    private function respond(string $secret, array $body, int $status = 202): JsonResponse
    {
        $response = response()->json($body, $status);

        if ($secret !== '') {
            $response->header('X-Pathao-Merchant-Webhook-Integration-Secret', $secret);
        }

        return $response;
    }
}
