<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class TrackingController extends Controller
{
    /**
     * Show tracking search form (and results if query present).
     */
    public function index(Request $request): View
    {
        $orders     = collect();
        $query      = trim($request->get('q', ''));
        $searched   = false;

        if ($query !== '') {
            $searched = true;

            // Convert Bengali digits to English digits so searches like ০১৭১২৩৪৫৬৭৮ work.
            $query = str_replace(
                ['০','১','২','৩','৪','৫','৬','৭','৮','৯'],
                ['0','1','2','3','4','5','6','7','8','9'],
                $query
            );

            // Normalize order number: strip leading "M-" or "m-" prefix so both
            // "M-1233" and "1233" match the same record.
            $normalizedQuery = preg_replace('/^[Mm]-?/', '', $query);

            $orders = Order::with(['orderItems.product', 'histories.user', 'courierLogs'])
                ->where(function ($q) use ($query, $normalizedQuery) {
                    $q->where('order_number', $normalizedQuery)
                      ->orWhere('order_number', $query)
                      ->orWhere('customer_phone', $query);
                })
                ->orderBy('order_date', 'desc')
                ->get()
                ->map(function (Order $order) {
                    // Mask phone: keep first 3, replace next 4 with ***, keep rest
                    $order->masked_phone   = self::maskPhone($order->customer_phone ?? '');
                    // Mask address: replace first 15 chars with ***
                    $order->masked_address = self::maskAddress($order->customer_address ?? '');

                    // Build a clean public status timeline from histories
                    $order->status_timeline = $order->histories
                        ->filter(fn ($h) => isset($h->changes['status']))
                        ->map(fn ($h) => [
                            'from'  => Order::STATUS_LIST[$h->changes['status']['from'] ?? null] ?? null,
                            'to'    => Order::STATUS_LIST[$h->changes['status']['to'] ?? null] ?? ($h->action === 'created' ? 'Pending' : null),
                            'time'  => $h->created_at,
                            'actor' => $h->user?->name ?? 'System',
                        ])
                        ->filter(fn ($row) => $row['to'] !== null)
                        ->values();

                    // Also push initial creation as first status if not already there
                    $creationHistory = $order->histories->firstWhere('action', 'created');
                    if ($creationHistory && $order->status_timeline->isEmpty()) {
                        $order->status_timeline = collect([[
                            'from'  => null,
                            'to'    => Order::STATUS_LIST[$order->status] ?? 'Pending',
                            'time'  => $creationHistory->created_at ?? $order->order_date,
                            'actor' => 'System',
                        ]]);
                    }

                    return $order;
                });
        }

        return view('frontend.pages.track-order', compact('orders', 'query', 'searched'));
    }

    /**
     * Mask phone: keep first 3 chars, replace next 4 with ***, keep remainder.
     * e.g. 01712345678 → 017***5678
     */
    private static function maskPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        $len = strlen($clean);

        if ($len === 0) return '';
        if ($len <= 7)  return str_repeat('*', $len);

        return substr($clean, 0, 3) . '***' . substr($clean, 7);
    }

    /**
     * Mask address: replace first 15 characters with ***.
     * e.g. "House 12, Road 3, Dhaka" → "***Road 3, Dhaka"  (wait — replaces first 15)
     */
    private static function maskAddress(string $address): string
    {
        if (strlen($address) <= 15) {
            return '***';
        }

        return '***' . substr($address, 15);
    }
}
