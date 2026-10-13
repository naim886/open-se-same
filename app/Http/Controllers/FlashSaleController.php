<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class FlashSaleController extends Controller
{
    // ── LIST ──────────────────────────────────────────────────────
    public function index(): View
    {
        $flashSales = FlashSale::withCount('products')
            ->orderByDesc('created_at')
            ->paginate(20);

        $cms_content = [
            'page_title'   => 'Flash Sales',
            'module'       => 'Flash Sale',
            'module_url'   => route('flash-sale.index'),
            'sub_module'   => 'List',
            'button_title' => 'Create Flash Sale',
            'button_url'   => route('flash-sale.create'),
            'type'         => 'create',
        ];

        return view('backend.modules.flash-sale.index', compact('cms_content', 'flashSales'));
    }

    // ── CREATE FORM ───────────────────────────────────────────────
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Create Flash Sale',
            'module'       => 'Flash Sale',
            'module_url'   => route('flash-sale.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Flash Sale List',
            'button_url'   => route('flash-sale.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.flash-sale.create', compact('cms_content'));
    }

    // ── STORE ─────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'starts_at'  => 'required|date',
            'ends_at'    => 'required|date|after:starts_at',
            'status'     => 'required|in:0,1',
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'sale_prices'   => 'nullable|array',
            'sale_prices.*' => 'nullable|numeric|min:0',
        ]);

        try {
            $flashSale = FlashSale::create([
                'name'       => $request->name,
                'starts_at'  => $request->starts_at,
                'ends_at'    => $request->ends_at,
                'status'     => $request->status,
            ]);

            $this->syncProducts($flashSale, $request);

            success_alert($flashSale->name . ' created successfully.');
            return redirect()->route('flash-sale.index');
        } catch (Throwable $e) {
            app_error_log('FlashSale Store', $e);
            failed_alert('Something went wrong: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // ── EDIT FORM ─────────────────────────────────────────────────
    public function edit(FlashSale $flashSale): View
    {
        $flashSale->load(['products']);

        $cms_content = [
            'page_title'   => 'Edit Flash Sale',
            'module'       => 'Flash Sale',
            'module_url'   => route('flash-sale.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Flash Sale List',
            'button_url'   => route('flash-sale.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.flash-sale.edit', compact('cms_content', 'flashSale'));
    }

    // ── UPDATE ────────────────────────────────────────────────────
    public function update(Request $request, FlashSale $flashSale): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'starts_at'  => 'required|date',
            'ends_at'    => 'required|date|after:starts_at',
            'status'     => 'required|in:0,1',
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'sale_prices'   => 'nullable|array',
            'sale_prices.*' => 'nullable|numeric|min:0',
        ]);

        try {
            $flashSale->update([
                'name'       => $request->name,
                'starts_at'  => $request->starts_at,
                'ends_at'    => $request->ends_at,
                'status'     => $request->status,
            ]);

            $this->syncProducts($flashSale, $request);

            success_alert($flashSale->name . ' updated successfully.');
            return redirect()->route('flash-sale.index');
        } catch (Throwable $e) {
            app_error_log('FlashSale Update', $e);
            failed_alert('Something went wrong: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // ── DELETE ────────────────────────────────────────────────────
    public function destroy(FlashSale $flashSale): RedirectResponse
    {
        try {
            $flashSale->delete();
            success_alert('Flash sale deleted successfully.');
        } catch (Throwable $e) {
            app_error_log('FlashSale Destroy', $e);
            failed_alert('Could not delete: ' . $e->getMessage());
        }
        return redirect()->route('flash-sale.index');
    }

    // ── AJAX: Product search ──────────────────────────────────────
    public function searchProducts(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $excludeIds = array_filter(array_map('intval', (array) $request->get('exclude', [])));

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($excludeIds, fn($query) => $query->whereNotIn('id', $excludeIds))
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

    // ── Products by category ─────────────────────────────────────
    public function productsByCategory(Request $request): JsonResponse
    {
        $categoryId = intval($request->get('category_id'));
        $excludeIds = array_filter(array_map('intval', (array) $request->get('exclude', [])));

        if (!$categoryId) {
            return response()->json([]);
        }

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $categoryId)
            ->when($excludeIds, fn($q) => $q->whereNotIn('id', $excludeIds))
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

    // ── Private: sync pivot ───────────────────────────────────────
    private function syncProducts(FlashSale $flashSale, Request $request): void
    {
        $productIds  = array_filter((array) $request->input('product_ids', []));
        $salePrices  = (array) $request->input('sale_prices', []);

        $syncData = [];
        foreach ($productIds as $idx => $productId) {
            $price = isset($salePrices[$idx]) && $salePrices[$idx] !== '' && $salePrices[$idx] !== null
                ? (float) $salePrices[$idx]
                : null;
            $syncData[$productId] = ['sale_price' => $price];
        }

        $flashSale->products()->sync($syncData);
    }
}
