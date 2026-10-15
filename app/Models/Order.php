<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderHistory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'order_date'    => 'datetime',
        'delivery_date' => 'datetime',
    ];

    // Order Status Constants
    public const STATUS_PENDING        = 1;
    public const STATUS_CONFIRMED      = 2;
    public const STATUS_COURIER_ENTRY  = 3;
    public const STATUS_PROCESSING     = 4;
    public const STATUS_SHIPPED        = 5;
    public const STATUS_DELIVERED      = 6;
    public const STATUS_CANCELLED      = 7;
    public const STATUS_RETURNED       = 8;
    public const STATUS_COURIER_PENDING  = 9;  // Courier received parcel, pending delivery
    public const STATUS_PARTIAL_DELIVERED = 10; // Partially delivered by courier

    public const STATUS_LIST = [
        self::STATUS_PENDING          => 'Pending',           // order placed
        self::STATUS_CONFIRMED        => 'Confirmed',         // confirmed via phone
        self::STATUS_COURIER_ENTRY    => 'Courier Entry',     // sent to Steadfast
        self::STATUS_COURIER_PENDING  => 'Courier Pending',   // courier has it, awaiting delivery
        self::STATUS_PROCESSING       => 'Processing',        // packaging
        self::STATUS_SHIPPED          => 'Shipped to courier',// physically handed to courier
        self::STATUS_DELIVERED        => 'Delivered',         // delivered to customer
        self::STATUS_PARTIAL_DELIVERED => 'Partial Delivered', // partially delivered
        self::STATUS_CANCELLED        => 'Cancelled',         // cancelled
        self::STATUS_RETURNED         => 'Returned',          // returned
    ];

    public const STATUS_COLORS = [
        self::STATUS_PENDING           => 'warning',
        self::STATUS_CONFIRMED         => 'info',
        self::STATUS_PROCESSING        => 'primary',
        self::STATUS_SHIPPED           => 'secondary',
        self::STATUS_DELIVERED         => 'success',
        self::STATUS_PARTIAL_DELIVERED => 'warning',
        self::STATUS_CANCELLED         => 'danger',
        self::STATUS_COURIER_ENTRY     => 'secondary',
        self::STATUS_COURIER_PENDING   => 'info',
        self::STATUS_RETURNED          => 'dark',
    ];

    public const DELIVERY_INSIDE_DHAKA  = 1;
    public const DELIVERY_OUTSIDE_DHAKA = 2;
    public const DELIVERY_EXPRESS       = 3;
    public const DELIVERY_SUPER_EXPRESS = 4;
    public const DELIVERY_FREE          = 5;

    public const DELIVERY_LIST = [
        self::DELIVERY_INSIDE_DHAKA  => 'Inside Dhaka',
        self::DELIVERY_OUTSIDE_DHAKA => 'Outside Dhaka',
        self::DELIVERY_EXPRESS       => 'Express',
        self::DELIVERY_SUPER_EXPRESS => 'Super Express',
        self::DELIVERY_FREE          => 'Free Delivery',
    ];

    // Fallback charges (Taka), used only when a value has not been configured
    // in Admin → Settings. Runtime reads go through deliveryCostList().
    public const DELIVERY_INSIDE_DHAKA_COST  = 70;
    public const DELIVERY_OUTSIDE_DHAKA_COST = 120;
    public const DELIVERY_EXPRESS_COST       = 150;
    public const DELIVERY_SUPER_EXPRESS_COST = 0;
    public const DELIVERY_FREE_COST          = 0;

    public const DELIVERY_COST_LIST = [
        self::DELIVERY_INSIDE_DHAKA  => self::DELIVERY_INSIDE_DHAKA_COST,
        self::DELIVERY_OUTSIDE_DHAKA => self::DELIVERY_OUTSIDE_DHAKA_COST,
        self::DELIVERY_EXPRESS       => self::DELIVERY_EXPRESS_COST,
        self::DELIVERY_SUPER_EXPRESS => self::DELIVERY_SUPER_EXPRESS_COST,
        self::DELIVERY_FREE          => self::DELIVERY_FREE_COST,
    ];

    /** Settings key holding the admin-configured charge for each delivery area. */
    public const DELIVERY_SETTING_KEYS = [
        self::DELIVERY_INSIDE_DHAKA  => 'delivery.inside_dhaka',
        self::DELIVERY_OUTSIDE_DHAKA => 'delivery.outside_dhaka',
        self::DELIVERY_EXPRESS       => 'delivery.express',
        self::DELIVERY_SUPER_EXPRESS => 'delivery.super_express',
        self::DELIVERY_FREE          => 'delivery.free',
    ];

    /**
     * Admin-configured delivery charges, keyed by delivery area.
     *
     * These are what the customer is actually charged, so every order-creation
     * path must use this rather than the DELIVERY_COST_LIST constants.
     */
    public static function deliveryCostList(): array
    {
        $costs = [];

        foreach (self::DELIVERY_SETTING_KEYS as $location => $settingKey) {
            $costs[$location] = Setting::float($settingKey);
        }

        return $costs;
    }

    /**
     * Charge for a single delivery area, falling back to Inside Dhaka for an
     * unknown area so an order can never be created with a null charge.
     */
    public static function deliveryCost(int|string|null $location): float
    {
        $costs    = self::deliveryCostList();
        $location = (int) $location;

        return (float) ($costs[$location] ?? $costs[self::DELIVERY_INSIDE_DHAKA] ?? 0);
    }

    // Customer-facing delivery options (cart/checkout/quick-order only offer these two)
    public const DELIVERY_CUSTOMER_LIST = [
        self::DELIVERY_INSIDE_DHAKA  => 'Inside Dhaka',
        self::DELIVERY_OUTSIDE_DHAKA => 'Outside Dhaka',
    ];

    // Payment Method Constants
    public const PAYMENT_METHOD_COD    = 1;
    public const PAYMENT_METHOD_ONLINE = 2;

    public const PAYMENT_METHOD_LIST = [
        self::PAYMENT_METHOD_COD    => 'Cash on Delivery',
        self::PAYMENT_METHOD_ONLINE => 'Online Payment',
    ];

    // Payment Status Constants
    public const PAYMENT_STATUS_UNPAID = 1;
    public const PAYMENT_STATUS_PAID   = 2;

    public const PAYMENT_STATUS_LIST = [
        self::PAYMENT_STATUS_UNPAID => 'Unpaid',
        self::PAYMENT_STATUS_PAID   => 'Paid',
    ];


    /**
     * Get all orders with pagination
     */
    final public function getOrders(Request $request): LengthAwarePaginator
    {
        $query = self::query()
            ->with(['orderItems', 'user', 'orderItems.product'])
            ->orderBy('order_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('delivery_location')) {
            $query->where('delivery_location', $request->get('delivery_location'));
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->get('customer_name') . '%');
        }
        if ($request->filled('customer_phone')) {
            $query->where('customer_phone', 'like', '%' . $request->get('customer_phone') . '%');
        }
        if ($request->filled('order_number')) {
            $query->where('order_number', $request->get('order_number'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->get('date_to'));
        }
        if ($request->filled('paid_status')) {
            if ($request->get('paid_status') === 'paid') {
                $query->whereColumn('total_amount', 'paid_amount');
            } elseif ($request->get('paid_status') === 'unpaid') {
                $query->whereColumn('total_amount', '!=', 'paid_amount');
            }
        }
        if ($request->filled('created_by')) {
            $query->where('user_id', $request->get('created_by'));
        }
        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }
        if ($request->filled('product_id')) {
            $query->whereHas('orderItems', function ($q) use ($request) {
                $q->where('product_id', $request->get('product_id'));
            });
        }

        return $query->paginate(GlobalConstants::DEFAULT_PAGINATION)->withQueryString();
    }

    /**
     * Resolve the unit cost for each order line, preferring the variation's
     * cost and falling back to the parent product's cost.
     *
     * @param  array<int, mixed>  $productIds
     * @param  array<int, mixed>  $variationIds
     * @return array<int, float|null> Keyed by line index.
     */
    private function resolveItemCosts(array $productIds, array $variationIds): array
    {
        $productIds   = array_values($productIds);
        $variationIds = array_values($variationIds);

        $cleanProductIds   = array_filter(array_map('intval', $productIds));
        $cleanVariationIds = array_filter(array_map('intval', $variationIds));

        $productCosts = $cleanProductIds
            ? Product::query()->whereIn('id', $cleanProductIds)->pluck('cost', 'id')->all()
            : [];

        $variationCosts = $cleanVariationIds
            ? ProductVariation::query()->whereIn('id', $cleanVariationIds)->pluck('cost', 'id')->all()
            : [];

        $costs = [];

        foreach ($productIds as $idx => $prodId) {
            $variationId = (int) ($variationIds[$idx] ?? 0);
            $cost        = null;

            if ($variationId && array_key_exists($variationId, $variationCosts)) {
                $cost = $variationCosts[$variationId];
            } elseif (array_key_exists((int) $prodId, $productCosts)) {
                $cost = $productCosts[(int) $prodId];
            }

            $costs[$idx] = ($cost === null || $cost === '') ? null : (float) $cost;
        }

        return $costs;
    }

    /**
     * Store a new order with multiple items.
     *
     * @param array $payload Validated payload from controller
     *   Expected keys:
     *     - product_ids (array)
     *     - quantities (array)
     *     - unit_prices (array)
     *     - product_price (numeric)  // products total
     *     - delivery_charge (numeric)
     *     - paid_amount (numeric)
     *     - order_total (numeric)
     *     - customer_name, customer_phone, customer_address, customer_email, note, status
     * @param int|null $userId
     * @return Model
     */
    final public function storeOrder(array $payload, ?int $userId = null): Model
    {
        // Build order-level data
        $orderData = [
            'order_number'      => $this->generateOrderNumber(),
            'user_id'           => $userId,
            'customer_name'     => $payload['customer_name'] ?? null,
            'customer_phone'    => $payload['customer_phone'] ?? null,
            'customer_address'  => $payload['customer_address'] ?? null,
            'customer_email'    => $payload['customer_email'] ?? null,
            'product_price'     => isset($payload['product_price']) ? (float)$payload['product_price'] : 0.0,
            'coupon_code'       => $payload['coupon_code'] ?? null,
            'discount_amount'   => isset($payload['discount_amount']) ? (float)$payload['discount_amount'] : 0.0,
            'delivery_location' => $payload['delivery_location'] ?? null,
            'delivery_charge'   => isset($payload['delivery_charge']) ? (float)$payload['delivery_charge'] : 0.0,
            'payment_method'    => $payload['payment_method'] ?? self::PAYMENT_METHOD_COD,
            'payment_status'    => $payload['payment_status'] ?? self::PAYMENT_STATUS_UNPAID,
            'paid_amount'       => isset($payload['paid_amount']) ? (float)$payload['paid_amount'] : 0.0,
            'total_amount'      => isset($payload['order_total']) ? (float)$payload['order_total'] : 0.0,
            'status'            => $payload['status'] ?? self::STATUS_PENDING,
            'order_date'        => now(),
            'notes'             => $payload['note'] ?? null,
            'source'            => $payload['source'] ?? 'facebook',
        ];

        // Create order row
        $order = self::query()->create($orderData);

        // Save items into separate table (no enforced FK to products)
        $productIds      = $payload['product_ids']       ?? [];
        $quantities      = $payload['quantities']        ?? [];
        $unitPrices      = $payload['unit_prices']       ?? [];
        $productNames    = $payload['product_names']     ?? [];
        $variationIds    = $payload['variation_ids']     ?? [];
        $variationLabels = $payload['variation_labels']  ?? [];

        $itemCosts = $this->resolveItemCosts($productIds, $variationIds);

        $items = [];
        foreach ($productIds as $idx => $prodId) {
            $qty      = isset($quantities[$idx]) ? (int)$quantities[$idx] : 1;
            $unit     = isset($unitPrices[$idx]) ? (float)$unitPrices[$idx] : 0.0;
            $subtotal = $qty * $unit;

            $items[] = [
                'order_id'        => $order->id,
                'product_id'      => (int)$prodId,
                'variation_id'    => $variationIds[$idx] ?? null,
                'variation_label' => $variationLabels[$idx] ?? null,
                'product_name'    => $productNames[$idx] ?? null,
                'unit_price'      => $unit,
                'cost_price'      => $itemCosts[$idx] ?? null,
                'quantity'        => $qty,
                'subtotal'        => $subtotal,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        if (!empty($items)) {
            // insert many
            DB::table('order_items')->insert($items);

            // Decrement product/variation stock for each item
            try {
                DB::beginTransaction();
                foreach ($items as $it) {
                    try {
                        $qty = (int)$it['quantity'];
                        $variationId = $it['variation_id'] ?? null;
                        if ($variationId) {
                            DB::table('product_variations')
                                ->where('id', $variationId)
                                ->where('stock', '>', 0)
                                ->decrement('stock', $qty);
                            DB::table('product_variations')
                                ->where('id', $variationId)
                                ->where('stock', '<', 0)
                                ->update(['stock' => 0]);
                        } else {
                            $productId = $it['product_id'];
                            $product = Product::find($productId);
                            if ($product && isset($product->stock)) {
                                $newStock = max(0, (int)$product->stock - $qty);
                                $product->stock = $newStock;
                                $product->save();

                                app(\App\Services\OrderNotificationService::class)->alertIfLowStock($product);
                            }
                        }
                    } catch (\Throwable $e) {
                        app_error_log('Order Stock Decrement Item', $e);
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                app_error_log('Order Stock Decrement', $e);
            }
        }

        // create initial history entry for the created order
        try {
            $itemsNew = DB::table('order_items')->where('order_id', $order->id)->get(['product_id','quantity','unit_price'])->map(function($r){
                return (array)$r;
            })->all();

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'action' => 'created',
                'previous' => null,
                'changes' => $orderData,
                'items_previous' => null,
                'items_new' => $itemsNew,
            ]);

            // Save note to order_notes if provided
            if (!empty($payload['note'])) {
                \App\Models\OrderNote::create([
                    'order_id' => $order->id,
                    'user_id'  => $userId,
                    'note'     => $payload['note'],
                ]);
            }
        } catch (\Throwable $e) {
            // If history write fails, don't block order creation; log and continue
            app_error_log('Order History Create', $e);
        }

        return $order;
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $order = self::query()->orderBy('id', 'desc')->first();
        if (!$order) {
            $orderNumber = 1000;
        } else {
            $orderNumber = $order->order_number + 1;
        }
        return $orderNumber;
    }

    /**
     * Get status text
     */
    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    /**
     * Get status color
     */
    final public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get payment method label
     */
    final public function getPaymentMethodText(): string
    {
        return self::PAYMENT_METHOD_LIST[$this->payment_method] ?? 'Cash on Delivery';
    }

    /**
     * Get payment status label
     */
    final public function getPaymentStatusText(): string
    {
        return self::PAYMENT_STATUS_LIST[$this->payment_status] ?? 'Unpaid';
    }

    /**
     * Check if order is pending
     */
    final public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if order is confirmed
     */
    final public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if order is delivered
     */
    final public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if order is cancelled
     */
    final public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get orders by status
     */
    final public function scopeByStatus(Builder $query, int $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get recent orders
     */
    final public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('order_date', '>=', now()->subDays($days));
    }

    // Relationships
    final public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // New: order items relationship
    final public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // New: user relationship (who created the order)
    final public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

   /**
     * Order history relation
    */
  final public function histories(): HasMany
   {
      return $this->hasMany(OrderHistory::class, 'order_id')->orderBy('created_at', 'desc');
   }

    /**
     * Order notes relation
     */
    final public function orderNotes(): HasMany
    {
        return $this->hasMany(\App\Models\OrderNote::class, 'order_id')->orderBy('created_at', 'desc');
    }

    // ================================================================
    //  STOCK HELPERS
    // ================================================================

    /**
     * Whether the given status means stock has already been returned to inventory.
     */
    public static function isStockRestoredStatus(int $status): bool
    {
        return in_array($status, [self::STATUS_CANCELLED, self::STATUS_RETURNED], true);
    }

    /**
     * Restore stock for every item currently on this order.
     * Use when transitioning TO cancelled or returned.
     */
    public function restoreStock(): void
    {
        $items = DB::table('order_items')
            ->where('order_id', $this->id)
            ->get(['product_id', 'variation_id', 'quantity']);

        foreach ($items as $item) {
            try {
                $qty = (int) $item->quantity;
                if ($item->variation_id) {
                    DB::table('product_variations')
                        ->where('id', $item->variation_id)
                        ->increment('stock', $qty);
                } else {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->whereNotNull('stock')
                        ->increment('stock', $qty);
                }
            } catch (\Throwable $e) {
                app_error_log('Order Stock Restore', $e);
            }
        }
    }

    /**
     * Decrement stock for every item currently on this order.
     * Use when re-activating a cancelled/returned order.
     */
    public function decrementStock(): void
    {
        $items = DB::table('order_items')
            ->where('order_id', $this->id)
            ->get(['product_id', 'variation_id', 'quantity']);

        foreach ($items as $item) {
            try {
                $qty = (int) $item->quantity;
                if ($item->variation_id) {
                    DB::table('product_variations')
                        ->where('id', $item->variation_id)
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                    DB::table('product_variations')
                        ->where('id', $item->variation_id)
                        ->where('stock', '<', 0)
                        ->update(['stock' => 0]);
                } else {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->whereNotNull('stock')
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->where('stock', '<', 0)
                        ->update(['stock' => 0]);
                }
            } catch (\Throwable $e) {
                app_error_log('Order Stock Decrement', $e);
            }
        }
    }

    /**
     * Restore stock from an items array snapshot (used inside updateOrder transaction).
     * Each element must have product_id and quantity keys.
     */
    private function restoreStockFromItems(array $items): void
    {
        foreach ($items as $item) {
            try {
                $qty = (int) ($item['quantity'] ?? 0);
                $vid = (int) ($item['variation_id'] ?? 0);
                $pid = (int) ($item['product_id']  ?? 0);
                if ($qty <= 0) continue;
                if ($vid > 0) {
                    DB::table('product_variations')
                        ->where('id', $vid)
                        ->increment('stock', $qty);
                } elseif ($pid > 0) {
                    DB::table('products')
                        ->where('id', $pid)
                        ->whereNotNull('stock')
                        ->increment('stock', $qty);
                }
            } catch (\Throwable $e) {
                app_error_log('Order Stock Restore Item', $e);
            }
        }
    }

    /**
     * Decrement stock from an items array snapshot (used inside updateOrder transaction).
     */
    private function decrementStockFromItems(array $items): void
    {
        foreach ($items as $item) {
            try {
                $qty = (int) ($item['quantity']    ?? 0);
                $vid = (int) ($item['variation_id'] ?? 0);
                $pid = (int) ($item['product_id']   ?? 0);
                if ($qty <= 0) continue;
                if ($vid > 0) {
                    DB::table('product_variations')
                        ->where('id', $vid)
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                    DB::table('product_variations')
                        ->where('id', $vid)
                        ->where('stock', '<', 0)
                        ->update(['stock' => 0]);
                } elseif ($pid > 0) {
                    DB::table('products')
                        ->where('id', $pid)
                        ->whereNotNull('stock')
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                    DB::table('products')
                        ->where('id', $pid)
                        ->where('stock', '<', 0)
                        ->update(['stock' => 0]);
                }
            } catch (\Throwable $e) {
                app_error_log('Order Stock Decrement Item', $e);
            }
        }
    }

    /**
     * Courier logs relation (one-to-many: an order can be re-sent to courier)
     */
    final public function courierLogs(): HasMany
    {
        return $this->hasMany(\App\Models\CourierLog::class, 'order_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get orders for dashboard stats
     */
    final public function getOrderStats(): array
    {
        // Get total counts grouped by status in a single query to avoid N queries
        $counts = self::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // Build statuses array from STATUS_LIST with count and color
        $statuses = collect(self::STATUS_LIST)->map(function ($label, $id) use ($counts) {
            return [
                'id'    => $id,
                'label' => $label,
                'count' => isset($counts[$id]) ? (int)$counts[$id] : 0,
                'color' => self::STATUS_COLORS[$id] ?? 'secondary',
            ];
        })->values()->all();

        return [
            'total_orders'     => self::count(),
            'pending_orders'   => self::where('status', self::STATUS_PENDING)->count(),
            'confirmed_orders' => self::where('status', self::STATUS_CONFIRMED)->count(),
            'delivered_orders' => self::where('status', self::STATUS_DELIVERED)->count(),
            'cancelled_orders' => self::where('status', self::STATUS_CANCELLED)->count(),
            'recent_orders'    => self::recent(7)->count(),
            'total_revenue'    => self::where('status', '!=', self::STATUS_CANCELLED)->sum('total_amount'),
            // New: detailed per-status array for the dashboard view
            'statuses'         => $statuses,
        ];
    }

    /**
     * Update order and its items
     *
     * @param array $payload
     * @return bool
     * @throws \Throwable
     */
    final public function updateOrder(array $payload): bool
    {
        // Backwards compatible signature: allow optional actor id as 2nd argument
        $actorId = null;
        if (func_num_args() > 1) {
            $actorId = func_get_arg(1);
        }

        DB::beginTransaction();
        try {
            // preserve previous snapshot for history
            $previous = [
                'customer_name'     => $this->customer_name,
                'customer_phone'    => $this->customer_phone,
                'customer_address'  => $this->customer_address,
                'customer_email'    => $this->customer_email,
                'product_price'     => $this->product_price,
                'delivery_location' => $this->delivery_location,
                'delivery_charge'   => $this->delivery_charge,
                'paid_amount'       => $this->paid_amount,
                'total_amount'      => $this->total_amount,
                'status'            => $this->status,
                'notes'             => $this->notes,
                'source'            => $this->source,
            ];

            // previous items snapshot
            $itemsPrevious = DB::table('order_items')
                ->where('order_id', $this->id)
                ->get(['product_id', 'quantity', 'unit_price'])
                ->map(function ($r) {
                    return (array) $r;
                })->all();

            $orderData = [
                'customer_name'     => $payload['customer_name'] ?? $this->customer_name,
                'customer_phone'    => $payload['customer_phone'] ?? $this->customer_phone,
                'customer_address'  => $payload['customer_address'] ?? $this->customer_address,
                'customer_email'    => $payload['customer_email'] ?? $this->customer_email,
                'product_price'     => isset($payload['product_price']) ? (float) $payload['product_price'] : $this->product_price,
                'coupon_code'       => $payload['coupon_code'] ?? $this->coupon_code,
                'discount_amount'   => isset($payload['discount_amount']) ? (float) $payload['discount_amount'] : $this->discount_amount,
                'delivery_location' => $payload['delivery_location'] ?? $this->delivery_location,
                'delivery_charge'   => isset($payload['delivery_charge']) ? (float) $payload['delivery_charge'] : $this->delivery_charge,
                'payment_method'    => $payload['payment_method'] ?? $this->payment_method,
                'payment_status'    => $payload['payment_status'] ?? $this->payment_status,
                'paid_amount'       => isset($payload['paid_amount']) ? (float) $payload['paid_amount'] : $this->paid_amount,
                'total_amount'      => isset($payload['order_total']) ? (float) $payload['order_total'] : $this->total_amount,
                'status'            => $payload['status'] ?? $this->status,
                'notes'             => $payload['note'] ?? $this->notes,
                'source'            => $payload['source'] ?? $this->source,
            ];

            // Update order row
            $this->update($orderData);

            // Replace order items
            $productIds      = $payload['product_ids']      ?? [];
            $quantities      = $payload['quantities']       ?? [];
            $unitPrices      = $payload['unit_prices']      ?? [];
            $variationIds    = $payload['variation_ids']    ?? [];
            $variationLabels = $payload['variation_labels'] ?? [];

            // Preserve the original cost snapshot for lines that survive the edit,
            // so editing an order does not rewrite its historical margin.
            $existingByKey = [];
            foreach (DB::table('order_items')->where('order_id', $this->id)
                ->get(['product_id', 'variation_id', 'cost_price']) as $row) {
                $existingByKey[$row->product_id . ':' . ($row->variation_id ?? 0)] = $row->cost_price;
            }

            // remove existing items
            DB::table('order_items')->where('order_id', $this->id)->delete();

            $itemCosts = $this->resolveItemCosts($productIds, $variationIds);

            $items = [];
            foreach ($productIds as $idx => $prodId) {
                $qty = isset($quantities[$idx]) ? (int) $quantities[$idx] : 1;
                $unit = isset($unitPrices[$idx]) ? (float) $unitPrices[$idx] : 0.0;
                $subtotal = $qty * $unit;

                $lineKey = (int) $prodId . ':' . (int) ($variationIds[$idx] ?? 0);

                $items[] = [
                    'order_id'        => $this->id,
                    'product_id'      => (int) $prodId,
                    'variation_id'    => $variationIds[$idx]    ?? null,
                    'variation_label' => $variationLabels[$idx] ?? null,
                    'unit_price'      => $unit,
                    'cost_price'      => $existingByKey[$lineKey] ?? $itemCosts[$idx] ?? null,
                    'quantity'        => $qty,
                    'subtotal'        => $subtotal,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            if (!empty($items)) {
                DB::table('order_items')->insert($items);
            }

            // new items snapshot
            $itemsNew = DB::table('order_items')
                ->where('order_id', $this->id)
                ->get(['product_id', 'quantity', 'unit_price'])
                ->map(function ($r) {
                    return (array) $r;
                })->all();

            // ── Stock adjustment based on status transition ──────────────
            $oldStatus   = (int) ($previous['status']);
            $newStatus   = (int) ($orderData['status']);
            $wasRestored = self::isStockRestoredStatus($oldStatus);
            $isRestored  = self::isStockRestoredStatus($newStatus);

            if (!$wasRestored && $isRestored) {
                // Active → Cancelled/Returned: restore stock from OLD items
                $this->restoreStockFromItems($itemsPrevious);
            } elseif ($wasRestored && !$isRestored) {
                // Cancelled/Returned → Active: re-decrement stock from NEW items
                $this->decrementStockFromItems($itemsNew);
            }
            // All other transitions: no stock change

            // compute field-level changes
            $changes = [];
            foreach ($orderData as $k => $v) {
                $prev = $previous[$k] ?? null;
                // normalize numeric comparison
                if (is_numeric($prev) || is_numeric($v)) {
                    $prevNum = is_null($prev) ? null : (float) $prev;
                    $vNum = is_null($v) ? null : (float) $v;
                    if ($prevNum !== $vNum) {
                        $changes[$k] = ['from' => $prev, 'to' => $v];
                    }
                } else {
                    if ($prev !== $v) {
                        $changes[$k] = ['from' => $prev, 'to' => $v];
                    }
                }
            }

            // store history record if anything changed or items changed
            if (!empty($changes) || $itemsPrevious !== $itemsNew) {
                OrderHistory::create([
                    'order_id' => $this->id,
                    'user_id' => $actorId,
                    'action' => 'updated',
                    'previous' => $previous,
                    'changes' => $changes,
                    'items_previous' => $itemsPrevious,
                    'items_new' => $itemsNew,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            app_error_log('Order Update', $e);
            throw $e;
        }
    }
}
