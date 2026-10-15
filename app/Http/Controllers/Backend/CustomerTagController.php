<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CustomerTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerTagController extends Controller
{
    public function index(): View
    {
        $tags = CustomerTag::withCount('assignments')->orderBy('name')->get();

        return view('backend.modules.customer.tags', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:60', 'unique:customer_tags,name'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        CustomerTag::create([
            'name'  => $validated['name'],
            'slug'  => CustomerTag::makeSlug($validated['name']),
            'color' => $validated['color'] ?? '#6c757d',
        ]);

        return back()->with('success', 'Tag created.');
    }

    public function update(Request $request, CustomerTag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:60', 'unique:customer_tags,name,' . $tag->id],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag->update([
            'name'  => $validated['name'],
            'slug'  => CustomerTag::makeSlug($validated['name']),
            'color' => $validated['color'] ?? '#6c757d',
        ]);

        return back()->with('success', 'Tag updated.');
    }

    public function destroy(CustomerTag $tag): RedirectResponse
    {
        // Assignments cascade at the database level.
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}
