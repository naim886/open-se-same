<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    final public function index(Request $request)
    {
        // ── Period resolution ──────────────────────────────────────────────
        $period   = $request->get('period', 'today');
        $today    = Carbon::today();

        switch ($period) {
            case 'weekly':
                $dateFrom = $today->copy()->subDays(6)->startOfDay();
                $dateTo   = $today->copy()->endOfDay();
                break;
            case 'monthly':
                $dateFrom = $today->copy()->startOfMonth();
                $dateTo   = $today->copy()->endOfMonth();
                break;
            case 'last_month':
                $dateFrom = $today->copy()->subMonthNoOverflow()->startOfMonth();
                $dateTo   = $today->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'custom':
                $dateFrom = $request->filled('date_from')
                    ? Carbon::parse($request->date_from)->startOfDay()
                    : $today->copy()->startOfMonth();
                $dateTo   = $request->filled('date_to')
                    ? Carbon::parse($request->date_to)->endOfDay()
                    : $today->copy()->endOfDay();
                break;
            default: // today
                $period   = 'today';
                $dateFrom = $today->copy()->startOfDay();
                $dateTo   = $today->copy()->endOfDay();
        }

        // ── Order Stats (period) ──────────────────────────────────────────
        $periodOrders = Order::whereBetween('order_date', [$dateFrom, $dateTo]);

        $totalOrders   = (clone $periodOrders)->count();

        // Exclude cancelled & returned from revenue/due so unpaid totals are meaningful
        $activeOrders  = (clone $periodOrders)->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED]);
        $totalRevenue  = (clone $activeOrders)->sum('total_amount');
        $paidRevenue   = (clone $activeOrders)->sum('paid_amount');
        $dueRevenue    = $totalRevenue - $paidRevenue;

        // AOV is derived from the same order set as revenue (cancelled &
        // returned excluded) so the two figures stay consistent.
        $activeOrderCount = (clone $activeOrders)->count();
        $avgOrderValue    = $activeOrderCount > 0 ? round($totalRevenue / $activeOrderCount, 2) : 0;

        // Per-status counts
        $statusCounts = (clone $periodOrders)
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        $statusStats = collect(Order::STATUS_LIST)->map(function ($label, $id) use ($statusCounts) {
            return [
                'id'    => $id,
                'label' => $label,
                'count' => (int)($statusCounts[$id] ?? 0),
                'color' => Order::STATUS_COLORS[$id] ?? 'secondary',
            ];
        })->values();

        // ── Inventory Stats (all-time, not period-filtered) ────────────────
        // Anything below $lowStockThreshold units counts as low stock and
        // raises the restock alert. The three tiles partition the catalogue
        // (>= threshold / 1..threshold-1 / 0) so they don't double-count.
        $lowStockThreshold   = 5;
        $inStockCount        = Product::where('stock', '>=', $lowStockThreshold)->count();
        $lowStockCount       = Product::where('stock', '>', 0)->where('stock', '<', $lowStockThreshold)->count();
        $outOfStockCount     = Product::where('stock', 0)->count();
        $lowStockAlertCount  = Product::where('stock', '>=', 0)->where('stock', '<', $lowStockThreshold)->count();
        $totalInventoryValue = Product::whereNotNull('stock')
            ->where('stock', '>', 0)
            ->selectRaw('SUM(stock * COALESCE(cost, price)) as total')
            ->value('total') ?? 0;

        $lowStockProducts = Product::where('stock', '>=', 0)
            ->where('stock', '<', $lowStockThreshold)
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'stock', 'price', 'primary_photo', 'status']);

        // ── Day-wise Chart Data ────────────────────────────────────────────
        $daywise = (clone $periodOrders)
            ->select(
                DB::raw('DATE(order_date) as day'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Fill every day in range with 0 for gaps
        $days         = [];
        $daywiseMap   = $daywise->keyBy('day');
        $cursor       = $dateFrom->copy()->startOfDay();
        $end          = $dateTo->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key   = $cursor->toDateString();
            $days[] = [
                'day'     => $key,
                'label'   => $cursor->format('d M'),
                'orders'  => (int)($daywiseMap[$key]->orders  ?? 0),
                'revenue' => (float)($daywiseMap[$key]->revenue ?? 0),
            ];
            $cursor->addDay();
        }

        // ── Delivery Location Pie ─────────────────────────────────────────
        $deliveryPie = (clone $periodOrders)
            ->select('delivery_location', DB::raw('COUNT(*) as cnt'))
            ->groupBy('delivery_location')
            ->get()
            ->map(fn ($r) => [
                'label' => Order::DELIVERY_LIST[$r->delivery_location] ?? 'Unknown',
                'count' => (int)$r->cnt,
            ])
            ->values();

        // ── Orders by who placed them (period) ──────────────────────────────
        $placedByPie = (clone $periodOrders)
            ->select('orders.user_id', DB::raw('COUNT(*) as cnt'), 'users.name')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('orders.user_id', 'users.name')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->name ?? 'System',
                'count' => (int)$r->cnt,
            ])
            ->values();

        // ── New vs Returning Customers ─────────────────────────────────────
        // Use SQL EXISTS to avoid loading thousands of phones into PHP memory
        // (prevents timeout / 502 on high-traffic periods like weekly)
        $totalDistinctPhones = DB::table('orders')
            ->whereBetween('order_date', [$dateFrom, $dateTo])
            ->whereNotNull('customer_phone')
            ->distinct()
            ->count('customer_phone');

        $returningCount = DB::table('orders as cur')
            ->whereBetween('cur.order_date', [$dateFrom, $dateTo])
            ->whereNotNull('cur.customer_phone')
            ->whereExists(function ($q) use ($dateFrom) {
                $q->select(DB::raw(1))
                  ->from('orders as prev')
                  ->whereColumn('prev.customer_phone', 'cur.customer_phone')
                  ->where('prev.order_date', '<', $dateFrom);
            })
            ->distinct()
            ->count('cur.customer_phone');

        $newCount = max(0, $totalDistinctPhones - $returningCount);

        // Previous period for comparison (same length)
        $periodLength  = max(1, $dateFrom->diffInDays($dateTo));
        $prevDateFrom  = $dateFrom->copy()->subDays($periodLength + 1);
        $prevDateTo    = $dateFrom->copy()->subDay();

        // Previous period, compared like-for-like: each growth figure uses the
        // same status filter as the current value it is compared against.
        $prevScope       = Order::whereBetween('order_date', [$prevDateFrom, $prevDateTo]);
        $prevTotalOrders = (clone $prevScope)->count();

        $prevActiveScope   = (clone $prevScope)->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED]);
        $prevOrders        = (clone $prevActiveScope)->count();
        $prevRevenue       = (clone $prevActiveScope)->sum('total_amount');
        $prevAvgOrderValue = $prevOrders > 0 ? $prevRevenue / $prevOrders : null;

        $orderGrowth   = $prevTotalOrders > 0 ? round((($totalOrders - $prevTotalOrders) / $prevTotalOrders) * 100, 1) : null;
        $revenueGrowth = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;
        $aovGrowth     = $prevAvgOrderValue ? round((($avgOrderValue - $prevAvgOrderValue) / $prevAvgOrderValue) * 100, 1) : null;

        // ── Top Selling Products (period, ranked by units sold) ────────────
        // Aggregated from order_items rather than the curated TopSellingProduct
        // module so the ranking reflects real sales in the selected period.
        $topSellingProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.order_date', [$dateFrom, $dateTo])
            ->whereNotIn('o.status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])
            ->groupBy('oi.product_id', 'p.name', 'p.primary_photo', 'oi.product_name')
            ->selectRaw('oi.product_id as product_id')
            ->selectRaw('COALESCE(p.name, oi.product_name) as name')
            ->selectRaw('p.primary_photo as photo')
            ->selectRaw('SUM(oi.quantity) as qty')
            ->selectRaw('SUM(oi.subtotal) as revenue')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // ── Recent Orders (10) ─────────────────────────────────────────────
        $recentOrders = (clone $periodOrders)
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get(['id', 'order_number', 'customer_name', 'customer_phone', 'total_amount', 'status', 'order_date']);

        return view('backend.index', compact(
            'period', 'dateFrom', 'dateTo',
            'totalOrders', 'totalRevenue', 'paidRevenue', 'dueRevenue',
            'activeOrderCount', 'avgOrderValue', 'aovGrowth',
            'statusStats',
            'inStockCount', 'lowStockCount', 'outOfStockCount', 'totalInventoryValue',
            'lowStockProducts', 'lowStockAlertCount', 'lowStockThreshold',
            'topSellingProducts',
            'days',
            'deliveryPie',
            'placedByPie',
            'newCount', 'returningCount',
            'orderGrowth', 'revenueGrowth',
            'recentOrders',
            'request'
        ));
    }
}

