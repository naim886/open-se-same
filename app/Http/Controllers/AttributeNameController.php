<?php

namespace App\Http\Controllers;

use App\Models\AttributeName;
use App\Http\Requests\StoreAttributeNameRequest;
use App\Http\Requests\UpdateAttributeNameRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AttributeNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Name List',
            'module'       => 'Attribute Name',
            'module_url'   => route('attribute-name.index'),
            'sub_module'   => 'List',
            'button_title' => 'Attribute Name Create',
            'button_url'   => route('attribute-name.create'),
            'type'         => 'list'
        ];
        $attributeNames = (new AttributeName())->getAttributeNames();
        return view('backend.modules.attribute-name.index', compact('cms_content', 'attributeNames'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Name Create',
            'module'       => 'Attribute Name',
            'module_url'   => route('attribute-name.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Attribute Name List',
            'button_url'   => route('attribute-name.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.attribute-name.create', compact('cms_content'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeNameRequest $request): RedirectResponse
    {
        try {
            $attributeName = (new AttributeName())->storeAttributeName($request);
            success_alert($attributeName->name . ' created successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Name Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('attribute-name.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeName $attributeName): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Name Details',
            'module'       => 'Attribute Name',
            'module_url'   => route('attribute-name.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Attribute Name List',
            'button_url'   => route('attribute-name.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.attribute-name.show', compact('cms_content', 'attributeName'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttributeName $attributeName): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Name Edit',
            'module'       => 'Attribute Name',
            'module_url'   => route('attribute-name.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Attribute Name List',
            'button_url'   => route('attribute-name.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.attribute-name.edit', compact('cms_content', 'attributeName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeNameRequest $request, AttributeName $attributeName): RedirectResponse
    {
        try {
            $attributeName->updateAttributeName($request);
            success_alert($attributeName->name . ' updated successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Name Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('attribute-name.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttributeName $attributeName): RedirectResponse
    {
        try {
            $attributeName->delete();
            success_alert($attributeName->name . ' deleted successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Name Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
        }
        return redirect()->route('attribute-name.index');
    }
}
