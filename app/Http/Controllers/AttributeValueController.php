<?php

namespace App\Http\Controllers;

use App\Models\AttributeName;
use App\Models\AttributeValue;
use App\Http\Requests\StoreAttributeValueRequest;
use App\Http\Requests\UpdateAttributeValueRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    final public function index(): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Value List',
            'module'       => 'Attribute Value',
            'module_url'   => route('attribute-value.index'),
            'sub_module'   => 'List',
            'button_title' => 'Attribute Value Create',
            'button_url'   => route('attribute-value.create'),
            'type'         => 'list'
        ];
        $attributeValues = (new AttributeValue())->getAttributeValues();
        return view('backend.modules.attribute-value.index', compact('cms_content', 'attributeValues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    final public function create(): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Value Create',
            'module'       => 'Attribute Value',
            'module_url'   => route('attribute-value.index'),
            'sub_module'   => 'Create',
            'button_title' => 'Attribute Value List',
            'button_url'   => route('attribute-value.index'),
            'type'         => 'list'
        ];
        $attributeNames = (new AttributeName())->getAttributeNameAssoc();
        return view('backend.modules.attribute-value.create', compact('cms_content', 'attributeNames'));
    }

    /**
     * Store a newly created resource in storage.
     */
    final public function store(StoreAttributeValueRequest $request): RedirectResponse
    {
        try {
            $attributeValue = (new AttributeValue())->storeAttributeValue($request);
            success_alert($attributeValue->name . ' created successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Value Store', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('attribute-value.index');
    }

    /**
     * Display the specified resource.
     */
    final public function show(AttributeValue $attributeValue): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Value Details',
            'module'       => 'Attribute Value',
            'module_url'   => route('attribute-value.index'),
            'sub_module'   => 'Details',
            'button_title' => 'Attribute Value List',
            'button_url'   => route('attribute-value.index'),
            'type'         => 'list'
        ];
        return view('backend.modules.attribute-value.show', compact('cms_content', 'attributeValue'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    final public function edit(AttributeValue $attributeValue): View
    {
        $cms_content = [
            'page_title'   => 'Attribute Value Edit',
            'module'       => 'Attribute Value',
            'module_url'   => route('attribute-value.index'),
            'sub_module'   => 'Edit',
            'button_title' => 'Attribute Value List',
            'button_url'   => route('attribute-value.index'),
            'type'         => 'list'
        ];
        $attributeNames = (new AttributeName())->getAttributeNameAssoc();
        return view('backend.modules.attribute-value.edit', compact('cms_content', 'attributeValue', 'attributeNames'));
    }

    /**
     * Update the specified resource in storage.
     */
    final public function update(UpdateAttributeValueRequest $request, AttributeValue $attributeValue): RedirectResponse
    {
        try {
            $attributeValue->updateAttributeValue($request);
            success_alert($attributeValue->name . ' updated successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Value Update', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
            return redirect()->back();
        }
        return redirect()->route('attribute-value.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    final public function destroy(AttributeValue $attributeValue): RedirectResponse
    {
        try {
            $attributeValue->delete();
            success_alert($attributeValue->name . ' deleted successfully.');
        } catch (Throwable $throwable) {
            app_error_log('Attribute Value Delete', $throwable);
            failed_alert('Something went wrong. ' . $throwable->getMessage());
        }
        return redirect()->route('attribute-value.index');
    }
}
