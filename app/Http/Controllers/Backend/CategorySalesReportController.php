<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesReportPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategorySalesReportController extends Controller
{
    use ResolvesReportPeriod;

    public function index(Request $request): View
    {
        ['period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'label' => $label]
            = $this->resolvePeriod($request);

        $categories = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.order_date', [$dateFrom, $dateTo])
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.status', $this->excludedStatuses())
            ->groupBy('categories.id', 'categories.name')
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('COUNT(DISTINCT order_items.product_id) as product_count'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
            ])
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = (float) $categories->sum('revenue');
        $totalQty     = (int) $categories->sum('qty_sold');

        $categories = $categories->map(function ($row) use ($totalRevenue) {
            $row->revenue     = (float) $row->revenue;
            $row->qty_sold    = (int) $row->qty_sold;
            $row->order_count = (int) $row->order_count;
            $row->aov         = $row->order_count > 0 ? $row->revenue / $row->order_count : 0.0;
            $row->share       = $totalRevenue > 0 ? ($row->revenue / $totalRevenue) * 100 : 0.0;

            return $row;
        });

        $cms_content = [
            'page_title' => 'Category Sales',
            'module'     => 'Report',
            'sub_module' => 'Category Sales',
        ];

        return view('backend.modules.report.category-sales', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'categories', 'totalRevenue', 'totalQty',
            'request'
        ));
    }
}
