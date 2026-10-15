<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $coupons = Coupon::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($status === 'redeemable', fn ($q) => $q->redeemable())
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.modules.coupon.index', [
            'coupons'     => $coupons,
            'search'      => $search,
            'status'      => $status,
            'cms_content' => $this->cmsContent('Coupons'),
        ]);
    }

    public function create(): View
    {
        return view('backend.modules.coupon.create', [
            'cms_content' => $this->cmsContent('New Coupon'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::create($this->validated($request));

        return redirect()->route('coupon.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('backend.modules.coupon.edit', [
            'coupon'      => $coupon,
            'cms_content' => $this->cmsContent('Edit Coupon'),
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('coupon.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }

    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', 'Coupon status updated.');
    }

    /**
     * Validate and normalise coupon input.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $isPercent = $request->input('type') === Coupon::TYPE_PERCENT;

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                'unique:coupons,code' . ($coupon ? ',' . $coupon->id : ''),
            ],
            'description'        => ['nullable', 'string', 'max:255'],
            'type'               => ['required', 'in:' . implode(',', array_keys(Coupon::TYPE_LIST))],
            'value'              => ['required', 'numeric', 'min:0.01', 'max:' . ($isPercent ? 100 : 1000000)],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
            'max_discount'       => ['nullable', 'numeric', 'min:0'],
            'usage_limit'        => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:0'],
            'starts_at'          => ['nullable', 'date'],
            'ends_at'            => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $validated['code']               = Coupon::normaliseCode($validated['code']);
        $validated['is_active']          = $request->boolean('is_active');
        $validated['min_order_amount']   = $validated['min_order_amount'] ?? 0;
        $validated['per_customer_limit'] = $validated['per_customer_limit'] ?? 1;
        $validated['max_discount']       = $isPercent ? ($validated['max_discount'] ?? null) : null;

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function cmsContent(string $subModule): array
    {
        return [
            'page_title'   => 'Coupons',
            'module'       => 'Marketing',
            'sub_module'   => $subModule,
            'button_title' => 'All Coupons',
            'button_url'   => route('coupon.index'),
            'type'         => 'list',
        ];
    }
}
