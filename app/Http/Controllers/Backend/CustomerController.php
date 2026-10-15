<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CustomerNote;
use App\Models\CustomerTag;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public const SOURCE_LIST = [
        'website'    => 'Website',
        'facebook'   => 'Facebook',
        'instagram'  => 'Instagram',
        'tiktok'     => 'TikTok',
        'whatsapp'   => 'WhatsApp',
        'referral'   => 'Referral',
        'phone_call' => 'Phone Call',
        'walk_in'    => 'Walk-in',
        'mobile_app' => 'Mobile App',
        'other'      => 'Other',
    ];

    public const SOURCE_COLORS = [
        'website'    => '#0d6efd',
        'facebook'   => '#1877f2',
        'instagram'  => '#e1306c',
        'tiktok'     => '#010101',
        'whatsapp'   => '#25d366',
        'referral'   => '#20c997',
        'phone_call' => '#fd7e14',
        'walk_in'    => '#6f42c1',
        'mobile_app' => '#0dcaf0',
        'other'      => '#adb5bd',
    ];

    // ── Index: customers aggregated from orders by customer_phone ─────────
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $source = $request->input('source');
        $sort   = $request->input('sort', 'last_order');
        $tagId  = $request->integer('tag') ?: null;
        $repeat = $request->boolean('repeat');

        // Orders needed before a customer counts as a repeat buyer.
        $repeatThreshold = max(2, Setting::int('crm.repeat_min_orders'));

        // Base query: one row per unique customer_phone
        $query = DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select([
                'orders.customer_phone',
                DB::raw('MAX(orders.customer_name)  as customer_name'),
                DB::raw('MAX(orders.customer_email) as customer_email'),
                DB::raw('COUNT(orders.id)            as order_count'),
                DB::raw('SUM(orders.total_amount)    as total_spent'),
                DB::raw('MIN(orders.order_date)      as first_order_at'),
                DB::raw('MAX(orders.order_date)      as last_order_at'),
                DB::raw('MAX(orders.source)          as source'),
                DB::raw('(
                    SELECT COALESCE(SUM(oi.quantity), 0)
                    FROM order_items oi
                    INNER JOIN orders o2 ON o2.id = oi.order_id
                    WHERE o2.customer_phone = orders.customer_phone
                      AND o2.deleted_at IS NULL
                ) as total_products_ordered'),
            ])
            ->groupBy('orders.customer_phone');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('orders.customer_name', 'like', "%{$search}%")
                  ->orWhere('orders.customer_phone', 'like', "%{$search}%")
                  ->orWhere('orders.customer_email', 'like', "%{$search}%");
            });
        }

        // Source filter
        if ($source && array_key_exists($source, self::SOURCE_LIST)) {
            $query->where('orders.source', $source);
        }

        // Tag filter
        if ($tagId && CustomerTag::whereKey($tagId)->exists()) {
            $query->whereIn('orders.customer_phone', function ($sub) use ($tagId) {
                $sub->select('phone')->from('customer_tag_assignments')
                    ->where('customer_tag_id', $tagId);
            });
        }

        // Repeat-buyer filter
        if ($repeat) {
            $query->havingRaw('COUNT(orders.id) >= ?', [$repeatThreshold]);
        }

        // Sort
        match ($sort) {
            'most_orders'   => $query->orderByDesc('order_count'),
            'most_spent'    => $query->orderByDesc('total_spent'),
            'most_products' => $query->orderByDesc('total_products_ordered'),
            'first_order'   => $query->orderBy('first_order_at'),
            default         => $query->orderByDesc('last_order_at'),
        };

        $customers    = $query->paginate(20)->withQueryString();
        $sourceList   = self::SOURCE_LIST;
        $sourceColors = self::SOURCE_COLORS;

        // Tags for the current page of customers, in one extra query.
        $customerTags = CustomerTag::forPhones($customers->pluck('customer_phone')->all());
        $allTags      = CustomerTag::orderBy('name')->get(['id', 'name', 'color']);

        // Summary stats
        $totals = DB::table('orders')
            ->whereNull('deleted_at')
            ->select([
                DB::raw('COUNT(DISTINCT customer_phone) as total_customers'),
                DB::raw('COUNT(id)                      as total_orders'),
            ])
            ->first();

        $totalCustomers  = $totals->total_customers ?? 0;
        $totalOrders     = $totals->total_orders ?? 0;

        $repeatCustomers = DB::table(DB::raw(
            '(SELECT customer_phone FROM orders WHERE deleted_at IS NULL GROUP BY customer_phone HAVING COUNT(id) >= '
            . (int) $repeatThreshold . ') as sub'
        ))->count();

        $repeatRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0.0;

        return view('backend.modules.customer.index', compact(
            'customers', 'sourceList', 'sourceColors',
            'totalCustomers', 'totalOrders', 'repeatCustomers', 'repeatRate',
            'customerTags', 'allTags', 'tagId', 'repeat', 'repeatThreshold'
        ));
    }

    // ── Show: all orders for a given customer phone ───────────────────────
    public function show(Request $request, string $phone): View
    {
        // Customer base info (built from their latest order data)
        $customer = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('customer_phone', $phone)
            ->selectRaw('customer_phone, MAX(customer_name) as customer_name, MAX(customer_email) as customer_email')
            ->groupBy('customer_phone')
            ->first();

        abort_if(!$customer, 404);

        // All orders for this phone
        $orders = Order::where('customer_phone', $phone)
            ->withCount('orderItems as items_count')
            ->orderByDesc('order_date')
            ->get();

        // Source distribution across their orders
        $sourceDist = $orders->groupBy(fn ($o) => $o->source ?? 'website')
            ->map(fn ($grp) => $grp->count())
            ->sortByDesc(fn ($v) => $v);

        // Computed stats
        $stats = (object) [
            'order_count'     => $orders->count(),
            'total_products'  => $orders->sum('items_count'),
            'total_spent'     => $orders->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])
                                        ->sum('total_amount'),
            'total_paid'      => $orders->sum('paid_amount'),
            'delivered_count' => $orders->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_PARTIAL_DELIVERED])->count(),
            'cancelled_count' => $orders->where('status', Order::STATUS_CANCELLED)->count(),
            'returned_count'  => $orders->where('status', Order::STATUS_RETURNED)->count(),
            'first_order_at'  => $orders->min('order_date'),
            'last_order_at'   => $orders->max('order_date'),
        ];

        $sourceList   = self::SOURCE_LIST;
        $sourceColors = self::SOURCE_COLORS;

        // CRM: tags, notes and repeat status
        $allTags         = CustomerTag::orderBy('name')->get(['id', 'name', 'color']);
        $assignedTags    = CustomerTag::forPhones([$phone])[$phone] ?? [];
        $assignedTagIds  = array_column($assignedTags, 'id');
        $notes           = CustomerNote::with('author')->where('phone', $phone)
                                ->orderByDesc('created_at')->get();
        $repeatThreshold = max(2, Setting::int('crm.repeat_min_orders'));
        $isRepeat        = $stats->order_count >= $repeatThreshold;

        return view('backend.modules.customer.show', compact(
            'customer', 'orders', 'stats', 'sourceList', 'sourceColors', 'sourceDist',
            'allTags', 'assignedTags', 'assignedTagIds', 'notes', 'isRepeat', 'repeatThreshold'
        ));
    }

    // ── Tags: attach a tag to a customer ─────────────────────────────────
    public function attachTag(Request $request, string $phone): RedirectResponse
    {
        $validated = $request->validate([
            'customer_tag_id' => ['required', 'integer', 'exists:customer_tags,id'],
        ]);

        $alreadyAssigned = DB::table('customer_tag_assignments')
            ->where('phone', $phone)
            ->where('customer_tag_id', $validated['customer_tag_id'])
            ->exists();

        if (! $alreadyAssigned) {
            DB::table('customer_tag_assignments')->insert([
                'phone'            => $phone,
                'customer_tag_id'  => $validated['customer_tag_id'],
                'assigned_by'      => auth()->id(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        return back()->with('success', 'Tag added.');
    }

    // ── Tags: remove a tag from a customer ───────────────────────────────
    public function detachTag(string $phone, int $tagId): RedirectResponse
    {
        DB::table('customer_tag_assignments')
            ->where('phone', $phone)
            ->where('customer_tag_id', $tagId)
            ->delete();

        return back()->with('success', 'Tag removed.');
    }

    // ── Notes: add an internal note ──────────────────────────────────────
    public function storeNote(Request $request, string $phone): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        CustomerNote::create([
            'phone'   => $phone,
            'user_id' => auth()->id(),
            'note'    => $validated['note'],
        ]);

        return back()->with('success', 'Note added.');
    }

    // ── Notes: delete an internal note ───────────────────────────────────
    public function destroyNote(string $phone, int $noteId): RedirectResponse
    {
        CustomerNote::where('phone', $phone)->where('id', $noteId)->delete();

        return back()->with('success', 'Note deleted.');
    }

    // ── Update source on a single order ──────────────────────────────────
    public function updateOrderSource(Request $request, Order $order)
    {
        $request->validate([
            'source' => ['required', 'in:' . implode(',', array_keys(self::SOURCE_LIST))],
        ]);

        $order->update(['source' => $request->source]);

        return back()->with('success', 'Order source updated.');
    }

    // ── Export: CSV ───────────────────────────────────────────────────────
    public function export(Request $request): Response
    {
        $search = $request->input('search');
        $source = $request->input('source');

        $customers = DB::table('orders')
            ->whereNull('orders.deleted_at')
            ->select([
                'orders.customer_phone',
                DB::raw('MAX(orders.customer_name)  as customer_name'),
                DB::raw('MAX(orders.customer_email) as customer_email'),
                DB::raw('COUNT(orders.id)            as order_count'),
                DB::raw('SUM(orders.total_amount)    as total_spent'),
                DB::raw('MIN(orders.order_date)      as first_order_at'),
                DB::raw('MAX(orders.order_date)      as last_order_at'),
                DB::raw('MAX(orders.source)          as source'),
                DB::raw('(
                    SELECT COALESCE(SUM(oi.quantity), 0)
                    FROM order_items oi
                    INNER JOIN orders o2 ON o2.id = oi.order_id
                    WHERE o2.customer_phone = orders.customer_phone
                      AND o2.deleted_at IS NULL
                ) as total_qty'),
            ])
            ->groupBy('orders.customer_phone')
            ->when($search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('orders.customer_name', 'like', "%{$search}%")
                   ->orWhere('orders.customer_phone', 'like', "%{$search}%")
                   ->orWhere('orders.customer_email', 'like', "%{$search}%")
            ))
            ->when($source && array_key_exists($source, self::SOURCE_LIST),
                fn ($q) => $q->where('orders.source', $source))
            ->orderByDesc('last_order_at')
            ->get();

        // ── Bulk-fetch product details per customer (one query, no N+1) ──
        $phones = $customers->pluck('customer_phone')->filter()->values()->all();

        // Group by customer_phone + product_name, sum quantities
        $productRows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNull('o.deleted_at')
            ->whereIn('o.customer_phone', $phones)
            ->whereNotNull('oi.product_name')
            ->select([
                'o.customer_phone',
                'oi.product_name',
                DB::raw('SUM(oi.quantity) as total_qty'),
            ])
            ->groupBy('o.customer_phone', 'oi.product_name')
            ->orderBy('o.customer_phone')
            ->orderByDesc(DB::raw('SUM(oi.quantity)'))
            ->get();

        // Build a map: phone -> "Product A x3; Product B x1; ..."
        $productMap = [];
        foreach ($productRows as $row) {
            $productMap[$row->customer_phone][] = $row->product_name . ' x' . (int) $row->total_qty;
        }
        foreach ($productMap as $phone => $items) {
            $productMap[$phone] = implode('; ', $items);
        }

        $sourceList = self::SOURCE_LIST;

        $csv  = "Name,Phone,Email,Source,Total Orders,Total Qty,Total Spent (BDT),First Order,Last Order,Products Ordered\r\n";
        foreach ($customers as $c) {
            $productList = $productMap[$c->customer_phone] ?? '';
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $c->customer_name ?? '') . '"',
                '"' . ($c->customer_phone ?? '') . '"',
                '"' . ($c->customer_email ?? '') . '"',
                '"' . ($sourceList[$c->source ?? 'website'] ?? 'Website') . '"',
                (int) $c->order_count,
                (int) $c->total_qty,
                number_format((float) $c->total_spent, 2, '.', ''),
                '"' . ($c->first_order_at ? Carbon::parse($c->first_order_at)->format('d M Y') : '') . '"',
                '"' . ($c->last_order_at  ? Carbon::parse($c->last_order_at)->format('d M Y')  : '') . '"',
                '"' . str_replace('"', '""', $productList) . '"',
            ]) . "\r\n";
        }

        $filename = 'customers_' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
