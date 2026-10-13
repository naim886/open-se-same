<?php

namespace App\Http\Controllers;

use App\Models\CourierLog;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SteadfastWebhookController extends Controller
{
    /** Steadfast API key used to authenticate incoming webhooks */
    private string $apiKey = 'ys97julfgukthikhhjovu9x92dw6bvbf';

    /**
     * Map Steadfast delivery status strings to our internal Order status constants.
     * null = do not change order status (informational only).
     */
    private array $statusMap = [
        'delivered'         => Order::STATUS_DELIVERED,
        'partial_delivered' => Order::STATUS_PARTIAL_DELIVERED,
        'cancelled'         => Order::STATUS_CANCELLED,
        'pending'           => Order::STATUS_COURIER_PENDING,
        'unknown'           => null,
    ];

    /**
     * Single entry point for all Steadfast webhook events.
     * POST /webhooks/steadfast
     */
    public function handle(Request $request): JsonResponse
    {
        // ------------------------------------------------
        // 1. Authenticate — Bearer token must match API key
        // ------------------------------------------------
        $authHeader = $request->header('Authorization', '');
        $token      = trim(str_replace('Bearer', '', $authHeader));

        Log::channel('steadfast')->info('Steadfast webhook received', [
            'ip'              => $request->ip(),
            'notification_type' => $request->input('notification_type'),
            'consignment_id'  => $request->input('consignment_id'),
            'payload'         => $request->json()->all(),
        ]);

        if ($token !== $this->apiKey) {
            Log::channel('steadfast')->warning('Steadfast webhook unauthorized', ['ip' => $request->ip()]);
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        // ------------------------------------------------
        // 2. Basic payload validation
        // ------------------------------------------------
        $payload = $request->json()->all();

        if (empty($payload['notification_type']) || empty($payload['consignment_id'])) {
            Log::channel('steadfast')->warning('Steadfast webhook missing required fields', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Missing required fields.'], 422);
        }

        $consignmentId    = (string) $payload['consignment_id'];
        $notificationType = $payload['notification_type'];

        // ------------------------------------------------
        // 3. Find the order by consignment_id
        // ------------------------------------------------
        $order = Order::where('consignment_id', $consignmentId)->first();

        if (!$order) {
            Log::channel('steadfast')->warning('Steadfast webhook: order not found', [
                'consignment_id' => $consignmentId,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Invalid consignment ID.'], 404);
        }

        Log::channel('steadfast')->info('Steadfast webhook: order matched', [
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'consignment_id'  => $consignmentId,
            'current_status'  => $order->status,
            'notification_type' => $notificationType,
        ]);

        // ------------------------------------------------
        // 4. Route to handler
        // ------------------------------------------------
        return match ($notificationType) {
            'delivery_status' => $this->handleDeliveryStatus($order, $payload),
            'tracking_update' => $this->handleTrackingUpdate($order, $payload),
            default           => response()->json(['status' => 'error', 'message' => 'Unknown notification type.'], 422),
        };
    }

    // ----------------------------------------------------------------
    // delivery_status
    // ----------------------------------------------------------------

    private function handleDeliveryStatus(Order $order, array $payload): JsonResponse
    {
        $rawStatus       = strtolower(trim($payload['status'] ?? ''));
        $trackingMessage = $payload['tracking_message'] ?? null;
        $updatedAt       = $payload['updated_at'] ?? null;

        Log::channel('steadfast')->info('Steadfast delivery_status received', [
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'consignment_id'  => $payload['consignment_id'],
            'raw_status'      => $rawStatus,
            'tracking_message'=> $trackingMessage,
            'current_order_status' => $order->status,
        ]);

        // Log the webhook hit to courier_logs
        CourierLog::create([
            'order_id'        => $order->id,
            'consignment_id'  => (string) $payload['consignment_id'],
            'tracking_code'   => $order->tracking_code,
            'status'          => $rawStatus,
            'tracking_message'=> $trackingMessage,
            'response'        => $payload,
            'sent_phone'      => null,
            'source'          => 'webhook',
        ]);

        // Map to internal status (null = no status change)
        $newStatus = $this->statusMap[$rawStatus] ?? null;

        Log::channel('steadfast')->info('Steadfast delivery_status: status mapping', [
            'order_id'      => $order->id,
            'raw_status'    => $rawStatus,
            'mapped_status' => $newStatus,
            'current_status'=> (int) $order->status,
            'will_update'   => $newStatus !== null && (int) $order->status !== $newStatus,
        ]);

        if ($newStatus !== null && (int) $order->status !== $newStatus) {
            $prevStatus = (int) $order->status;

            // ── Stock: restore on cancel, re-decrement on reactivation ──
            if (!Order::isStockRestoredStatus($prevStatus) && Order::isStockRestoredStatus($newStatus)) {
                $order->restoreStock();
            } elseif (Order::isStockRestoredStatus($prevStatus) && !Order::isStockRestoredStatus($newStatus)) {
                $order->decrementStock();
            }

            $updateData = ['status' => $newStatus];

            // When delivered, stamp the delivery date
            if ($newStatus === Order::STATUS_DELIVERED) {
                $updateData['delivery_date'] = now();
                Log::channel('steadfast')->info('Steadfast delivery_status: marking delivered, setting delivery_date', [
                    'order_id'      => $order->id,
                    'delivery_date' => $updateData['delivery_date'],
                ]);
            }

            $order->update($updateData);

            Log::channel('steadfast')->info('Steadfast delivery_status: order status updated', [
                'order_id'    => $order->id,
                'from_status' => $prevStatus,
                'to_status'   => $newStatus,
            ]);

            // Status change history — shows up in Status Change Log
            OrderHistory::create([
                'order_id'       => $order->id,
                'user_id'        => null,
                'action'         => 'status_changed',
                'previous'       => ['status' => $prevStatus],
                'changes'        => ['status' => ['from' => $prevStatus, 'to' => $newStatus]],
                'items_previous' => null,
                'items_new'      => null,
            ]);
        } else {
            Log::channel('steadfast')->info('Steadfast delivery_status: no status change needed', [
                'order_id'      => $order->id,
                'raw_status'    => $rawStatus,
                'mapped_status' => $newStatus,
                'current_status'=> (int) $order->status,
            ]);
        }

        // Also store a courier_webhook history entry for the full detail
        OrderHistory::create([
            'order_id'       => $order->id,
            'user_id'        => null,
            'action'         => 'courier_webhook',
            'previous'       => [],
            'changes'        => [
                'notification_type'  => 'delivery_status',
                'courier_status'     => $rawStatus,
                'tracking_message'   => $trackingMessage,
                'cod_amount'         => $payload['cod_amount'] ?? null,
                'delivery_charge'    => $payload['delivery_charge'] ?? null,
                'courier_updated_at' => $updatedAt,
            ],
            'items_previous' => null,
            'items_new'      => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Webhook received successfully.',
        ]);
    }

    // ----------------------------------------------------------------
    // tracking_update
    // ----------------------------------------------------------------

    private function handleTrackingUpdate(Order $order, array $payload): JsonResponse
    {
        $trackingMessage = $payload['tracking_message'] ?? null;
        $updatedAt       = $payload['updated_at'] ?? null;

        Log::channel('steadfast')->info('Steadfast tracking_update received', [
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'consignment_id'  => $payload['consignment_id'],
            'tracking_message'=> $trackingMessage,
        ]);

        // Log to courier_logs
        CourierLog::create([
            'order_id'         => $order->id,
            'consignment_id'   => (string) $payload['consignment_id'],
            'tracking_code'    => $order->tracking_code,
            'status'           => null,
            'tracking_message' => $trackingMessage,
            'response'         => $payload,
            'sent_phone'       => null,
            'source'           => 'webhook',
        ]);

        // Store in order history for visibility
        OrderHistory::create([
            'order_id'       => $order->id,
            'user_id'        => null,
            'action'         => 'courier_webhook',
            'previous'       => [],
            'changes'        => [
                'notification_type'  => 'tracking_update',
                'tracking_message'   => $trackingMessage,
                'courier_updated_at' => $updatedAt,
            ],
            'items_previous' => null,
            'items_new'      => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Webhook received successfully.',
        ]);
    }
}
