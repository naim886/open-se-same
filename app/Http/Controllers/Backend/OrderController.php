<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\SendFacebookCapiEvent;
use App\Models\CourierLog;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderNote;
use App\Models\Product;
use App\Services\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request): View
    {
        $cms_content = [
            'page_title' => 'Order List',
            'module' => 'Order',
            'module_url' => route('orders.index'),
            'sub_module' => 'List',
            'button_title' => 'Order Create',
            'button_url' => route('orders.create'),
            'type' => 'create'
        ];

        $orders = $this->order->getOrders($request);
        $stats  = $this->order->getOrderStats();
        $users  = \App\Models\User::select('id', 'name')->orderBy('name')->get();
        $allProducts = Product::where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);

        // Collect unique product IDs from order items shown on this page
        $productIds = $orders->pluck('orderItems')->flatten()->pluck('product_id')->filter()->unique()->values()->all();

        // Load products and build a lookup map with name + full photo URL
        $productMap = [];
        if (!empty($productIds)) {
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->select(['id', 'name', 'primary_photo'])
                ->get();

            $productMap = $products->mapWithKeys(function ($p) {
                return [$p->id => [
                    'name'  => $p->name,
                    'photo' => $p->primary_photo ? Storage::url($p->primary_photo) : null,
                ]];
            })->all();
        }
        return view('backend.modules.order.index', compact('orders', 'stats', 'productMap', 'cms_content', 'users', 'allProducts'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Order Create',
            'module'       => 'Order',
            'module_url'   => route('orders.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Order List',
            'button_url'   => route('orders.index'),
            'type'         => 'list'
        ];
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['activeVariations.variationAttributes.attributeName', 'activeVariations.variationAttributes.attributeValue'])
            ->get();
        return view('backend.modules.order.create', compact('products', 'cms_content'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request): RedirectResponse
    {

        // Accept multiple products and quantities and pricing fields
        $request->validate([
            'product_ids'       => 'required|array|min:1',
            'product_ids.*'     => 'required|integer',
            'quantities'        => 'required|array',
            'quantities.*'      => 'required|integer|min:1',
            'unit_prices'       => 'required|array',
            'unit_prices.*'     => 'required|numeric|min:0',
            'product_price'     => 'required|numeric|min:0',
            'delivery_location' => 'required|integer|in:' . implode(',', array_keys(Order::DELIVERY_LIST)),
            'delivery_charge'   => 'required|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',
            'order_total'       => 'required|numeric|min:0',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string|max:20',
            'customer_address'  => 'required|string|max:500',
            'customer_email'    => 'nullable|email|max:255',
            'status'            => 'nullable|integer|in:' . implode(',', array_keys(Order::STATUS_LIST)),
            'note'              => 'nullable|string|max:2000',
            'source'            => 'nullable|string|max:50',
        ]);

        try {
            // Pass validated data array and authenticated user id to model
            $data = $request->only([
                'product_ids', 'quantities', 'unit_prices', 'variation_ids', 'variation_labels',
                'product_price', 'delivery_location', 'delivery_charge', 'paid_amount', 'order_total',
                'customer_name', 'customer_phone', 'customer_address', 'customer_email',
                'note', 'status', 'source'
            ]);
            $userId = auth()->id();

            $this->order->storeOrder($data, $userId);

            return redirect()->route('orders.index')
                ->with('success', 'Order created successfully!');
        } catch (\Throwable $e) {
            // Keep error generic for production safety
            app_error_log('Order Store', $e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order. Please try again.');
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): View
    {
        $order->load([
            'product',
            'orderItems.product',
            'histories'       => fn ($q) => $q->latest(),
            'histories.user',
            'orderNotes'      => fn ($q) => $q->latest(),
            'orderNotes.user',
            'courierLogs'     => fn ($q) => $q->latest(),
        ]);
        return view('backend.modules.order.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order): View
    {
        $order->load(['orderItems', 'orderNotes.user', 'histories.user']);
        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['activeVariations.variationAttributes.attributeName', 'activeVariations.variationAttributes.attributeValue'])
            ->get();
        return view('backend.modules.order.edit', compact('order', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        // Validate same fields as store to safely update items and totals
        $request->validate([
            'product_ids'       => 'required|array|min:1',
            'product_ids.*'     => 'required|integer',
            'quantities'        => 'required|array',
            'quantities.*'      => 'required|integer|min:1',
            'unit_prices'       => 'required|array',
            'unit_prices.*'     => 'required|numeric|min:0',
            'product_price'     => 'required|numeric|min:0',
            'delivery_location' => 'required|integer|in:' . implode(',', array_keys(Order::DELIVERY_LIST)),
            'delivery_charge'   => 'required|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',
            'order_total'       => 'required|numeric|min:0',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string|max:20',
            'customer_address'  => 'required|string|max:500',
            'customer_email'    => 'nullable|email|max:255',
            'status'            => 'required|integer|in:' . implode(',', array_keys(Order::STATUS_LIST)),
            'note'              => 'nullable|string|max:2000',
            'source'            => 'nullable|string|max:50',
        ]);

        try {
            // Build data array similar to store
            $data = $request->only([
                'product_ids', 'quantities', 'unit_prices', 'variation_ids', 'variation_labels',
                'product_price', 'delivery_location', 'delivery_charge', 'paid_amount', 'order_total',
                'customer_name', 'customer_phone', 'customer_address', 'customer_email',
                'note', 'status', 'source'
            ]);

            $actorId = auth()->id();
            $order->updateOrder($data, $actorId);

            return redirect()->route('orders.index')
                ->with('success', 'Order updated successfully!');
        } catch (\Exception $e) {
            app_error_log('Order Update', $e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update order. Please try again.');
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order): RedirectResponse
    {
        try {
            $order->delete();
            return redirect()->route('backend.orders.index')
                ->with('success', 'Order deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete order. Please try again.');
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|integer|in:' . implode(',', array_keys(Order::STATUS_LIST)),
        ]);

        try {
            $previousStatus = (int) $order->status;
            $newStatus      = (int) $request->status;
            if ($previousStatus !== $newStatus) {
                // ── Stock: restore on cancel/return, re-decrement on reactivation ──
                if (!Order::isStockRestoredStatus($previousStatus) && Order::isStockRestoredStatus($newStatus)) {
                    $order->restoreStock();
                } elseif (Order::isStockRestoredStatus($previousStatus) && !Order::isStockRestoredStatus($newStatus)) {
                    $order->decrementStock();
                }

                $order->update(['status' => $newStatus]);
                // record history row
                \App\Models\OrderHistory::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'action' => 'status_changed',
                    'previous' => ['status' => $previousStatus],
                    'changes' => ['status' => ['from' => $previousStatus, 'to' => $newStatus]],
                    'items_previous' => null,
                    'items_new' => null,
                ]);

                // ── CAPI: OrderCancelled ──────────────────────────
                if ($newStatus === Order::STATUS_CANCELLED
                    && $previousStatus !== Order::STATUS_CANCELLED) {
                    $this->dispatchCancelledCapiEvent($order);
                }
            }
             return redirect()->back()
                 ->with('success', 'Order status updated successfully!');
         } catch (\Exception $e) {
             return redirect()->back()
                 ->with('error', 'Failed to update order status. Please try again.');
         }
     }

    /**
     * Dispatch the Facebook CAPI "OrderCancelled" custom event for the given order.
     * Runs asynchronously via the queue so it never delays the HTTP response.
     */
    private function dispatchCancelledCapiEvent(Order $order): void
    {
        // Eagerly load items if not already loaded
        if (!$order->relationLoaded('orderItems')) {
            $order->load('orderItems');
        }

        // Build content IDs from all order items (use variation_id when present, else product_id)
        $contentIds = $order->orderItems
            ->map(fn($item) => (string) ($item->variation_id ?? $item->product_id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $contentName = $order->orderItems
            ->pluck('product_name')
            ->filter()
            ->implode(', ');

        $numItems = (int) $order->orderItems->sum('quantity');

        // Build external_id: hash of normalised customer phone (matches ShopController logic)
        $rawPhone = preg_replace('/\D/', '', $order->customer_phone ?? '');
        if ($rawPhone !== '' && strlen($rawPhone) === 11 && str_starts_with($rawPhone, '0')) {
            $rawPhone = '880' . substr($rawPhone, 1);
        }
        $externalId = $rawPhone !== '' ? hash('sha256', $rawPhone) : hash('sha256', (string) $order->id);

        $nameParts = explode(' ', trim($order->customer_name ?? ''), 2);

        $userData = [
            'ip'          => null,  // not available in backend context
            'user_agent'  => null,
            'fbc'         => null,
            'fbp'         => null,
            'external_id' => $externalId,
            'email'       => $order->customer_email ?? null,
            'phone'       => $order->customer_phone ?? null,
            'first_name'  => $nameParts[0] ?? null,
            'last_name'   => $nameParts[1] ?? null,
        ];

        $customData = [
            'value'        => (float) ($order->order_total ?? 0),
            'content_ids'  => $contentIds,
            'content_name' => $contentName,
            'content_type' => 'product',
            'num_items'    => $numItems,
            'order_id'     => (string) $order->order_number,
        ];

        SendFacebookCapiEvent::dispatch(
            'OrderCancelled',
            $userData,
            $customData,
            Str::uuid()->toString(),
            route('orders.show', $order->id)
        );
    }

    /**
     * Filter orders by status
     */
    public function filterByStatus(Request $request): RedirectResponse
    {
        // Redirect to the main orders index with status query string
        // so the full order list (with productMap, users, etc.) is always loaded correctly
        $params = array_filter(['status' => $request->get('status')]);
        return redirect()->route('orders.index', $params);
    }


    public function order_dependency(Request $request)
    {
        $product = null;
        if ($request->input('product_id')) {
            $product = Product::query()
                ->select(['id', 'price', 'sale_price', 'primary_photo', 'product_type'])
                ->where('id', $request->input('product_id'))
                ->first();
        }
        if (!$product) {
            return response()->json(['product' => null], 404);
        }

        $isVariant  = $product->isVariantProduct();
        $variations = [];
        if ($isVariant) {
            $product->load(['activeVariations.variationAttributes.attributeName', 'activeVariations.variationAttributes.attributeValue']);
            $variations = $product->activeVariations->map(function ($v) use ($product) {
                return [
                    'id'    => $v->id,
                    'label' => $v->label,
                    'price' => $v->sale_price ? (float) $v->sale_price : (float) $v->price,
                ];
            })->values()->toArray();
        }

        return response()->json([
            'product' => [
                'price'         => $product->sale_price == 0 ? $product->price : $product->sale_price,
                'primary_photo' => Storage::url($product->primary_photo),
                'is_variant'    => $isVariant,
                'variations'    => $variations,
            ]
        ]);
    }

    /**
     * Add a note to an order (append-only).
     */
    public function addNote(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            OrderNote::create([
                'order_id' => $order->id,
                'user_id'  => auth()->id(),
                'note'     => $request->note,
            ]);
            return redirect()->back()->with('success', 'Note added successfully.');
        } catch (\Exception $e) {
            app_error_log('Order Add Note', $e);
            return redirect()->back()->with('error', 'Failed to add note.');
        }
    }

    /**
     * Send order to Steadfast courier.
     * Handles Bengali phone detection/conversion with user confirmation.
     */
    public function sendToCourier(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'convert_phone' => 'nullable|in:0,1',
        ]);

        try {
            $phone         = $order->customer_phone;
            $originalPhone = null;

            // If phone has Bengali digits and user did NOT confirm conversion, refuse
            if ($this->isBengaliPhone($phone)) {
                if ($request->input('convert_phone') != '1') {
                    return redirect()->back()->with(
                        'error',
                        'Phone number contains Bengali digits. Please confirm conversion to English before sending.'
                    );
                }
                // Convert Bengali digits to English (no history — phone not permanently changed)
                $originalPhone = $phone;
                $phone = $this->convertBengaliToEnglish($phone);
            }

            // Build item description
            $order->loadMissing('orderItems.product');
            $itemDesc = $order->orderItems
                ->map(fn($i) => ($i->product->name ?? 'Item') . ' x' . $i->quantity)
                ->implode(', ');

            $payload = [
                'invoice'           => $order->order_number,
                'recipient_name'    => $order->customer_name,
                'recipient_phone'   => $phone,
                'recipient_address' => $order->customer_address,
                'cod_amount'        => max(0, (float) $order->total_amount - (float) ($order->paid_amount ?? 0)),
                'note'              => $order->notes,
                'item_description'  => $itemDesc,
            ];

            $steadfast = new SteadfastService();
            $result    = $steadfast->createOrder($payload);

            $consignment   = $result['consignment'] ?? null;
            $consignmentId = $consignment ? ($consignment['consignment_id'] ?? null) : null;
            $trackingCode  = $consignment ? ($consignment['tracking_code']  ?? null) : null;
            $apiStatus     = $consignment ? ($consignment['status']         ?? null) : null;

            // Always log the attempt (even on failure) for audit trail
            CourierLog::create([
                'order_id'       => $order->id,
                'consignment_id' => $consignmentId,
                'tracking_code'  => $trackingCode,
                'status'         => $apiStatus,
                'response'       => $result,
                'sent_phone'     => $phone,
            ]);

            if ($consignmentId) {
                $prevStatus = $order->status;
                $order->update([
                    'consignment_id' => $consignmentId,
                    'tracking_code'  => $trackingCode,
                    'status'         => Order::STATUS_COURIER_ENTRY,
                ]);

                // Log the courier submission detail
                OrderHistory::create([
                    'order_id'       => $order->id,
                    'user_id'        => auth()->id(),
                    'action'         => 'sent_to_courier',
                    'previous'       => ['status' => $prevStatus],
                    'changes'        => [
                        'consignment_id' => $consignmentId,
                        'tracking_code'  => $trackingCode,
                        'status'         => ['from' => $prevStatus, 'to' => Order::STATUS_COURIER_ENTRY],
                        'note'           => 'Sent to Steadfast courier'
                                           . ($originalPhone ? " (phone converted from {$originalPhone})" : ''),
                    ],
                    'items_previous' => null,
                    'items_new'      => null,
                ]);

                // Log the status change so it appears in Status Change Log
                OrderHistory::create([
                    'order_id'       => $order->id,
                    'user_id'        => auth()->id(),
                    'action'         => 'status_changed',
                    'previous'       => ['status' => $prevStatus],
                    'changes'        => ['status' => ['from' => $prevStatus, 'to' => Order::STATUS_COURIER_ENTRY]],
                    'items_previous' => null,
                    'items_new'      => null,
                ]);

                return redirect()->back()->with(
                    'success',
                    "✅ Sent to Steadfast! Consignment ID: {$consignmentId} | Tracking: {$trackingCode}"
                );
            }

            $errorMsg = $result['message'] ?? 'Unknown error from Steadfast API';
            return redirect()->back()->with('error', "Courier API error: {$errorMsg}");

        } catch (\Exception $e) {
            app_error_log('Send To Courier', $e);
            return redirect()->back()->with('error', 'Failed to send to courier: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------
    // Phone helpers
    // -----------------------------------------------

    private function isBengaliPhone(string $phone): bool
    {
        return (bool) preg_match('/[০-৯]/u', $phone);
    }

    private function convertBengaliToEnglish(string $phone): string
    {
        $bengali = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($bengali, $english, $phone);
    }
}
