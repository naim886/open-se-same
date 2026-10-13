<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteContentRequest;
use App\Http\Requests\UpdateSiteContentRequest;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SiteContentController extends Controller
{
    public function index(): View
    {
        $contents = (new SiteContent())->getContents();

        $cms_content = [
            'page_title'   => 'Site Contents',
            'module'       => 'Site Content',
            'module_url'   => route('site-content.index'),
            'sub_module'   => 'List',
            'button_title' => 'Add Content',
            'button_url'   => route('site-content.create'),
            'type'         => 'create',
        ];

        return view('backend.modules.site-content.index', compact('cms_content', 'contents'));
    }

    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Add Site Content',
            'module'       => 'Site Content',
            'module_url'   => route('site-content.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Content List',
            'button_url'   => route('site-content.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.site-content.create', compact('cms_content'));
    }

    public function store(StoreSiteContentRequest $request): RedirectResponse
    {
        try {
            (new SiteContent())->storeContent($request);
            success_alert('Content "' . $request->input('label') . '" created successfully.');
            return redirect()->route('site-content.index');
        } catch (Throwable $e) {
            app_error_log('SiteContent Store', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(SiteContent $siteContent): View
    {
        $cms_content = [
            'page_title'   => 'Edit Content',
            'module'       => 'Site Content',
            'module_url'   => route('site-content.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Content List',
            'button_url'   => route('site-content.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.site-content.edit', compact('cms_content', 'siteContent'));
    }

    public function update(UpdateSiteContentRequest $request, SiteContent $siteContent): RedirectResponse
    {
        try {
            $siteContent->updateContent($request);
            success_alert('Content updated successfully.');
            return redirect()->route('site-content.index');
        } catch (Throwable $e) {
            app_error_log('SiteContent Update', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(SiteContent $siteContent): RedirectResponse
    {
        try {
            $siteContent->delete();
            success_alert('Content deleted successfully.');
        } catch (Throwable $e) {
            app_error_log('SiteContent Destroy', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
        }

        return redirect()->route('site-content.index');
    }
}
