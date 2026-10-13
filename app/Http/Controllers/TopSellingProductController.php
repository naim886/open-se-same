<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TopSellingProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopSellingProductController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────────
    public function index(): View
    {
        $items = TopSellingProduct::with('product')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(30);

        $cms_content = [
            'page_title'   => 'Top Selling Products',
            'module'       => 'Top Selling',
            'module_url'   => route('top-selling.index'),
            'sub_module'   => 'List',
            'button_title' => 'Add Product',
            'button_url'   => route('top-selling.create'),
            'type'         => 'create',
        ];

        return view('backend.modules.top-selling.index', compact('items', 'cms_content'));
    }

    // ── CREATE ────────────────────────────────────────────────────
    public function create(): View
    {
        $cms_content = [
            'page_title' => 'Add Top Selling Product',
            'module'     => 'Top Selling',
            'module_url' => route('top-selling.index'),
            'sub_module' => 'Add',
        ];

        return view('backend.modules.top-selling.create', compact('cms_content'));
    }

    // ── STORE ─────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        $existingIds = TopSellingProduct::pluck('product_id')->toArray();
        $maxOrder    = TopSellingProduct::max('sort_order') ?? 0;

        foreach ($request->product_ids as $productId) {
            if (!in_array($productId, $existingIds)) {
                TopSellingProduct::create([
                    'product_id'  => $productId,
                    'sort_order'  => ++$maxOrder,
                    'status'      => TopSellingProduct::STATUS_ACTIVE,
                ]);
            }
        }

        return redirect()->route('top-selling.index')
            ->with('success', 'Products added to Top Selling list.');
    }

    // ── EDIT ──────────────────────────────────────────────────────
    public function edit(TopSellingProduct $topSelling): View
    {
        $cms_content = [
            'page_title' => 'Edit Top Selling Entry',
            'module'     => 'Top Selling',
            'module_url' => route('top-selling.index'),
            'sub_module' => 'Edit',
        ];

        return view('backend.modules.top-selling.edit', compact('topSelling', 'cms_content'));
    }

    // ── UPDATE ────────────────────────────────────────────────────
    public function update(Request $request, TopSellingProduct $topSelling): RedirectResponse
    {
        $request->validate([
            'sort_order' => 'required|integer|min:0',
            'status'     => 'required|in:0,1',
        ]);

        $topSelling->update([
            'sort_order' => $request->sort_order,
            'status'     => $request->status,
        ]);

        return redirect()->route('top-selling.index')
            ->with('success', 'Updated successfully.');
    }

    // ── DESTROY ───────────────────────────────────────────────────
    public function destroy(TopSellingProduct $topSelling): RedirectResponse
    {
        $topSelling->delete();

        return redirect()->route('top-selling.index')
            ->with('success', 'Removed from Top Selling list.');
    }

    // ── REORDER (AJAX) ────────────────────────────────────────────
    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $order => $id) {
            TopSellingProduct::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    // ── SEARCH PRODUCTS (AJAX) ────────────────────────────────────
    public function searchProducts(Request $request): JsonResponse
    {
        $q          = trim($request->get('q', ''));
        $existingIds = TopSellingProduct::pluck('product_id')->toArray();

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($existingIds, fn($query) => $query->whereNotIn('id', $existingIds))
            ->select('id', 'name', 'price', 'sale_price', 'primary_photo')
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'price'      => $p->price,
                'sale_price' => $p->sale_price,
                'thumb'      => $p->primary_photo_thumb ? asset('storage/'.$p->primary_photo_thumb) : null,
            ]);

        return response()->json($products);
    }

    // ── PRODUCTS BY CATEGORY (AJAX) ───────────────────────────────
    public function productsByCategory(Request $request): JsonResponse
    {
        $categoryId  = intval($request->get('category_id'));
        $existingIds = TopSellingProduct::pluck('product_id')->toArray();

        if (!$categoryId) return response()->json([]);

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $categoryId)
            ->when($existingIds, fn($q) => $q->whereNotIn('id', $existingIds))
            ->select('id', 'name', 'price', 'sale_price', 'primary_photo')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'price'      => $p->price,
                'sale_price' => $p->sale_price,
                'thumb'      => $p->primary_photo_thumb ? asset('storage/'.$p->primary_photo_thumb) : null,
            ]);

        return response()->json($products);
    }
}
