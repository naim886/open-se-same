<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cms_content = [
            'page_title' => 'Category List',
            'module' => 'Category',
        ];

        $categories = (new Category())->getCategories();
        return view('backend.modules.category.index', compact('cms_content', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    final public function create(): View
    {
        $cms_content = [
            'page_title' => 'Category Create',
            'module' => 'Category',
            'module_url' => route('category.index'),
            'sub_module' => 'Create',
            'button_title' => 'Category List',
            'button_url' => route('category.index'),
            'type' => 'list'
        ];

        $parentCategories = (new Category())->getParentOptions();

        return view('backend.modules.category.create', compact('cms_content', 'parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    final public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            $category = (new Category())->storeCategory($request);
            success_alert($category->name . ' created successfully.');
            return redirect()->route('category.index');
        } catch (Throwable $throwable) {
            app_error_log('Category Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $cms_content = [
            'page_title' => 'Category Details',
            'module' => 'Category',
            'module_url' => route('category.index'),
            'sub_module' => 'Details',
            'button_title' => 'Category List',
            'button_url' => route('category.index'),
            'type' => 'list'
        ];

        return view('backend.modules.category.show', compact('cms_content', 'category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $cms_content = [
            'page_title' => 'Category Edit',
            'module' => 'Category',
            'module_url' => route('category.index'),
            'sub_module' => 'Edit',
            'button_title' => 'Category List',
            'button_url' => route('category.index'),
            'type' => 'list'
        ];

        $parentCategories = (new Category())->getParentOptions($category->id);

        return view('backend.modules.category.edit', compact('cms_content', 'category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $category->updateCategory($request);
            success_alert($category->name . ' updated successfully.');
            return redirect()->route('category.index');
        } catch (Throwable $throwable) {
            app_error_log('Category Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        try {
            $categoryName = $category->name;
            $parentId     = $category->category_id ? (int) $category->category_id : null;

            // Products whose primary category is being removed fall back to
            // the deleted category's parent (or no category for a root one).
            Product::query()
                ->where('category_id', $category->id)
                ->update(['category_id' => $parentId]);

            // Remove the category from every product's additional categories.
            $category->products()->detach();

            // Keep the category tree valid by moving child categories up one
            // level instead of leaving them pointing at a deleted parent.
            Category::query()
                ->where('category_id', $category->id)
                ->update(['category_id' => $parentId]);

            // Delete category images if they exist
            if ($category->icon) {
                $iconPath = str_replace(asset('storage'), '', $category->getRawOriginal('icon'));
                Storage::disk('public')->delete($iconPath);
            }

            if ($category->banner) {
                $bannerPath = str_replace(asset('storage'), '', $category->getRawOriginal('banner'));
                Storage::disk('public')->delete($bannerPath);
            }

            $category->delete();

            success_alert($categoryName . ' deleted successfully.');
            return redirect()->route('category.index');
        } catch (Throwable $throwable) {
            app_error_log('Category Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
    }
}
