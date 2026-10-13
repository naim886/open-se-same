<?php

namespace App\Http\Controllers;

use App\Manager\Image\ImageUploadManager;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerPanelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    /**
     * Build the order query scoped to the authenticated customer.
     * Matches by user_id OR customer_phone (handles guest → registered transitions).
     */
    private function customerOrders(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        return Order::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if ($user->phone) {
                $q->orWhere('customer_phone', $user->phone);
            }
        });
    }

    // ────────────────────────────────────────────────────────────
    // Dashboard
    // ────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $user         = auth()->user();
        $recentOrders = $this->customerOrders()
            ->with(['orderItems'])
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get();

        $totalOrders    = $this->customerOrders()->count();
        $totalSpent     = $this->customerOrders()->sum('total_amount');
        $pendingOrders  = $this->customerOrders()->whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_COURIER_ENTRY,
            Order::STATUS_PROCESSING,
        ])->count();

        return view('customer.dashboard', compact('user', 'recentOrders', 'totalOrders', 'totalSpent', 'pendingOrders'));
    }

    // ────────────────────────────────────────────────────────────
    // Order List
    // ────────────────────────────────────────────────────────────

    public function orders(Request $request): View
    {
        $orders = $this->customerOrders()
            ->with(['orderItems'])
            ->orderBy('order_date', 'desc')
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    // ────────────────────────────────────────────────────────────
    // Order Detail / Tracking
    // ────────────────────────────────────────────────────────────

    public function orderDetail(int $id): View
    {
        $order = $this->customerOrders()
            ->with(['orderItems.product', 'histories.user', 'courierLogs'])
            ->findOrFail($id);

        // Build public status timeline from history records
        $order->status_timeline = $order->histories
            ->filter(fn($h) => isset($h->changes['status']))
            ->map(fn($h) => [
                'from'  => Order::STATUS_LIST[$h->changes['status']['from'] ?? null] ?? null,
                'to'    => Order::STATUS_LIST[$h->changes['status']['to'] ?? null] ?? ($h->action === 'created' ? 'Pending' : null),
                'time'  => $h->created_at,
                'actor' => 'Team',
            ])
            ->filter(fn($row) => $row['to'] !== null)
            ->values();

        // Ensure first creation entry is present
        $creationHistory = $order->histories->firstWhere('action', 'created');
        if ($creationHistory && $order->status_timeline->isEmpty()) {
            $order->status_timeline = collect([[
                'from'  => null,
                'to'    => Order::STATUS_LIST[$order->status] ?? 'Pending',
                'time'  => $creationHistory->created_at ?? $order->order_date,
                'actor' => 'System',
            ]]);
        }

        return view('customer.order-detail', compact('order'));
    }

    // ────────────────────────────────────────────────────────────
    // Profile
    // ────────────────────────────────────────────────────────────

    public function profile(): View
    {
        return view('customer.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name'  => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = auth()->user();

        // Delete old photo if exists
        if ($user->photo) {
            ImageUploadManager::deletePhoto(User::IMAGE_PATH . $user->photo);
        }

        $photo = (new ImageUploadManager())
            ->file($request->file('photo'))
            ->name(generateUniqueImageName($user->name))
            ->path(User::IMAGE_PATH)
            ->width(User::PROFILE_PHOTO_WIDTH)
            ->height(User::PROFILE_PHOTO_HEIGHT)
            ->upload();

        $user->update(['photo' => $photo]);

        return back()->with('success', 'Profile photo updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($request->input('current_password'), auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', 'Password changed successfully.');
    }

    // ────────────────────────────────────────────────────────────
    // Address Book
    // ────────────────────────────────────────────────────────────

    public function addressBook(): View
    {
        $addresses = auth()->user()->addresses()->get();
        return view('customer.address-book', compact('addresses'));
    }

    public function addressStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label'          => ['required', 'string', 'max:30'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:500'],
            'is_default'     => ['nullable'],
        ]);

        $user      = auth()->user();
        $isDefault = !empty($data['is_default']);

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        // First address is always default
        if ($user->addresses()->count() === 0) {
            $isDefault = true;
        }

        $user->addresses()->create([
            'label'          => $data['label'],
            'recipient_name' => $data['recipient_name'],
            'phone'          => $data['phone'],
            'address'        => $data['address'],
            'is_default'     => $isDefault,
        ]);

        return back()->with('success', 'Address added successfully.');
    }

    public function addressUpdate(Request $request, int $id): RedirectResponse
    {
        $addr = auth()->user()->addresses()->findOrFail($id);

        $data = $request->validate([
            'label'          => ['required', 'string', 'max:30'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:500'],
            'is_default'     => ['nullable'],
        ]);

        $isDefault = !empty($data['is_default']);

        if ($isDefault) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $addr->update([
            'label'          => $data['label'],
            'recipient_name' => $data['recipient_name'],
            'phone'          => $data['phone'],
            'address'        => $data['address'],
            'is_default'     => $isDefault,
        ]);

        return back()->with('success', 'Address updated.');
    }

    public function addressSetDefault(int $id): RedirectResponse
    {
        $user = auth()->user();
        $user->addresses()->update(['is_default' => false]);
        $user->addresses()->findOrFail($id)->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    public function addressDestroy(int $id): RedirectResponse
    {
        $addr = auth()->user()->addresses()->findOrFail($id);
        $wasDefault = $addr->is_default;
        $addr->delete();

        // Promote next most-recently-added address to default
        if ($wasDefault) {
            auth()->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }
}
