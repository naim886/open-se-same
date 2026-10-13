<?php

namespace App\Http\Controllers;

use App\Models\Specification;
use App\Http\Requests\StoreSpecificationRequest;
use App\Http\Requests\UpdateSpecificationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class SpecificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cms_content = [
            'page_title'   => 'Specification List',
            'module'       => 'Specification',
            'module_url'   => route('specification.index'),
            'sub_module'   => 'List',
            'button_title' => 'Specification Create',
            'button_url'   => route('specification.create'),
            'type'         => 'list'
        ];
        $specifications = (new Specification())->getSpecifications();
        return view('backend.modules.specification.index', compact('cms_content', 'specifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Specification Create',
            'module'       => 'Specification',
            'module_url'   => route('specification.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Specification List',
            'button_url'   => route('specification.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.specification.create', compact('cms_content'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecificationRequest $request): RedirectResponse
    {
        try {
            $specification = (new Specification())->storeSpecification($request);
            success_alert($specification->specifications_name . ' created successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Specification Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('specification.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Specification $specification): View
    {
        $cms_content = [
            'page_title'   => 'Specification Details',
            'module'       => 'Specification',
            'module_url'   => route('specification.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Specification List',
            'button_url'   => route('specification.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.specification.show', compact('cms_content', 'specification'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Specification $specification): View
    {
        $cms_content = [
            'page_title'   => 'Specification Edit',
            'module'       => 'Specification',
            'module_url'   => route('specification.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Specification List',
            'button_url'   => route('specification.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.specification.edit', compact('cms_content', 'specification'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecificationRequest $request, Specification $specification): RedirectResponse
    {
        try {
            $specification->updateSpecification($request);
            success_alert($specification->specifications_name . ' updated successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Specification Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('specification.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specification $specification): RedirectResponse
    {
        try {
            $specification->delete();
            success_alert($specification->specifications_name . ' deleted successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Specification Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
        }
        return redirect()->route('specification.index');
    }
}
