<?php

namespace App\Services;

use App\Mail\AdminLowStockMail;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

/**
 * Customer and admin order/inventory notifications.
 *
 * Every method swallows its own errors: a mail outage must never break the
 * checkout that triggered it. Messages are queued (the app already relies on a
 * queue worker for its Facebook CAPI jobs).
 */
class OrderNotificationService
{
    /**
     * Email the customer a confirmation and alert the admin about a new order.
     */
    public function orderPlaced(Order $order): void
    {
        try {
            if (Setting::bool('notification.order_confirmation') && $order->customer_email) {
                Mail::to($order->customer_email)->queue(new OrderConfirmationMail($order));
            }
        } catch (\Throwable $e) {
            app_error_log('Order Confirmation Mail', $e);
        }

        try {
            $adminEmail = Setting::get('notification.admin_email');

            if (Setting::bool('notification.admin_new_order') && $adminEmail) {
                Mail::to($adminEmail)->queue(new AdminNewOrderMail($order));
            }
        } catch (\Throwable $e) {
            app_error_log('Admin New Order Mail', $e);
        }
    }

    /**
     * Warn the admin the first time a product falls below the stock threshold.
     */
    public function alertIfLowStock(Product $product): void
    {
        try {
            $adminEmail = Setting::get('notification.admin_email');

            if (! Setting::bool('notification.admin_low_stock') || ! $adminEmail) {
                return;
            }

            $threshold = Setting::int('inventory.low_stock_threshold');

            if ($threshold <= 0 || (int) $product->stock >= $threshold) {
                return;
            }

            // Already warned for this dip; the flag clears again on restock.
            if ($product->low_stock_notified_at !== null) {
                return;
            }

            Mail::to($adminEmail)->queue(new AdminLowStockMail($product, $threshold));

            $product->forceFill(['low_stock_notified_at' => now()])->saveQuietly();
        } catch (\Throwable $e) {
            app_error_log('Admin Low Stock Mail', $e);
        }
    }
}
