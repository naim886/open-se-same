<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesReportController extends Controller
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
            default: // this_month
                $period   = 'this_month';
                $dateFrom = $today->copy()->startOfMonth();
                $dateTo   = $today->copy()->endOfMonth();
                $label    = $today->format('F Y');
        }

        // ── Raw pivot: (user_id, status) → count + revenue ───────────────
        $rows = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->select(
                'orders.user_id',
                'users.name as user_name',
                'orders.status',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('SUM(orders.total_amount) as revenue'),
                DB::raw('SUM(orders.paid_amount) as paid')
            )
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('orders.user_id', 'users.name', 'orders.status')
            ->orderBy('users.name')
            ->get();

        // ── Pivot into [ userId => [label, total, revenue, paid, statuses[]] ] ──
        $statusIds   = array_keys(Order::STATUS_LIST);
        $userReport  = [];   // keyed by user_id (null → 'System')

        $excludeFromRevenue = [Order::STATUS_CANCELLED, Order::STATUS_RETURNED];

        foreach ($rows as $row) {
            $key   = $row->user_id ?? 'system';
            $label_user = $row->user_name ?? 'System';

            if (!isset($userReport[$key])) {
                $userReport[$key] = [
                    'label'    => $label_user,
                    'total'    => 0,
                    'revenue'  => 0.0,
                    'paid'     => 0.0,
                    'statuses' => array_fill_keys($statusIds, 0),
                ];
            }

            $userReport[$key]['total']                  += (int)$row->cnt;
            $userReport[$key]['statuses'][$row->status]  = (int)$row->cnt;

            // Exclude cancelled & returned from revenue so Due/Unpaid is meaningful
            if (!in_array($row->status, $excludeFromRevenue, true)) {
                $userReport[$key]['revenue'] += (float)$row->revenue;
                $userReport[$key]['paid']    += (float)$row->paid;
            }
        }

        // Sort: System last
        uasort($userReport, fn ($a, $b) =>
            ($a['label'] === 'System' ? 1 : 0) - ($b['label'] === 'System' ? 1 : 0)
            ?: strcmp($a['label'], $b['label'])
        );

        // ── Grand totals ──────────────────────────────────────────────────
        $grandTotal   = 0;
        $grandRevenue = 0.0;
        $grandPaid    = 0.0;
        $grandStatuses = array_fill_keys($statusIds, 0);

        foreach ($userReport as $u) {
            $grandTotal   += $u['total'];
            $grandRevenue += $u['revenue'];
            $grandPaid    += $u['paid'];
            foreach ($statusIds as $sid) {
                $grandStatuses[$sid] += $u['statuses'][$sid];
            }
        }

        $cms_content = [
            'page_title' => 'Sales Report',
            'module'     => 'Report',
            'sub_module' => 'Sales',
        ];

        return view('backend.modules.report.sales', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'userReport',
            'grandTotal', 'grandRevenue', 'grandPaid', 'grandStatuses',
            'request'
        ));
    }
}
