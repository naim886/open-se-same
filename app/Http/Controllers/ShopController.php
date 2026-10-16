<?php

namespace App\Http\Controllers;

use App\Models\AttributeName;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\FlashSale;
use App\Models\HomepageSection;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttribute;
use App\Models\SiteContent;
use App\Models\TopSellingProduct;
use App\Http\Requests\StoreContactRequest;
use App\Jobs\SendFacebookCapiEvent;
use App\Services\CouponService;
use App\Services\OrderNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopController extends Controller
{
    // ============================================================
    // HOME
    // ============================================================
    public function home(): View
    {
        $sections = HomepageSection::ordered()->load('category');
        $blocks   = [];

        if ($sections->isEmpty()) {
            // Nothing configured yet: fall back to the hero slider plus the
            // "Shop by Category" tiles.
            $banners = Banner::where('status', Banner::STATUS_ACTIVE)
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($banners->isNotEmpty()) {
                $blocks[] = ['type' => HomepageSection::TYPE_BANNER, 'title' => null, 'banners' => $banners];
            }

            $tiles = $this->categoryTiles();

            if ($tiles->isNotEmpty()) {
                $blocks[] = ['type' => 'category_tiles', 'title' => null, 'categories' => $tiles];
            }
        } else {
            foreach ($sections as $section) {
                $block = $this->resolveHomeBlock($section);

                if ($block !== null) {
                    $blocks[] = $block;
                }
            }
        }

        $homePageContent = SiteContent::get('home_page');

        return view('frontend.pages.home', compact('blocks', 'homePageContent'));
    }

    /**
     * Turn one configured homepage block into the data its view partial needs.
     * Blocks with nothing to show return null so the homepage never renders an
     * empty section.
     *
     * @return array<string, mixed>|null
     */
    private function resolveHomeBlock(HomepageSection $section): ?array
    {
        switch ($section->type) {
            case HomepageSection::TYPE_BANNER:
                $banners = Banner::where('status', Banner::STATUS_ACTIVE)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                return $banners->isEmpty()
                    ? null
                    : ['type' => $section->type, 'title' => $section->title, 'banners' => $banners];

            case HomepageSection::TYPE_FLASH_SALE:
                $flashSale = FlashSale::running()
                    ->with(['products' => fn ($q) => $q->with(['photos'])->orderBy('flash_sale_products.id')])
                    ->first();

                return $flashSale
                    ? ['type' => $section->type, 'title' => $section->title, 'flashSale' => $flashSale]
                    : null;

            case HomepageSection::TYPE_TOP_SELLING:
                $products = TopSellingProduct::active()
                    ->with(['product' => fn ($q) => $q->where('status', Product::STATUS_ACTIVE)->with(['category', 'photos'])])
                    ->orderBy('sort_order')
                    ->get()
                    ->pluck('product')
                    ->filter()
                    ->take(10)
                    ->values();

                return $products->isEmpty()
                    ? null
                    : ['type' => $section->type, 'title' => $section->title, 'products' => $products];

            case HomepageSection::TYPE_NEW_PRODUCTS:
                $products = Product::where('status', Product::STATUS_ACTIVE)
                    ->with(['category', 'photos'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                return $products->isEmpty()
                    ? null
                    : ['type' => $section->type, 'title' => $section->title, 'products' => $products];

            case HomepageSection::TYPE_CATEGORY:
                $category = $section->category;

                if (!$category || (int) $category->status !== Category::STATUS_ACTIVE) {
                    return null;
                }

                $products = Product::where('status', Product::STATUS_ACTIVE)
                    ->whereIn('category_id', $this->categoryScopeIds($category))
                    ->with(['category', 'photos'])
                    ->limit(10)
                    ->get();

                return $products->isEmpty()
                    ? null
                    : [
                        'type'     => $section->type,
                        'title'    => $section->title,
                        'category' => $category,
                        'products' => $products,
                    ];
        }

        return null;
    }

    /**
     * Top level categories shown as the homepage "Shop by Category" tiles.
     */
    private function categoryTiles(): Collection
    {
        return Category::where('status', Category::STATUS_ACTIVE)
            ->whereNull('category_id')
            ->withCount(['products' => fn ($q) => $q->where('status', Product::STATUS_ACTIVE)])
            ->orderBy('sort_order', 'asc')
            ->limit(16)
            ->get();
    }

    // ============================================================
    // OFFERS / DISCOUNTED PRODUCTS
    // ============================================================
    public function offer(Request $request): View
    {
        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->with(['category', 'photos']);

        if ($request->filled('category')) {
            $query->where('category_id', intval($request->category));
        }

        // Default sort for offer page is biggest discount first
        if (!$request->filled('sort')) {
            $request->merge(['sort' => 'discount']);
        }

        $this->applyFilters($query, $request);

        $products = $query->paginate(24);

        $allCategories = Category::where('status', Category::STATUS_ACTIVE)
            ->withCount(['products' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)
                ->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->get();

        $flashSale = \App\Models\FlashSale::running()->first();

        return view('frontend.pages.offer', compact('products', 'allCategories', 'flashSale'));
    }

    // ============================================================
    // ALL PRODUCTS
    // ============================================================
    public function products(Request $request): View
    {
        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category', 'photos']);

        $this->applyFilters($query, $request);

        $products = $query->paginate(20)->withQueryString();

        return view('frontend.pages.products', $this->listingData([
            'products' => $products,
            'category' => null,
            'brand'    => null,
        ]));
    }

    // ============================================================
    // CATEGORY PAGE
    // ============================================================
    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('status', Category::STATUS_ACTIVE)
            ->firstOrFail();

        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->whereIn('category_id', $this->categoryScopeIds($category))
            ->with(['category', 'photos']);

        $this->applyFilters($query, $request);

        $products = $query->paginate(20)->withQueryString();

        return view('frontend.pages.products', $this->listingData([
            'products' => $products,
            'category' => $category,
            'brand'    => null,
        ]));
    }

    // ============================================================
    // BRAND PAGE
    // ============================================================
    public function brand(Request $request, string $slug): View
    {
        $brand = Brand::where('slug', $slug)
            ->where('status', Brand::STATUS_ACTIVE)
            ->firstOrFail();

        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->where('brand_id', $brand->id)
            ->with(['category', 'photos']);

        $this->applyFilters($query, $request);

        $products = $query->paginate(20)->withQueryString();

        return view('frontend.pages.brand', $this->listingData([
            'products' => $products,
            'category' => null,
            'brand'    => $brand,
        ]));
    }

    // ============================================================
    // SEARCH
    // ============================================================
    public function search(Request $request): View
    {
        $q = $request->get('q', '');

        if (trim($q) !== '') {
            \App\Models\SearchKeyword::record($q);
        }

        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category', 'photos'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                    ->orWhere('short_description', 'like', "%$q%")
                    ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%$q%"))
                    ->orWhereHas('brand', fn($bq) => $bq->where('name', 'like', "%$q%"));
            });

        $this->applyFilters($query, $request);
        $products = $query->paginate(20)->withQueryString();

        return view('frontend.pages.products', $this->listingData([
            'products' => $products,
            'category' => null,
            'brand'    => null,
        ]));
    }

    /**
     * Shared payload for the product listing pages (all products, category,
     * brand and search) — the filter sidebar is identical on all of them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function listingData(array $data): array
    {
        $categoryIds = $data['category'] ? $this->categoryScopeIds($data['category']) : [];
        $brandId     = $data['brand']?->id;

        $data['categoryTree']     = $this->categoryTree();
        $data['variationFilters'] = $this->variationFilterOptions($categoryIds, $brandId);
        $data['filterAction']     = $data['brand']
            ? route('shop.brand', $data['brand']->slug)
            : ($data['category'] ? route('shop.category', $data['category']->slug) : route('shop.products'));

        return $data;
    }

    /**
     * A category plus its descendants — parent categories list the products of
     * their sub categories, matching the collapsible tree in the sidebar.
     *
     * @return array<int, int>
     */
    private function categoryScopeIds(Category $category): array
    {
        return array_merge([$category->id], $category->descendantIds());
    }

    /**
     * Top level categories with their active children, for the collapsible
     * category accordion in the filter sidebar / mobile filter drawer.
     */
    private function categoryTree(): Collection
    {
        return Category::where('status', Category::STATUS_ACTIVE)
            ->whereNull('category_id')
            ->with([
                'children' => fn ($query) => $query
                    ->where('status', Category::STATUS_ACTIVE)
                    ->withCount(['products' => fn ($q) => $q->where('status', Product::STATUS_ACTIVE)])
                    ->orderBy('sort_order'),
            ])
            ->withCount(['products' => fn ($q) => $q->where('status', Product::STATUS_ACTIVE)])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Attribute name/value pairs actually used by variations of the products in
     * scope, so the sidebar only offers filters that can return results.
     *
     * @param  array<int, int>  $categoryIds
     */
    private function variationFilterOptions(array $categoryIds = [], ?int $brandId = null): Collection
    {
        $pairs = ProductVariationAttribute::query()
            ->join('product_variations', 'product_variations.id', '=', 'product_variation_attributes.product_variation_id')
            ->join('products', 'products.id', '=', 'product_variations.product_id')
            ->whereNull('product_variations.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('product_variations.status', 1)
            ->where('products.status', Product::STATUS_ACTIVE)
            ->when($categoryIds !== [], fn ($query) => $query->whereIn('products.category_id', $categoryIds))
            ->when($brandId, fn ($query) => $query->where('products.brand_id', $brandId))
            ->distinct()
            ->get([
                'product_variation_attributes.attribute_name_id',
                'product_variation_attributes.attribute_value_id',
            ]);

        if ($pairs->isEmpty()) {
            return collect();
        }

        $names = AttributeName::whereIn('id', $pairs->pluck('attribute_name_id')->unique())
            ->orderBy('name')
            ->pluck('name', 'id');

        $values = AttributeValue::whereIn('id', $pairs->pluck('attribute_value_id')->unique())
            ->orderBy('name')
            ->pluck('name', 'id');

        return $pairs
            ->groupBy('attribute_name_id')
            ->map(static fn ($group, $nameId) => [
                'id'     => (int) $nameId,
                'name'   => $names[$nameId] ?? 'Attribute',
                'values' => $group->pluck('attribute_value_id')
                    ->unique()
                    ->map(static fn ($valueId) => [
                        'id'   => (int) $valueId,
                        'name' => $values[$valueId] ?? 'Unnamed',
                    ])
                    ->values(),
            ])
            ->sortBy('name')
            ->values();
    }

    // ============================================================
    // PRODUCT DETAIL
    // ============================================================
    public function product(string $slug): View
    {
        $product = Product::with(['photos', 'category', 'brand', 'specifications.specification', 'qas', 'activeVariations'])
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        FlashSale::applyFlashPrice($product);

        // ── CAPI: ViewContent ─────────────────────────────────────
        $vcEventId = Str::uuid()->toString();
        SendFacebookCapiEvent::dispatch(
            'ViewContent',
            $this->capiRequestData(),
            [
                'value'        => round((float)($product->sale_price ?? $product->price ?? 0) / 2, 2),
                'content_ids'  => [(string) $product->id],
                'content_name' => $product->name,
                'content_type' => 'product',
            ],
            $vcEventId,
            request()->url()
        );

        $relatedProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['photos'])
            ->limit(5)
            ->get();

        $deliveryInfo = SiteContent::get('delivery_policy');
        $returnInfo   = SiteContent::get('return_policy');

        return view('frontend.pages.product-show', compact('product', 'relatedProducts', 'deliveryInfo', 'returnInfo', 'vcEventId'));
    }

    // ============================================================
    // QUICK ORDER FORM
    // ============================================================
    public function quickOrder(string $slug): View
    {
        $product = Product::with(['photos', 'activeVariations.variationAttributes.attributeName', 'activeVariations.variationAttributes.attributeValue'])
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        FlashSale::applyFlashPrice($product);

        // ── CAPI: InitiateCheckout ────────────────────────────────
        $icEventId = Str::uuid()->toString();
        SendFacebookCapiEvent::dispatch(
            'InitiateCheckout',
            $this->capiRequestData(),
            [
                'value'        => round((float)($product->sale_price ?? $product->price ?? 0) / 2, 2),
                'content_ids'  => [(string) $product->id],
                'content_name' => $product->name,
                'content_type' => 'product',
                'num_items'    => 1,
            ],
            $icEventId,
            request()->url()
        );

        $defaultAddress = auth()->check() ? auth()->user()->addresses()->where('is_default', true)->first() : null;
        $authUser       = auth()->user();

        return view('frontend.pages.quick-order', compact('product', 'icEventId', 'defaultAddress', 'authUser'));
    }

    // ============================================================
    // PLACE QUICK ORDER (single product)
    // ============================================================
    public function placeQuickOrder(Request $request, CouponService $coupons): RedirectResponse
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => ['required', 'digits:11'],
            'customer_address' => 'required|string|max:500',
            'customer_email'   => 'nullable|email|max:255',
            'quantity'         => 'required|integer|min:1',
            'delivery_location'=> 'required|in:1,2,3,4,5',
            'variation_id'     => 'nullable|integer|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty     = (int) $request->quantity;
        FlashSale::applyFlashPrice($product);
        $price   = $product->sale_price ?? $product->price;

        $variation = null;
        if ($request->variation_id) {
            $variation = ProductVariation::with('variationAttributes.attributeName', 'variationAttributes.attributeValue')
                ->find($request->variation_id);
            if ($variation) {
                $price = $variation->sale_price ?: $variation->price;
            }
        }
        $dc      = Order::deliveryCost($request->delivery_location);

        // Coupons are validated server-side against the true product subtotal.
        $lineTotal    = (float) ($price * $qty);
        $couponResult = $coupons->validate($request->input('coupon_code'), $lineTotal, $request->customer_phone);
        $discount     = $couponResult->ok ? $couponResult->discount : 0.0;

        $payload = [
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'customer_address'  => $request->customer_address,
            'customer_email'    => $request->customer_email,
            'product_ids'       => [$product->id],
            'product_names'     => [$product->name],
            'quantities'        => [$qty],
            'unit_prices'       => [$price],
            'variation_ids'     => [$variation?->id],
            'variation_labels'  => [$variation?->label],
            'product_price'     => $lineTotal,
            'delivery_location' => (int) $request->delivery_location,
            'delivery_charge'   => $dc,
            'paid_amount'       => 0,
            'order_total'       => max(0, $lineTotal + $dc - $discount),
            'coupon_code'       => $couponResult->ok ? $couponResult->coupon->code : null,
            'discount_amount'   => $discount,
            'note'              => $request->note,
            'status'            => Order::STATUS_PENDING,
            'source'            => 'website',
        ];

        try {
            $order = (new Order())->storeOrder($payload);

            if ($couponResult->ok) {
                $coupons->recordUsage($couponResult->coupon, $order->id, $request->customer_phone, $discount);
            }

            app(OrderNotificationService::class)->orderPlaced($order);

            // ── CAPI: Purchase ────────────────────────────────────
            $purchaseEventId = Str::uuid()->toString();
            $nameParts       = explode(' ', trim($request->customer_name), 2);
            $purchaseContentId = $variation ? (string) $variation->id : (string) $product->id;
            SendFacebookCapiEvent::dispatch(
                'Purchase',
                $this->capiRequestData([
                    'email'      => $request->customer_email,
                    'phone'      => $request->customer_phone,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name'  => $nameParts[1] ?? '',
                ]),
                [
                    'value'        => round(($price * $qty) / 2, 2),
                    'content_ids'  => [$purchaseContentId],
                    'content_name' => $product->name,
                    'content_type' => 'product',
                    'num_items'    => $qty,
                    'order_id'     => (string) $order->order_number,
                ],
                $purchaseEventId,
                request()->url()
            );

            return redirect()->route('shop.order.success')
                ->with('success', 'আপনার অর্ডার সফলভাবে গৃহীত হয়েছে!')
                ->with('order_number', $order->order_number)
                ->with('delivery_location', (int) $request->delivery_location)
                ->with('purchase_event_id', $purchaseEventId)
                ->with('order_details', [
                    'customer_name'    => $payload['customer_name'],
                    'customer_phone'   => $payload['customer_phone'],
                    'customer_email'   => $payload['customer_email'] ?? '',
                    'customer_address' => $payload['customer_address'],
                    'product_price'    => $payload['product_price'],
                    'total_amount'     => $payload['order_total'],
                    'content_ids'      => [$purchaseContentId],
                ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'অর্ডার করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    // ============================================================
    // COUPON: LIVE VALIDATION (AJAX)
    // ============================================================
    public function validateCoupon(Request $request, CouponService $coupons): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        // Prefer the server-side cart subtotal. The client-supplied value is only
        // a preview fallback for the single-product quick-order page.
        $cart     = session('cart', []);
        $subtotal = (float) $request->input('subtotal', 0);

        if (! empty($cart)) {
            $subtotal = (float) collect($cart)->sum(
                fn ($item) => ($item['sale_price'] ?? $item['price']) * $item['quantity']
            );
        }

        $result = $coupons->validate($request->input('code'), $subtotal, $request->input('phone'));

        $delivery = Order::deliveryCost($request->input('delivery_location', Order::DELIVERY_INSIDE_DHAKA));

        return response()->json([
            'ok'       => $result->ok,
            'message'  => $result->message !== ''
                ? $result->message
                : ($result->ok ? 'Coupon applied successfully.' : 'Invalid coupon code.'),
            'code'     => $result->coupon?->code,
            'discount' => $result->discount,
            'total'    => max(0, $subtotal + $delivery - $result->discount),
        ]);
    }

    // ============================================================
    // PLACE CART ORDER
    // ============================================================
    public function placeOrder(Request $request, CouponService $coupons): RedirectResponse
    {
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => ['required', 'digits:11'],
            'customer_address' => 'required|string|max:500',
            'customer_email'   => 'nullable|email|max:255',
            'delivery_location'=> 'required|in:1,2,3,4,5',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'কার্ট খালি। প্রথমে পণ্য যোগ করুন।');
        }

        $productIds      = [];
        $quantities      = [];
        $unitPrices      = [];
        $productNames    = [];
        $variationIds    = [];
        $variationLabels = [];
        $productTotal    = 0;

        foreach ($cart as $cartKey => $item) {
            $price        = $item['sale_price'] ?? $item['price'];
            $qty          = $item['quantity'];
            $productIds[] = $item['product_id'] ?? $cartKey; // legacy key was productId directly
            $quantities[] = $qty;
            $unitPrices[] = $price;
            $productNames[] = $item['name'] ?? null;
            $variationIds[]    = $item['variation_id']    ?? null;
            $variationLabels[] = $item['variation_label'] ?? null;
            $productTotal += $price * $qty;
        }

        $dc = Order::deliveryCost($request->delivery_location);

        // Coupons are validated server-side against the true cart subtotal.
        $couponResult = $coupons->validate($request->input('coupon_code'), $productTotal, $request->customer_phone);
        $discount     = $couponResult->ok ? $couponResult->discount : 0.0;

        $payload = [
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'customer_address'  => $request->customer_address,
            'customer_email'    => $request->customer_email,
            'product_ids'        => $productIds,
            'quantities'         => $quantities,
            'unit_prices'        => $unitPrices,
            'product_names'      => $productNames,
            'variation_ids'      => $variationIds,
            'variation_labels'   => $variationLabels,
            'product_price'      => $productTotal,
            'delivery_location' => (int) $request->delivery_location,
            'delivery_charge'   => $dc,
            'paid_amount'       => 0,
            'order_total'       => max(0, $productTotal + $dc - $discount),
            'coupon_code'       => $couponResult->ok ? $couponResult->coupon->code : null,
            'discount_amount'   => $discount,
            'note'              => $request->note,
            'status'            => Order::STATUS_PENDING,
            'source'            => 'website',
        ];

        try {
            $order = (new Order())->storeOrder($payload);

            if ($couponResult->ok) {
                $coupons->recordUsage($couponResult->coupon, $order->id, $request->customer_phone, $discount);
            }

            app(OrderNotificationService::class)->orderPlaced($order);

            // Clear cart after successful order
            session()->forget('cart');

            // ── CAPI: Purchase ────────────────────────────────────
            $purchaseEventId = Str::uuid()->toString();
            $nameParts       = explode(' ', trim($request->customer_name), 2);
            // Build content_ids: use variation_id when present, else product_id
            $purchaseContentIds = array_values(array_map(
                fn($vId, $pId) => $vId ? (string) $vId : (string) $pId,
                $variationIds,
                $productIds
            ));
            SendFacebookCapiEvent::dispatch(
                'Purchase',
                $this->capiRequestData([
                    'email'      => $request->customer_email,
                    'phone'      => $request->customer_phone,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name'  => $nameParts[1] ?? '',
                ]),
                [
                    'value'        => round($productTotal / 2, 2),
                    'content_ids'  => $purchaseContentIds,
                    'content_type' => 'product',
                    'num_items'    => (int) array_sum($quantities),
                    'order_id'     => (string) $order->order_number,
                ],
                $purchaseEventId,
                request()->url()
            );

            return redirect()->route('shop.order.success')
                ->with('success', 'আপনার অর্ডার সফলভাবে গৃহীত হয়েছে!')
                ->with('order_number', $order->order_number)
                ->with('delivery_location', (int) $request->delivery_location)
                ->with('purchase_event_id', $purchaseEventId)
                ->with('order_details', [
                    'customer_name'    => $payload['customer_name'],
                    'customer_phone'   => $payload['customer_phone'],
                    'customer_email'   => $payload['customer_email'] ?? '',
                    'customer_address' => $payload['customer_address'],
                    'product_price'    => $payload['product_price'],
                    'total_amount'     => $payload['order_total'],
                    'content_ids'      => $purchaseContentIds,
                ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'অর্ডার করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    // ============================================================
    // CHECKOUT
    // ============================================================
    public function checkout(): View
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return view('frontend.pages.cart', ['cart' => [], 'subtotal' => 0]);
        }
        $subtotal   = collect($cart)->sum(fn($i) => ($i['sale_price'] ?? $i['price']) * $i['quantity']);
        $delivery   = request('delivery', 70);
        $icEventId  = Str::uuid()->toString();
        // Build content_ids using variation_id when present, else product_id
        $contentIds = array_values(array_map(
            fn($item) => $item['variation_id'] ? (string) $item['variation_id'] : (string) ($item['product_id'] ?? 0),
            $cart
        ));
        $numItems   = (int) collect($cart)->sum(fn($i) => $i['quantity']);

        // ── CAPI: InitiateCheckout ────────────────────────────────
        SendFacebookCapiEvent::dispatch(
            'InitiateCheckout',
            $this->capiRequestData(),
            [
                'value'        => round($subtotal / 2, 2),
                'content_ids'  => $contentIds,
                'content_type' => 'product',
                'num_items'    => $numItems,
            ],
            $icEventId,
            request()->url()
        );

        $defaultAddress = auth()->check() ? auth()->user()->addresses()->where('is_default', true)->first() : null;
        $authUser       = auth()->user();

        return view('frontend.pages.checkout', compact('cart', 'subtotal', 'delivery', 'icEventId', 'defaultAddress', 'authUser'));
    }

    // ============================================================
    // ORDER SUCCESS
    // ============================================================
    public function orderSuccess(): View
    {
        return view('frontend.pages.order-placed');
    }

    // ============================================================
    // CAPI HELPER
    // ============================================================
    private function capiRequestData(array $customer = []): array
    {
        // Persistent anonymous session ID — used as external_id when no phone available
        if (!session()->has('capi_uid')) {
            session(['capi_uid' => (string) Str::uuid()]);
        }

        // external_id: prefer hashed phone (most stable), fall back to session UUID
        $rawPhone = isset($customer['phone']) ? preg_replace('/\D/', '', $customer['phone']) : '';
        if ($rawPhone !== '') {
            // Normalise BD number → 880XXXXXXXXXX before hashing
            if (strlen($rawPhone) === 11 && str_starts_with($rawPhone, '0')) {
                $rawPhone = '880' . substr($rawPhone, 1);
            }
            $externalId = hash('sha256', $rawPhone);
        } else {
            $externalId = hash('sha256', session('capi_uid'));
        }

        return [
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent() ?? '',
            'fbc'         => request()->cookie('_fbc'),
            'fbp'         => request()->cookie('_fbp'),
            'external_id' => $externalId,
            'fb_login_id' => auth()->check() ? (string) auth()->id() : null,
            'email'       => $customer['email']      ?? null,
            'phone'       => $customer['phone']      ?? null,
            'first_name'  => $customer['first_name'] ?? null,
            'last_name'   => $customer['last_name']  ?? null,
        ];
    }

    // ============================================================
    // CART: VIEW
    // ============================================================
    public function cart(): View
    {
        $cart     = session('cart', []);
        $subtotal = collect($cart)->sum(fn($i) => ($i['sale_price'] ?? $i['price']) * $i['quantity']);
        return view('frontend.pages.cart', compact('cart', 'subtotal'));
    }

    // ============================================================
    // CART: ADD
    // ============================================================
    public function cartAdd(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'quantity'     => 'integer|min:1',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty     = (int) ($request->quantity ?? 1);

        FlashSale::applyFlashPrice($product);

        // Resolve variation
        $variation    = null;
        $variationId  = $request->variation_id ? (int) $request->variation_id : null;
        $variationLabel = null;

        if ($variationId) {
            $variation = \App\Models\ProductVariation::with(['variationAttributes.attributeName', 'variationAttributes.attributeValue'])
                ->where('product_id', $product->id)
                ->where('status', 1)
                ->find($variationId);
            if ($variation) {
                $variationLabel = $variation->label;
            }
        }

        // Use variation price if available, otherwise product price
        $effectivePrice    = $variation?->price       ?? $product->price;
        $effectiveSalePrice = $variation?->sale_price ?? ($product->sale_price ?? null);

        $cart = session('cart', []);

        // Cart key = product_id or product_id-variation_id for unique slots
        $cartKey = $variationId ? ($product->id . '-' . $variationId) : (string) $product->id;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $qty;
        } else {
            $img = null;
            // Use variation image if it has one, otherwise fall back to product image
            if ($variation?->image) {
                $img = asset('storage/' . $variation->image);
            } elseif ($product->primary_photo) {
                $img = asset('storage/' . $product->primary_photo);
            } elseif ($product->photos->first()) {
                $img = asset('storage/' . $product->photos->first()->photo_path);
            }

            $cart[$cartKey] = [
                'name'            => $product->name,
                'slug'            => $product->slug,
                'price'           => $effectivePrice,
                'sale_price'      => $effectiveSalePrice,
                'quantity'        => $qty,
                'image'           => $img,
                'category'        => $product->category->name ?? null,
                'product_id'      => $product->id,
                'variation_id'    => $variationId,
                'variation_label' => $variationLabel,
            ];
        }

        session(['cart' => $cart]);

        // ── CAPI: AddToCart ───────────────────────────────────────
        $atcEventId  = Str::uuid()->toString();
        $atcPrice    = (float) ($effectiveSalePrice ?? $effectivePrice ?? 0);
        $atcContentId = $variationId ? (string) $variationId : (string) $product->id;
        SendFacebookCapiEvent::dispatch(
            'AddToCart',
            $this->capiRequestData(),
            [
                'value'        => round($atcPrice / 2, 2),
                'content_ids'  => [$atcContentId],
                'content_name' => $product->name,
                'content_type' => 'product',
                'num_items'    => $qty,
            ],
            $atcEventId,
            request()->url()
        );

        return response()->json([
            'success'        => true,
            'cart_count'     => count($cart),
            'cart_qty'       => array_sum(array_column($cart, 'quantity')),
            'message'        => 'পণ্য কার্টে যোগ হয়েছে!',
            'product_price'  => $atcPrice,
            'product_name'   => $product->name,
            'atc_event_id'   => $atcEventId,
            'atc_content_id' => $atcContentId,
            'variation_id'   => $variationId,
        ]);
    }

    // ============================================================
    // CART: UPDATE
    // ============================================================
    public function cartUpdate(Request $request, string $productId): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = (int) $request->quantity;
            session(['cart' => $cart]);
        }

        return back()->with('success', 'কার্ট আপডেট হয়েছে।');
    }

    // ============================================================
    // CART: REMOVE ITEM
    // ============================================================
    public function cartRemove(string $productId): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$productId]);
        session(['cart' => $cart]);

        return back()->with('success', 'পণ্য সরিয়ে দেওয়া হয়েছে।');
    }

    // ============================================================
    // CART: CLEAR
    // ============================================================
    public function cartClear(): RedirectResponse
    {
        session()->forget('cart');
        return back()->with('success', 'কার্ট পরিষ্কার করা হয়েছে।');
    }

    // ============================================================
    // BLOGS LIST
    // ============================================================
    public function blogs(Request $request): View
    {
        $search = $request->input('search', '');

        $query = Blog::where('status', Blog::STATUS_ACTIVE)
            ->withCount('faqs')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('meta_description', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $blogs = $query->paginate(9)->withQueryString();

        return view('frontend.pages.blogs', compact('blogs', 'search'));
    }

    // ============================================================
    // BLOG DETAIL
    // ============================================================
    public function blogDetail(string $slug): View
    {
        $blog = Blog::where('slug', $slug)
            ->where('status', Blog::STATUS_ACTIVE)
            ->with('faqs')
            ->firstOrFail();

        // Increment view count (throttle per session to avoid refresh abuse)
        $viewedKey = 'blog_viewed_' . $blog->id;
        if (!session()->has($viewedKey)) {
            $blog->incrementViewCount();
            session()->put($viewedKey, true);
        }

        $relatedBlogs = Blog::where('status', Blog::STATUS_ACTIVE)
            ->where('id', '!=', $blog->id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('frontend.pages.blog-detail', compact('blog', 'relatedBlogs'));
    }

    // ============================================================
    // PAGE DETAIL
    // ============================================================
    public function pageDetail(string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->where('status', Page::STATUS_ACTIVE)
            ->firstOrFail();

        return view('frontend.pages.page-detail', compact('page'));
    }

    // ============================================================
    // HELPER: Apply filters/sorting to query
    // ============================================================
    private function applyFilters($query, Request $request): void
    {
        // Variation filters. Selected values are ANDed across attributes and
        // ORed within one attribute, so "Color: Red or Blue" plus "Size: M"
        // matches products having an M in either colour.
        foreach ((array) $request->input('variation', []) as $valueIds) {
            $valueIds = array_values(array_filter((array) $valueIds, static fn ($id) => $id !== '' && $id !== null));

            if ($valueIds === []) {
                continue;
            }

            $query->whereHas('variations', fn ($variationQuery) => $variationQuery
                ->where('status', 1)
                ->whereHas('variationAttributes', fn ($attributeQuery) => $attributeQuery
                    ->whereIn('attribute_value_id', $valueIds)));
        }

        if ($request->filled('min_price')) {
            $query->where(fn($q) => $q
                ->where('sale_price', '>=', $request->min_price)
                ->orWhere(fn($q2) => $q2->whereNull('sale_price')->where('price', '>=', $request->min_price))
            );
        }

        if ($request->filled('max_price')) {
            $query->where(fn($q) => $q
                ->where('sale_price', '<=', $request->max_price)
                ->orWhere(fn($q2) => $q2->whereNull('sale_price')->where('price', '<=', $request->max_price))
            );
        }

        switch ($request->get('sort', 'newest')) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'popular':
                $query->inRandomOrder();
                break;
            case 'discount':
                $query->orderByRaw('(price - COALESCE(sale_price, price)) DESC');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    }

    // ============================================================
    // CONTACT US
    // ============================================================
    public function contact(): View
    {
        return view('frontend.pages.contact');
    }

    public function submitContact(StoreContactRequest $request): RedirectResponse
    {
        ContactMessage::storeMessage($request);
        return redirect()->route('shop.contact')
            ->with('success', "Your message has been sent! We'll get back to you soon.");
    }
}



