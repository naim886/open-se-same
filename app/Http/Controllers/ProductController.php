<?php

namespace App\Http\Controllers;

use App\Models\AttributeName;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Specification;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Jobs\ImportProductsCsv;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    /**
     * Show the CSV import screen.
     */
    public function importForm(): View
    {
        $cms_content = [
            'page_title'   => 'Product CSV Import',
            'module'       => 'Product',
            'module_url'   => route('product.index'),
            'sub_module'   => 'Import',
            'button_title' => 'Product List',
            'button_url'   => route('product.index'),
            'type'         => 'list',
        ];

        $importStatus = Cache::get('product_csv_import_status');

        return view('backend.modules.product.import', compact('cms_content', 'importStatus'));
    }

    /**
     * Queue a CSV import.
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:51200',
        ]);

        try {
            $path = $request->file('csv')->storeAs(
                'imports',
                'products-' . now()->format('YmdHis') . '.csv'
            );

            ImportProductsCsv::dispatch(Storage::path($path), [
                'download_images'   => $request->boolean('download_images'),
                'import_variations' => $request->boolean('import_variations'),
                'update_existing'   => $request->boolean('update_existing'),
                'replace_images'    => $request->boolean('replace_images'),
                'limit'             => (int) $request->input('limit', 0),
            ]);

            Cache::put('product_csv_import_status', [
                'state'     => 'queued',
                'queued_at' => now()->toDateTimeString(),
            ], now()->addDay());

            success_alert('CSV import queued. Products will appear as the background job processes the file.');
        } catch (Throwable $throwable) {
            app_error_log('Product CSV Import', $throwable);
            failed_alert('Could not queue the CSV import. ' . $throwable->getMessage());
        }

        return redirect()->route('product.import');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $cms_content = [
            'page_title'   => 'Product List',
            'module'       => 'Product',
            'module_url'   => route('product.index'),
            'sub_module'   => 'List',
            'button_title' => 'Product Create',
            'button_url'   => route('product.create'),
            'type'         => 'list'
        ];

        $filters = $request->only([
            'name',
            'slug',
            'sku',
            'category_id',
            'brand_id',
            'product_type',
            'status',
        ]);

        $products   = (new Product())->getProducts($filters);
        $categories = (new Category())->getCategoryAssoc();
        $brands     = (new Brand())->getBrandAssoc();

        return view('backend.modules.product.index', compact('cms_content', 'products', 'categories', 'brands', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Product Create',
            'module'       => 'Product',
            'module_url'   => route('product.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Product List',
            'button_url'   => route('product.index'),
            'type'         => 'list'
        ];

        $categories = (new Category())->getCategoryAssoc();
        $brands = (new Brand())->getBrandAssoc();
        $specifications = (new Specification())->getSpecificationAssoc();
        $attributeNames = (new AttributeName())->getAttributeNameAssoc();
        $attributeValues = AttributeValue::with('attributeName')
            ->where('status', 1)
            ->get()
            ->groupBy('attribute_name_id')
            ->map(function ($values) {
                return $values->pluck('name', 'id');
            });

        return view('backend.modules.product.create', compact('cms_content', 'categories', 'brands', 'specifications', 'attributeNames', 'attributeValues'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $product = (new Product())->storeProduct($request);
            success_alert($product->name . ' created successfully.');
            return redirect()->route('product.index');
        } catch (Throwable $throwable) {
            app_error_log('Product Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $product->load(['photos', 'specifications.specification']);

        $cms_content = [
            'page_title'   => 'Product Details',
            'module'       => 'Product',
            'module_url'   => route('product.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Product List',
            'button_url'   => route('product.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.product.show', compact('cms_content', 'product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $cms_content = [
            'page_title'   => 'Product Edit',
            'module'       => 'Product',
            'module_url'   => route('product.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Product List',
            'button_url'   => route('product.index'),
            'type'         => 'list'
        ];

        $categories = (new Category())->getCategoryAssoc();
        $brands = (new Brand())->getBrandAssoc();
        $specifications = (new Specification())->getSpecificationAssoc();
        $attributeNames = (new AttributeName())->getAttributeNameAssoc();
        $product->load(['categories', 'photos', 'specifications.specification', 'variations.variationAttributes.attributeName', 'variations.variationAttributes.attributeValue']);

        $attributeValues = AttributeValue::with('attributeName')
            ->where('status', 1)
            ->get()
            ->groupBy('attribute_name_id')
            ->map(function ($values) {
                return $values->pluck('name', 'id');
            });

        return view('backend.modules.product.edit', compact('cms_content', 'product', 'categories', 'brands', 'specifications', 'attributeNames', 'attributeValues'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {

        try {
            $product->updateProduct($request);
            success_alert($product->name . ' updated successfully.');
            return redirect()->route('product.index');
        } catch (Throwable $throwable) {
            app_error_log('Product Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $productName = $product->name;
            $product->delete();
            success_alert($productName . ' deleted successfully.');
            return redirect()->route('product.index');
        } catch (Throwable $throwable) {
            app_error_log('Product Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
    }
}
