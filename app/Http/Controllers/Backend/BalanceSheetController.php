<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceSheetController extends Controller
{
    public function index(Request $request): View
    {
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

        $excludeStatuses = [Order::STATUS_CANCELLED, Order::STATUS_RETURNED];

        // ── Income (orders, excl. cancelled/returned) ───────────────────
        $orderQuery = Order::whereBetween('order_date', [$dateFrom, $dateTo])
                           ->whereNotIn('status', $excludeStatuses);

        $totalRevenue   = (float) (clone $orderQuery)->sum('total_amount');
        $collectedAmount = (float) (clone $orderQuery)->sum('paid_amount');
        $dueAmount      = $totalRevenue - $collectedAmount;
        $totalOrders    = (clone $orderQuery)->count();

        // Order status breakdown
        $statusBreakdown = (clone $orderQuery)
            ->selectRaw('status, COUNT(*) as cnt, SUM(total_amount) as revenue, SUM(paid_amount) as paid')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // ── Expenses ─────────────────────────────────────────────────────
        $expQuery    = Expense::whereBetween('date', [$dateFrom, $dateTo])
                              ->where('status', Expense::STATUS_APPROVED);
        $totalExpenses = (float) (clone $expQuery)->sum('amount');

        $expByCategory = (clone $expQuery)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // ── Account Balances (all-time, not period filtered) ─────────────
        $accountBalances = [];
        $totalAccountBalance = 0.0;
        foreach (AccountTransaction::ACCOUNT_LIST as $key => $label_acc) {
            $bal = AccountTransaction::balanceFor($key);
            $accountBalances[$key] = $bal;
            $totalAccountBalance  += $bal;
        }

        // ── Net P&L ───────────────────────────────────────────────────────
        $grossProfit = $collectedAmount - $totalExpenses;

        $cms_content = [
            'page_title' => 'Balance Sheet',
            'module'     => 'Report',
            'sub_module' => 'Balance Sheet',
        ];

        return view('backend.modules.report.balance-sheet', compact(
            'cms_content', 'period', 'dateFrom', 'dateTo', 'label',
            'totalRevenue', 'collectedAmount', 'dueAmount', 'totalOrders',
            'statusBreakdown', 'totalExpenses', 'expByCategory',
            'accountBalances', 'totalAccountBalance', 'grossProfit',
            'request'
        ));
    }
}
