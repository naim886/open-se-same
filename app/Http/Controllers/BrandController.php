<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cms_content = [
            'page_title' => 'Brand List',
            'module' => 'Brand',
        ];

        $brands = (new Brand())->getBrands();
        return view('backend.modules.brand.index', compact('cms_content', 'brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Brand Create',
            'module'       => 'Brand',
            'module_url'   => route('brand.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Brand List',
            'button_url'   => route('brand.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.brand.create', compact('cms_content'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        try {
            $brand = (new Brand())->storeBrand($request);
            success_alert($brand->name . ' created successfully.');
            return redirect()->route('brand.index');
        } catch (Throwable $throwable) {
            app_error_log('Brand Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): View
    {
        $cms_content = [
            'page_title'   => 'Brand Details',
            'module'       => 'Brand',
            'module_url'   => route('brand.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Brand List',
            'button_url'   => route('brand.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.brand.show', compact('cms_content', 'brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand): View
    {
        $cms_content = [
            'page_title'   => 'Brand Edit',
            'module'       => 'Brand',
            'module_url'   => route('brand.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Brand List',
            'button_url'   => route('brand.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.brand.edit', compact('cms_content', 'brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        try {
            $brand->updateBrand($request);
            success_alert($brand->name . ' updated successfully.');
            return redirect()->route('brand.index');
        } catch (Throwable $throwable) {
            app_error_log('Brand Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            if ($brand->products()->count() > 0) {
                failed_alert('Cannot delete brand that has products assigned to it.');
                return redirect()->back();
            }

            $brand->deleteImage($brand->getRawOriginal('logo'), Brand::LOGO_PATH);
            $brand->deleteImage($brand->getRawOriginal('banner'), Brand::BANNER_PATH);

            $brandName = $brand->name;
            $brand->delete();

            success_alert($brandName . ' deleted successfully.');
            return redirect()->route('brand.index');
        } catch (Throwable $throwable) {
            app_error_log('Brand Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
    }
}
