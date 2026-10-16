<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Http\Requests\UpdateHomepageSettingsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class HomepageSettingController extends Controller
{
    /**
     * Show the homepage block builder.
     */
    public function index(): View
    {
        $cms_content = [
            'page_title' => 'Homepage Settings',
            'module'     => 'Homepage Settings',
        ];

        $sections   = HomepageSection::getSections();

        // "Parent › Child" labels so the admin can tell similarly named
        // categories apart when picking one for a block.
        $categories = Category::where('status', Category::STATUS_ACTIVE)
            ->with('parent:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static fn (Category $category) => [
                $category->id => $category->parent
                    ? $category->parent->name . ' › ' . $category->name
                    : $category->name,
            ]);

        return view('backend.modules.homepage-setting.index', compact('cms_content', 'sections', 'categories'));
    }

    /**
     * Replace the homepage blocks with the submitted set.
     */
    public function update(UpdateHomepageSettingsRequest $request): RedirectResponse
    {
        try {
            HomepageSection::syncSections($request->input('sections', []));
            success_alert('Homepage settings updated successfully.');
            return redirect()->route('homepage-settings.index');
        } catch (Throwable $throwable) {
            app_error_log('Homepage Settings Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
