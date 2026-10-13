<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommissionReportController extends Controller
{
    /** Commission per delivered order (BDT) */
    const COMMISSION_PER_DELIVERY = 10;

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

        // ── Raw pivot: (user_id, status) → count ─────────────────────────
        // Exclude null user_id (System) from commission — they get their own row but 0 commission
        $rows = Order::whereBetween('order_date', [$dateFrom, $dateTo])
            ->select(
                'orders.user_id',
                'users.name as user_name',
                'orders.status',
                DB::raw('COUNT(*) as cnt')
            )
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('orders.user_id', 'users.name', 'orders.status')
            ->orderBy('users.name')
            ->get();

        $statusIds  = array_keys(Order::STATUS_LIST);
        $userReport = [];

        foreach ($rows as $row) {
            $key       = $row->user_id ?? 'system';
            $labelUser = $row->user_name ?? 'System';

            if (!isset($userReport[$key])) {
                $userReport[$key] = [
                    'label'      => $labelUser,
                    'is_system'  => $row->user_id === null,
                    'total'      => 0,
                    'statuses'   => array_fill_keys($statusIds, 0),
                    'delivered'  => 0,
                    'commission' => 0,
                ];
            }

            $userReport[$key]['total']                  += (int)$row->cnt;
            $userReport[$key]['statuses'][$row->status]  = (int)$row->cnt;

            if ($row->status === Order::STATUS_DELIVERED) {
                $userReport[$key]['delivered'] += (int)$row->cnt;
            }
            // Also count partial delivered towards commission
            if ($row->status === Order::STATUS_PARTIAL_DELIVERED) {
                $userReport[$key]['delivered'] += (int)$row->cnt;
            }
        }

        // Calculate commission per user
        foreach ($userReport as &$u) {
            $u['commission'] = $u['is_system'] ? 0 : ($u['delivered'] * self::COMMISSION_PER_DELIVERY);
        }
        unset($u);

        // Sort: real users first (alphabetical), System last
        uasort($userReport, fn ($a, $b) =>
            ($a['is_system'] ? 1 : 0) - ($b['is_system'] ? 1 : 0)
            ?: strcmp($a['label'], $b['label'])
        );

        // ── Grand totals ──────────────────────────────────────────────────
        $grandTotal       = 0;
        $grandDelivered   = 0;
        $grandCommission  = 0;
        $grandStatuses    = array_fill_keys($statusIds, 0);

        foreach ($userReport as $u) {
            $grandTotal      += $u['total'];
            $grandDelivered  += $u['delivered'];
            $grandCommission += $u['commission'];
            foreach ($statusIds as $sid) {
                $grandStatuses[$sid] += $u['statuses'][$sid];
            }
        }

        $cms_content = [
            'page_title' => 'Commission Report',
            'module'     => 'Report',
            'sub_module' => 'Commission',
        ];

        return view('backend.modules.report.commission', compact(
            'cms_content',
            'period', 'dateFrom', 'dateTo', 'label',
            'userReport',
            'grandTotal', 'grandDelivered', 'grandCommission', 'grandStatuses',
            'request'
        ));
    }
}
