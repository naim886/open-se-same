<?php

namespace App\Http\Controllers\Backend\Concerns;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Shared period-window resolution for the reporting screens.
 */
trait ResolvesReportPeriod
{
    /**
     * Order statuses that never count towards revenue.
     *
     * @return array<int, int>
     */
    protected function excludedStatuses(): array
    {
        return [Order::STATUS_CANCELLED, Order::STATUS_RETURNED];
    }

    /**
     * Resolve the reporting window from the request.
     *
     * @return array{period: string, dateFrom: Carbon, dateTo: Carbon, label: string}
     */
    protected function resolvePeriod(Request $request): array
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
            case 'last_30':
                $dateFrom = $today->copy()->subDays(29)->startOfDay();
                $dateTo   = $today->copy()->endOfDay();
                $label    = 'Last 30 Days';
                break;
            case 'last_month':
                $dateFrom = $today->copy()->subMonthNoOverflow()->startOfMonth();
                $dateTo   = $today->copy()->subMonthNoOverflow()->endOfMonth();
                $label    = $dateFrom->format('F Y');
                break;
            case 'this_year':
                $dateFrom = $today->copy()->startOfYear();
                $dateTo   = $today->copy()->endOfYear();
                $label    = $today->format('Y');
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

        return compact('period', 'dateFrom', 'dateTo', 'label');
    }
}
