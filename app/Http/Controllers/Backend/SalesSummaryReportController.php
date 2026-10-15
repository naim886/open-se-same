<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesReportPeriod;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesSummaryReportController extends Controller
{
    use ResolvesReportPeriod;

    private const GRANULARITIES = [
        'day'   => 'Daily',
        'week'  => 'Weekly',
        'month' => 'Monthly',
    ];

    public function index(Request $request): View
    {
        ['period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'label' => $label]
            = $this->resolvePeriod($request);

        $granularity = $request->get('granularity', 'day');
        if (! array_key_exists($granularity, self::GRANULARITIES)) {
            $granularity = 'day';
        }

        $bucket = match ($granularity) {
            'week'  => "DATE_FORMAT(orders.order_date, '%x-W%v')",
            'month' => "DATE_FORMAT(orders.order_date, '%Y-%m')",
            default => 'DATE(orders.order_date)',
        };

        $commonFilters = function ($query) use ($dateFrom, $dateTo) {
            return $query
                ->whereBetween('orders.order_date', [$dateFrom, $dateTo])
                ->whereNull('orders.deleted_at')
                ->whereNotIn('orders.status', $this->excludedStatuses());
        };

        // Order-level totals. Aggregated separately from the item query because
        // joining order_items would multiply each order's total by its item count.
        $orderRows = $commonFilters(DB::table('orders'))
            ->groupBy(DB::raw($bucket))
            ->select([
                DB::raw($bucket . ' as bucket'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COUNT(DISTINCT orders.customer_phone) as customer_count'),
                DB::raw('SUM(orders.total_amount) as revenue'),
                DB::raw('SUM(orders.delivery_charge) as delivery_revenue'),
                DB::raw('SUM(orders.paid_amount) as paid'),
            ])
            ->get()
            ->keyBy('bucket');

        // Item-level quantities, product revenue and cost of goods.
        $itemRows = $commonFilters(
                DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            )
            ->groupBy(DB::raw($bucket))
            ->select([
                DB::raw($bucket . ' as bucket'),
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.subtotal) as product_revenue'),
                DB::raw('SUM(COALESCE(order_items.cost_price, products.cost, 0) * order_items.quantity) as cost_total'),
            ])
            ->get()
            ->keyBy('bucket');

        $buckets = $orderRows->keys()
            ->merge($itemRows->keys())
            ->unique()
            ->sort()
            ->values()
            ->map(function ($key) use ($orderRows, $itemRows) {
                $order = $orderRows->get($key);
                $item  = $itemRows->get($key);

                $revenue        = (float) ($order->revenue ?? 0);
                $productRevenue = (float) ($item->product_revenue ?? 0);
                $cost           = (float) ($item->cost_total ?? 0);
                $orders         = (int) ($order->order_count ?? 0);

                return (object) [
                    'bucket'           => $key,
                    'label'            => $this->formatBucket($key),
                    'order_count'      => $orders,
                    'customer_count'   => (int) ($order->customer_count ?? 0),
                    'qty_sold'         => (int) ($item->qty_sold ?? 0),
                    'revenue'          => $revenue,
                    'product_revenue'  => $productRevenue,
                    'delivery_revenue' => (float) ($order->delivery_revenue ?? 0),
                    'paid'             => (float) ($order->paid ?? 0),
                    'cost_total'       => $cost,
                    'profit'           => $productRevenue - $cost,
                    'aov'              => $orders > 0 ? $revenue / $orders : 0.0,
                ];
            });

        $totals = (object) [
            'order_count'      => (int) $buckets->sum('order_count'),
            'qty_sold'         => (int) $buckets->sum('qty_sold'),
            'revenue'          => (float) $buckets->sum('revenue'),
            'product_revenue'  => (float) $buckets->sum('product_revenue'),
            'delivery_revenue' => (float) $buckets->sum('delivery_revenue'),
            'paid'             => (float) $buckets->sum('paid'),
            'cost_total'       => (float) $buckets->sum('cost_total'),
            'profit'           => (float) $buckets->sum('profit'),
        ];
        $totals->aov = $totals->order_count > 0 ? $totals->revenue / $totals->order_count : 0.0;

        // Best day/week/month in the window, handy at a glance.
        $best = $buckets->sortByDesc('revenue')->first();

        $cms_content = [
            'page_title' => 'Sales Summary',
            'module'     => 'Report',
            'sub_module' => 'Sales Summary',
        ];

        return view('backend.modules.report.sales-summary', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'granularity', 'buckets', 'totals', 'best',
            'request'
        ))->with('granularityLabels', self::GRANULARITIES);
    }

    /**
     * Render a raw bucket key as a human-readable label.
     */
    private function formatBucket(string $key): string
    {
        if (preg_match('/^\d{4}-W\d{2}$/', $key)) {
            [$year, $week] = explode('-W', $key);

            return 'Week ' . (int) $week . ', ' . $year;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $key)) {
            return Carbon::createFromFormat('Y-m', $key)->format('F Y');
        }

        return Carbon::parse($key)->format('d M Y');
    }
}
