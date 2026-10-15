<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\FlashSale;
use App\Models\NewsLetter;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\SearchKeyword;
use App\Models\SiteContent;
use App\Models\TopSellingProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobileApiController extends Controller
{
    // ============================================================
    // HELPERS
    // ============================================================

    private function ok(array $data, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], $status);
    }

    private function error(string $message, int $status = 422, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }

    /**
     * Safely build a storage URL.
     * If the stored value is already a full URL (e.g. was saved with the full URL in older records),
     * return it as-is to avoid double-prefixing like /storage/https://…/storage/…
     */
    private function storageUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Minimal shape used in product cards (listings, home, search).
     * Only eager-load 'category' — no 'photos' needed.
     */
    private function formatProductCard($product): array
    {
        return [
            'id'              => $product->id,
            'name'            => $product->name,
            'slug'            => $product->slug,
            'price'           => (float) $product->price,
            'sale_price'      => $product->sale_price !== null ? (float) $product->sale_price : null,
            'discount_percent' => $product->sale_price
                ? round((($product->price - $product->sale_price) / $product->price) * 100)
                : 0,
            'thumb'           => $this->storageUrl($product->primary_photo_thumb),
            'category'        => $product->relationLoaded('category') && $product->category
                ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug]
                : null,
        ];
    }

    /**
     * Full shape used only on the product detail screen.
     */
    private function formatProduct($product): array
    {
        $gallery = $product->relationLoaded('photos')
            ? $product->photos->map(fn($p) => [
                'full'  => $this->storageUrl($p->photo_path),
                'thumb' => $this->storageUrl(Product::toThumbPath($p->photo_path)),
            ])->values()->all()
            : [];

        return [
            'id'                => $product->id,
            'name'              => $product->name,
            'slug'              => $product->slug,
            'short_description' => $product->short_description,
            'description'       => $product->description ?? null,
            'price'             => (float) $product->price,
            'sale_price'        => $product->sale_price !== null ? (float) $product->sale_price : null,
            'discount_percent'  => $product->sale_price
                ? round((($product->price - $product->sale_price) / $product->price) * 100)
                : 0,
            'stock'             => $product->stock ?? null,
            'sku'               => $product->sku ?? null,
            'product_type'      => $product->product_type ?? Product::PRODUCT_TYPE_SIMPLE,
            'key_benefits'      => $product->key_benefits ?? [],
            'category'          => $product->relationLoaded('category') && $product->category
                ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug]
                : null,
            'primary_photo'       => $this->storageUrl($product->primary_photo),
            'primary_photo_thumb' => $this->storageUrl($product->primary_photo_thumb),
            'gallery'             => $gallery,
            'created_at'          => $product->created_at?->toIso8601String(),
        ];
    }

    private function applyFilters($query, Request $request): void
    {
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
    // HOME
    // GET /api/home
    // ============================================================
    public function home(): JsonResponse
    {
        $banners = Banner::where('status', Banner::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($b) => [
                'id'       => $b->id,
                'title'    => $b->title ?? null,
                'subtitle' => $b->subtitle ?? null,
                'image'    => $this->storageUrl($b->image),
                'link'     => $b->link ?? null,
            ]);

        $categories = Category::where('status', Category::STATUS_ACTIVE)
            ->withCount(['products' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)])
            ->orderBy('sort_order')
            ->limit(16)
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'slug'          => $c->slug,
                'icon'          => $this->storageUrl($c->icon),
                'banner'        => $this->storageUrl($c->banner),
                'products_count' => $c->products_count,
            ]);

        $featuredProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category'])
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(fn($p) => $this->formatProductCard($p));

        $newProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => $this->formatProductCard($p));

        // Category-wise products (first 4 categories)
        $categoryWiseProducts = [];
        foreach ($categories->take(4) as $category) {
            $products = Product::where('status', Product::STATUS_ACTIVE)
                ->where('category_id', $category['id'])
                ->limit(5)
                ->get();

            if ($products->count() > 0) {
                $categoryWiseProducts[] = [
                    'category' => $category,
                    'products' => $products->map(fn($p) => $this->formatProductCard($p))->values(),
                ];
            }
        }

        $flashSale = FlashSale::running()
            ->with(['products' => fn($q) => $q->orderBy('flash_sale_products.id')])
            ->first();

        $flashSaleData = null;
        if ($flashSale) {
            $flashSaleData = [
                'id'        => $flashSale->id,
                'title'     => $flashSale->title ?? 'Flash Sale',
                'ends_at'   => $flashSale->ends_at?->toIso8601String(),
                'starts_at' => $flashSale->starts_at?->toIso8601String(),
                'products'  => $flashSale->products->map(function ($p) {
                    $card                     = $this->formatProductCard($p);
                    $card['sale_price']        = (float) $p->pivot->sale_price;
                    $card['discount_percent']  = $p->price > 0
                        ? round((($p->price - $p->pivot->sale_price) / $p->price) * 100)
                        : 0;
                    return $card;
                })->values(),
            ];
        }

        $topSellingEntries = TopSellingProduct::active()
            ->with(['product' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)->with(['category'])])
            ->orderBy('sort_order')
            ->get()
            ->pluck('product')
            ->filter();

        $topSellingProducts = ($topSellingEntries->isNotEmpty() ? $topSellingEntries : collect())
            ->map(fn($p) => $this->formatProductCard($p))
            ->values();

        $homeContent = SiteContent::get('home_page');

        return $this->ok([
            'banners'              => $banners,
            'categories'           => $categories,
            'featured_products'    => $featuredProducts,
            'new_products'         => $newProducts,
            'category_wise_products' => $categoryWiseProducts,
            'flash_sale'           => $flashSaleData,
            'top_selling_products' => $topSellingProducts,
            'home_content'         => $homeContent,
        ]);
    }

    // ============================================================
    // ALL CATEGORIES
    // GET /api/categories
    // ============================================================
    public function categories(): JsonResponse
    {
        $categories = Category::where('status', Category::STATUS_ACTIVE)
            ->withCount(['products' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)])
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'id'              => $c->id,
                'name'            => $c->name,
                'slug'            => $c->slug,
                'short_description' => $c->short_description ?? null,
                'icon'            => $this->storageUrl($c->icon),
                'banner'          => $this->storageUrl($c->banner),
                'products_count'  => $c->products_count,
            ]);

        return $this->ok(['categories' => $categories]);
    }

    // ============================================================
    // CATEGORY PRODUCTS
    // GET /api/category/{slug}?sort=newest&min_price=&max_price=&page=1
    // ============================================================
    public function category(Request $request, string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('status', Category::STATUS_ACTIVE)
            ->first();

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $category->id)
            ->with(['category']);

        $this->applyFilters($query, $request);

        $paginated = $query->paginate(20);

        return $this->ok([
            'category' => [
                'id'                => $category->id,
                'name'              => $category->name,
                'slug'              => $category->slug,
                'short_description' => $category->short_description ?? null,
                'icon'              => $this->storageUrl($category->icon),
                'banner'            => $this->storageUrl($category->banner),
            ],
            'products' => collect($paginated->items())->map(fn($p) => $this->formatProductCard($p))->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ============================================================
    // ALL PRODUCTS
    // GET /api/products?sort=newest&min_price=&max_price=&category=id&page=1
    // ============================================================
    public function products(Request $request): JsonResponse
    {
        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category']);

        if ($request->filled('category')) {
            $query->where('category_id', intval($request->category));
        }

        $this->applyFilters($query, $request);

        $paginated = $query->paginate(20);

        return $this->ok([
            'products' => collect($paginated->items())->map(fn($p) => $this->formatProductCard($p))->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ============================================================
    // OFFER / DISCOUNTED PRODUCTS
    // GET /api/products/offers?sort=discount&min_price=&max_price=&category=id&page=1
    // ============================================================
    public function offers(Request $request): JsonResponse
    {
        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->with(['category']);

        if ($request->filled('category')) {
            $query->where('category_id', intval($request->category));
        }

        if (!$request->filled('sort')) {
            $request->merge(['sort' => 'discount']);
        }

        $this->applyFilters($query, $request);

        $paginated = $query->paginate(24);

        $saleCategories = Category::where('status', Category::STATUS_ACTIVE)
            ->withCount(['products' => fn($q) => $q->where('status', Product::STATUS_ACTIVE)
                ->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'products_count' => $c->products_count]);

        $flashSale = FlashSale::running()->first();

        return $this->ok([
            'products' => collect($paginated->items())->map(fn($p) => $this->formatProductCard($p))->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'categories'  => $saleCategories,
            'flash_sale_active' => $flashSale !== null,
        ]);
    }

    // ============================================================
    // SEARCH PRODUCTS
    // GET /api/products/search?q=dress&sort=newest&page=1
    // ============================================================
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if ($q !== '') {
            SearchKeyword::record($q);
        }

        $query = Product::where('status', Product::STATUS_ACTIVE)
            ->with(['category'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
            });

        $this->applyFilters($query, $request);

        $paginated = $query->paginate(20);

        return $this->ok([
            'query'    => $q,
            'products' => collect($paginated->items())->map(fn($p) => $this->formatProductCard($p))->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ============================================================
    // PRODUCT DETAIL
    // GET /api/product/{slug}
    // ============================================================
    public function product(string $slug): JsonResponse
    {
        $product = Product::with(['photos', 'category', 'specifications.specification', 'qas'])
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->first();

        if (!$product) {
            return $this->error('Product not found.', 404);
        }

        FlashSale::applyFlashPrice($product);

        $relatedProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['photos'])
            ->limit(5)
            ->get()
            ->map(fn($p) => $this->formatProduct($p));

        $deliveryInfo = SiteContent::get('delivery_policy');
        $returnInfo   = SiteContent::get('return_policy');

        $formatted = $this->formatProduct($product);

        // Append detailed fields not in the listing format
        $formatted['specifications'] = $product->specifications->map(fn($s) => [
            'name'  => $s->specification?->name ?? $s->name ?? null,
            'value' => $s->value,
        ])->values();

        $formatted['qas'] = $product->qas->map(fn($qa) => [
            'question' => $qa->question,
            'answer'   => $qa->answer,
        ])->values();

        return $this->ok([
            'product'          => $formatted,
            'related_products' => $relatedProducts,
            'delivery_info'    => $deliveryInfo,
            'return_info'      => $returnInfo,
        ]);
    }

    // ============================================================
    // BLOGS
    // GET /api/blogs?search=&page=1
    // ============================================================
    public function blogs(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        $query = Blog::where('status', Blog::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('meta_description', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate(9);

        return $this->ok([
            'blogs' => collect($paginated->items())->map(fn($b) => [
                'id'               => $b->id,
                'title'            => $b->title,
                'slug'             => $b->slug,
                'author'           => $b->author ?? null,
                'meta_description' => $b->meta_description ?? null,
                'thumbnail'        => $this->storageUrl($b->thumbnail),
                'view_count'       => $b->view_count ?? 0,
                'created_at'       => $b->created_at?->toIso8601String(),
            ])->values(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ============================================================
    // BLOG DETAIL
    // GET /api/blog/{slug}
    // ============================================================
    public function blogDetail(string $slug): JsonResponse
    {
        $blog = Blog::where('slug', $slug)
            ->where('status', Blog::STATUS_ACTIVE)
            ->with('faqs')
            ->first();

        if (!$blog) {
            return $this->error('Blog post not found.', 404);
        }

        // Throttle view count per session on mobile (using IP as proxy)
        $blog->incrementViewCount();

        $relatedBlogs = Blog::where('status', Blog::STATUS_ACTIVE)
            ->where('id', '!=', $blog->id)
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn($b) => [
                'id'         => $b->id,
                'title'      => $b->title,
                'slug'       => $b->slug,
                'thumbnail'  => $this->storageUrl($b->thumbnail),
                'created_at' => $b->created_at?->toIso8601String(),
            ]);

        return $this->ok([
            'blog' => [
                'id'               => $blog->id,
                'title'            => $blog->title,
                'slug'             => $blog->slug,
                'author'           => $blog->author ?? null,
                'content'          => $blog->content ?? null,
                'meta_description' => $blog->meta_description ?? null,
                'thumbnail'        => $this->storageUrl($blog->thumbnail),
                'view_count'       => $blog->view_count ?? 0,
                'created_at'       => $blog->created_at?->toIso8601String(),
                'faqs'             => $blog->faqs->map(fn($f) => [
                    'question' => $f->question,
                    'answer'   => $f->answer,
                ])->values(),
            ],
            'related_blogs' => $relatedBlogs,
        ]);
    }

    // ============================================================
    // PAGE DETAIL (CMS)
    // GET /api/page/{slug}
    // ============================================================
    public function pageDetail(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('status', Page::STATUS_ACTIVE)
            ->first();

        if (!$page) {
            return $this->error('Page not found.', 404);
        }

        return $this->ok([
            'page' => [
                'id'               => $page->id,
                'title'            => $page->title,
                'slug'             => $page->slug,
                'content'          => $page->description ?? null,
                'meta_description' => $page->meta_description ?? null,
            ],
        ]);
    }

    // ============================================================
    // DELIVERY OPTIONS
    // GET /api/delivery-options
    // ============================================================
    public function deliveryOptions(): JsonResponse
    {
        $options = collect(Order::DELIVERY_LIST)->map(fn($label, $id) => [
            'id'    => $id,
            'label' => $label,
            'cost'  => Order::deliveryCost($id),
        ])->values();

        return $this->ok(['delivery_options' => $options]);
    }

    // ============================================================
    // TRACK ORDER
    // GET /api/track-order?q=01712345678   (phone or order number)
    // ============================================================
    public function trackOrder(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return $this->error('Please provide a phone number or order number.', 422);
        }

        // Normalise Bengali digits
        $query = str_replace(
            ['০','১','২','৩','৪','৫','৬','৭','৮','৯'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $query
        );

        // Strip "M-" prefix for order number lookups
        $normalizedQuery = preg_replace('/^[Mm]-?/', '', $query);

        $orders = Order::with(['orderItems.product.photos', 'histories'])
            ->where(function ($q) use ($query, $normalizedQuery) {
                $q->where('order_number', $normalizedQuery)
                  ->orWhere('order_number', $query)
                  ->orWhere('customer_phone', $query);
            })
            ->orderBy('order_date', 'desc')
            ->get()
            ->map(function (Order $order) {
                $timeline = $order->histories
                    ->filter(fn($h) => isset($h->changes['status']))
                    ->map(fn($h) => [
                        'from' => Order::STATUS_LIST[$h->changes['status']['from'] ?? null] ?? null,
                        'to'   => Order::STATUS_LIST[$h->changes['status']['to'] ?? null] ?? null,
                        'time' => $h->created_at?->toIso8601String(),
                    ])
                    ->filter(fn($row) => $row['to'] !== null)
                    ->values();

                return [
                    'order_number'     => 'M-' . $order->order_number,
                    'status'           => Order::STATUS_LIST[$order->status] ?? 'Unknown',
                    'status_code'      => $order->status,
                    'order_date'       => $order->order_date?->toIso8601String(),
                    'delivery_method'  => Order::DELIVERY_LIST[$order->delivery_location] ?? null,
                    'delivery_charge'  => (float) $order->delivery_charge,
                    'product_price'    => (float) $order->product_price,
                    'total_amount'     => (float) $order->total_amount,
                    'masked_phone'     => $this->maskPhone($order->customer_phone ?? ''),
                    'masked_address'   => $this->maskAddress($order->customer_address ?? ''),
                    'items'            => $order->orderItems->map(fn($item) => [
                        'product_name'  => $item->product?->name ?? 'Product',
                        'product_slug'  => $item->product?->slug ?? null,
                        'product_image' => $this->storageUrl(
                            $item->product?->primary_photo_thumb ?? $item->product?->primary_photo
                        ),
                        'unit_price'    => (float) $item->unit_price,
                        'quantity'      => (int) $item->quantity,
                        'subtotal'      => (float) $item->subtotal,
                    ])->values(),
                    'status_timeline'  => $timeline,
                ];
            });

        if ($orders->isEmpty()) {
            return $this->error('No orders found for the provided information.', 404);
        }

        return $this->ok(['orders' => $orders]);
    }

    // ============================================================
    // PLACE QUICK ORDER (single product)
    // POST /api/order/quick
    // Body: product_id, customer_name, customer_phone, customer_address,
    //       customer_email (optional), quantity, delivery_location, note (optional)
    // ============================================================
    public function placeQuickOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id'        => 'required|exists:products,id',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => ['required', 'digits:11'],
            'customer_address'  => 'required|string|max:500',
            'customer_email'    => 'nullable|email|max:255',
            'quantity'          => 'required|integer|min:1',
            'delivery_location' => 'required|in:1,2,3,4,5',
            'note'              => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $product = Product::findOrFail($request->product_id);
        $qty     = (int) $request->quantity;

        FlashSale::applyFlashPrice($product);

        $price = $product->sale_price ?? $product->price;
        $dc    = Order::deliveryCost($request->delivery_location);

        $payload = [
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'customer_address'  => $request->customer_address,
            'customer_email'    => $request->customer_email,
            'product_ids'       => [$product->id],
            'quantities'        => [$qty],
            'unit_prices'       => [(float) $price],
            'product_price'     => (float) $price * $qty,
            'delivery_location' => (int) $request->delivery_location,
            'delivery_charge'   => $dc,
            'paid_amount'       => 0,
            'order_total'       => ((float) $price * $qty) + $dc,
            'note'              => $request->note,
            'status'            => Order::STATUS_PENDING,
            'source'            => 'mobile_app',
        ];

        try {
            $order = (new Order())->storeOrder($payload);

            app(\App\Services\OrderNotificationService::class)->orderPlaced($order);

            return response()->json([
                'success'      => true,
                'message'      => 'Your order has been placed successfully!',
                'order_number' => 'M-' . $order->order_number,
                'summary'      => [
                    'product_price'  => $payload['product_price'],
                    'delivery_charge'=> $payload['delivery_charge'],
                    'total_amount'   => $payload['order_total'],
                ],
            ], 201);
        } catch (\Exception $e) {
            return $this->error('Failed to place order. Please try again.', 500);
        }
    }

    // ============================================================
    // PLACE CART ORDER (multiple products)
    // POST /api/order
    // Body: customer_name, customer_phone, customer_address, customer_email (opt),
    //       delivery_location, note (opt)
    //       items: [ { product_id, quantity }, ... ]
    // ============================================================
    public function placeOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => ['required', 'digits:11'],
            'customer_address'  => 'required|string|max:500',
            'customer_email'    => 'nullable|email|max:255',
            'delivery_location' => 'required|in:1,2,3,4,5',
            'note'              => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $productIds   = [];
        $quantities   = [];
        $unitPrices   = [];
        $productTotal = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->status !== Product::STATUS_ACTIVE) {
                return $this->error("Product ID {$item['product_id']} is not available.", 422);
            }

            FlashSale::applyFlashPrice($product);

            $price          = (float) ($product->sale_price ?? $product->price);
            $qty            = (int) $item['quantity'];
            $productIds[]   = $product->id;
            $quantities[]   = $qty;
            $unitPrices[]   = $price;
            $productTotal  += $price * $qty;
        }

        $dc = Order::deliveryCost($request->delivery_location);

        $payload = [
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'customer_address'  => $request->customer_address,
            'customer_email'    => $request->customer_email,
            'product_ids'       => $productIds,
            'quantities'        => $quantities,
            'unit_prices'       => $unitPrices,
            'product_price'     => $productTotal,
            'delivery_location' => (int) $request->delivery_location,
            'delivery_charge'   => $dc,
            'paid_amount'       => 0,
            'order_total'       => $productTotal + $dc,
            'note'              => $request->note,
            'status'            => Order::STATUS_PENDING,
            'source'            => 'mobile_app',
        ];

        try {
            $order = (new Order())->storeOrder($payload);

            app(\App\Services\OrderNotificationService::class)->orderPlaced($order);

            return response()->json([
                'success'      => true,
                'message'      => 'Your order has been placed successfully!',
                'order_number' => 'M-' . $order->order_number,
                'summary'      => [
                    'product_price'   => $productTotal,
                    'delivery_charge' => $dc,
                    'total_amount'    => $productTotal + $dc,
                ],
            ], 201);
        } catch (\Exception $e) {
            return $this->error('Failed to place order. Please try again.', 500);
        }
    }

    // ============================================================
    // CONTACT FORM
    // POST /api/contact
    // Body: name, email, phone (optional), subject, message
    // ============================================================
    public function submitContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        ContactMessage::storeMessage($request);

        return response()->json([
            'success' => true,
            'message' => "Your message has been sent. We'll get back to you soon!",
        ], 201);
    }

    // ============================================================
    // NEWSLETTER SUBSCRIBE
    // POST /api/newsletter
    // Body: email
    // ============================================================
    public function newsletter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255', 'unique:news_letters,email'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        NewsLetter::query()->create([
            'email'  => $request->email,
            'status' => NewsLetter::STATUS_ACTIVE,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have been subscribed to our newsletter!',
        ], 201);
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function maskPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        $len   = strlen($clean);
        if ($len === 0) return '';
        if ($len <= 7)  return str_repeat('*', $len);
        return substr($clean, 0, 3) . '***' . substr($clean, 7);
    }

    private function maskAddress(string $address): string
    {
        if (strlen($address) <= 15) return '***';
        return '***' . substr($address, 15);
    }
}
