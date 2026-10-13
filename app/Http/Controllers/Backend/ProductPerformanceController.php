<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductPerformanceController extends Controller
{
    public function index(Request $request): View
    {
        // ── Period resolution ─────────────────────────────────────────────
        $period = $request->get('period', 'this_month');
        $today  = Carbon::today();

        switch ($period) {
            case 'today':
                $dateFrom = $today->copy()->startOfDay();
                $dateTo   = $today->copy()->endOfDay();
                $label    = 'Today (' . $today->format('d M Y') . ')';
                break;
            case 'last_7':
                $dateFrom = $today->copy()->subDays(6)->startOfDay();
                $dateTo   = $today->copy()->endOfDay();
                $label    = 'Last 7 Days';
                break;
            case 'last_month':
                $dateFrom = $today->copy()->subMonthNoOverflow()->startOfMonth();
                $dateTo   = $today->copy()->subMonthNoOverflow()->endOfMonth();
                $label    = $dateFrom->format('F Y');
                break;
            case 'custom':
                $dateFrom = $request->filled('date_from')
                    ? Carbon::parse($request->date_from)->startOfDay()
                    : $today->copy()->startOfMonth();
                $dateTo   = $request->filled('date_to')
                    ? Carbon::parse($request->date_to)->endOfDay()
                    : $today->copy()->endOfDay();
                $label    = $dateFrom->format('d M Y') . ' – ' . $dateTo->format('d M Y');
                break;
            default:
                $period   = 'this_month';
                $dateFrom = $today->copy()->startOfMonth();
                $dateTo   = $today->copy()->endOfMonth();
                $label    = $today->format('F Y');
        }

        // ── Best selling: products sold in period ─────────────────────────
        // Left-join products → order_items → orders (period-filtered)
        $bestSelling = DB::table('products')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', function ($join) use ($dateFrom, $dateTo) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.order_date', [$dateFrom, $dateTo])
                     ->whereNull('orders.deleted_at');
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->groupBy(
                'products.id', 'products.name', 'products.price',
                'products.sale_price', 'products.stock', 'products.primary_photo',
                'products.status', 'categories.name'
            )
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.sale_price',
                'products.stock',
                'products.primary_photo',
                'products.status',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->orderByDesc('qty_sold')
            ->get();

        // ── Never sold: products with zero order_items ever (all-time) ────
        $neverSold = Product::whereNotIn('products.id', function ($q) {
                $q->select('product_id')
                  ->from('order_items')
                  ->whereNotNull('product_id');
            })
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id', 'products.name', 'products.price',
                'products.sale_price', 'products.stock', 'products.primary_photo',
                'products.status', 'products.created_at',
                'categories.name as category_name'
            )
            ->orderBy('products.name')
            ->get();

        // ── Summary counts ────────────────────────────────────────────────
        $totalProducts  = Product::whereNull('deleted_at')->count();
        $totalSoldCount = $bestSelling->count();
        $totalNeverSold = $neverSold->count();
        $totalQtySold   = (int) $bestSelling->sum('qty_sold');
        $totalRevenue   = (float) $bestSelling->sum('revenue');

        $cms_content = [
            'page_title' => 'Product Performance',
            'module'     => 'Report',
            'sub_module' => 'Product Performance',
        ];

        return view('backend.modules.report.product-performance', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'bestSelling', 'neverSold',
            'totalProducts', 'totalSoldCount', 'totalNeverSold',
            'totalQtySold', 'totalRevenue',
            'request'
        ));
    }
}
