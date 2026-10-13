<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $banners = (new Banner())->getBanners();

        $cms_content = [
            'page_title'   => 'Banner List',
            'module'       => 'Banner',
            'module_url'   => route('banner.index'),
            'sub_module'   => 'List',
            'button_title' => 'Add Banner',
            'button_url'   => route('banner.create'),
            'type'         => 'create'
        ];

        return view('backend.modules.banner.index', compact('cms_content', 'banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Banner Create',
            'module'       => 'Banner',
            'module_url'   => route('banner.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Banner List',
            'button_url'   => route('banner.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.banner.create', compact('cms_content'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $request): RedirectResponse
    {
        try {
            $banner = (new Banner())->storeBanner($request);
            success_alert($banner->title . ' created successfully.');
            return redirect()->route('banner.index');
        } catch (Throwable $throwable) {
            app_error_log('Banner Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner): View
    {
        $cms_content = [
            'page_title'   => 'Banner Details',
            'module'       => 'Banner',
            'module_url'   => route('banner.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Banner List',
            'button_url'   => route('banner.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.banner.show', compact('cms_content', 'banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner): View
    {
        $cms_content = [
            'page_title'   => 'Banner Edit',
            'module'       => 'Banner',
            'module_url'   => route('banner.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Banner List',
            'button_url'   => route('banner.index'),
            'type'         => 'list'
        ];

        return view('backend.modules.banner.edit', compact('cms_content', 'banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        try {
            $banner->updateBanner($request);
            success_alert($banner->title . ' updated successfully.');
            return redirect()->route('banner.index');
        } catch (Throwable $throwable) {
            app_error_log('Banner Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        try {
            // Delete the banner image if exists
            if ($banner->image) {
                \Storage::disk('public')->delete($banner->image);
            }

            $banner->delete();
            success_alert($banner->title . ' deleted successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Banner Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
        }

        return redirect()->route('banner.index');
    }
}
