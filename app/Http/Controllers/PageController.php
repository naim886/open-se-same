<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = (new Page())->getPages();

        $cms_content = [
            'page_title'   => 'Pages',
            'module'       => 'Page',
            'module_url'   => route('page.index'),
            'sub_module'   => 'List',
            'button_title' => 'Add Page',
            'button_url'   => route('page.create'),
            'type'         => 'create',
        ];

        return view('backend.modules.page.index', compact('cms_content', 'pages'));
    }

    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Create Page',
            'module'       => 'Page',
            'module_url'   => route('page.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Page List',
            'button_url'   => route('page.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.page.create', compact('cms_content'));
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        try {
            $page = (new Page())->storePage($request);
            success_alert($page->title . ' created successfully.');
            return redirect()->route('page.index');
        } catch (Throwable $e) {
            app_error_log('Page Store', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(Page $page): View
    {
        $cms_content = [
            'page_title'   => 'Page Details',
            'module'       => 'Page',
            'module_url'   => route('page.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Page List',
            'button_url'   => route('page.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.page.show', compact('cms_content', 'page'));
    }

    public function edit(Page $page): View
    {
        $cms_content = [
            'page_title'   => 'Edit Page',
            'module'       => 'Page',
            'module_url'   => route('page.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Page List',
            'button_url'   => route('page.index'),
            'type'         => 'list',
        ];

        return view('backend.modules.page.edit', compact('cms_content', 'page'));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        try {
            $page->updatePage($request);
            success_alert($page->title . ' updated successfully.');
            return redirect()->route('page.index');
        } catch (Throwable $e) {
            app_error_log('Page Update', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Page $page): RedirectResponse
    {
        try {
            if ($page->cover_photo) {
                \Storage::disk('public')->delete($page->cover_photo);
            }
            $page->delete();
            success_alert($page->title . ' deleted successfully.');
        } catch (Throwable $e) {
            app_error_log('Page Destroy', $e);
            failed_alert('Something went wrong. ' . $e->getMessage());
        }

        return redirect()->route('page.index');
    }
}
