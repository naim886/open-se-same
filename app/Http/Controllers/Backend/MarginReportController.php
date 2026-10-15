<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesReportPeriod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarginReportController extends Controller
{
    use ResolvesReportPeriod;

    /**
     * Item cost uses the snapshot taken at sale time, falling back to the
     * product's current cost for orders created before snapshots existed.
     */
    private const COST_SQL = 'SUM(COALESCE(order_items.cost_price, products.cost, 0) * order_items.quantity)';

    private const MISSING_COST_SQL = 'SUM(CASE WHEN COALESCE(order_items.cost_price, products.cost) IS NULL THEN 1 ELSE 0 END)';

    public function index(Request $request): View
    {
        ['period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'label' => $label]
            = $this->resolvePeriod($request);

        $base = fn () => DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.order_date', [$dateFrom, $dateTo])
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.status', $this->excludedStatuses());

        $byProduct = $base()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('order_items.product_id', 'products.name', 'categories.name')
            ->select([
                'order_items.product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
                DB::raw(self::COST_SQL . ' as cost_total'),
                DB::raw(self::MISSING_COST_SQL . ' as lines_without_cost'),
            ])
            ->get();

        $byCategory = $base()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
                DB::raw(self::COST_SQL . ' as cost_total'),
                DB::raw(self::MISSING_COST_SQL . ' as lines_without_cost'),
            ])
            ->get();

        $enrich = function ($rows) {
            $rows = $rows->map(function ($row) {
                $row->revenue     = (float) $row->revenue;
                $row->cost_total  = (float) $row->cost_total;
                $row->qty_sold    = (int) $row->qty_sold;
                $row->profit      = $row->revenue - $row->cost_total;
                $row->margin_pct  = $row->revenue > 0 ? ($row->profit / $row->revenue) * 100 : 0.0;
                $row->lines_without_cost = (int) $row->lines_without_cost;

                return $row;
            });

            return $rows->sortByDesc('profit')->values();
        };

        $byProduct  = $enrich($byProduct);
        $byCategory = $enrich($byCategory);

        $totalRevenue = (float) $byProduct->sum('revenue');
        $totalCost    = (float) $byProduct->sum('cost_total');
        $totalProfit  = $totalRevenue - $totalCost;
        $totalMargin  = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0.0;
        $totalQty     = (int) $byProduct->sum('qty_sold');
        $missingCost  = (int) $byProduct->sum('lines_without_cost');

        $cms_content = [
            'page_title' => 'Profit & Margin',
            'module'     => 'Report',
            'sub_module' => 'Profit & Margin',
        ];

        return view('backend.modules.report.margin', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'byProduct', 'byCategory',
            'totalRevenue', 'totalCost', 'totalProfit', 'totalMargin', 'totalQty',
            'missingCost',
            'request'
        ));
    }
}
