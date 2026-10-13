<?php

namespace App\Http\Controllers;

use App\Models\PopupLead;
use Illuminate\Http\Request;

class PopupLeadController extends Controller
{
    /**
     * Backend: list all leads.
     */
    public function index(Request $request)
    {
        $query = PopupLead::latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $leads = $query->paginate(30)->withQueryString();

        return view('backend.modules.popup-leads.index', compact('leads'));
    }

    /**
     * Frontend AJAX: store a new lead.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        // Cloudflare passes the real visitor IP in CF-Connecting-IP.
        // Fall back to X-Forwarded-For, then the socket IP.
        $ip = $request->header('CF-Connecting-IP')
            ?? $request->header('X-Forwarded-For')
            ?? $request->ip();
        // X-Forwarded-For can be a comma-separated list; take the first entry.
        $validated['ip_address'] = trim(explode(',', $ip)[0]);
        $validated['user_agent'] = $request->userAgent();

        PopupLead::create($validated);

        return response()->json(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
    }

    /**
     * Backend: delete a lead.
     */
    public function destroy(PopupLead $popupLead)
    {
        $popupLead->delete();

        return back()->with('success', 'Lead deleted successfully.');
    }
}
