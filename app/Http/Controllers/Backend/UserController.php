<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role   = $request->input('role');
        $status = $request->input('status');
        $sort   = $request->input('sort', 'newest');

        $query = User::with('roles')
            ->withCount('orders');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->whereHas('roles', fn($q) => $q->where('name', $role));
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $query->when($sort === 'newest',      fn($q) => $q->orderByDesc('created_at'))
              ->when($sort === 'oldest',      fn($q) => $q->orderBy('created_at'))
              ->when($sort === 'most_orders', fn($q) => $q->orderByDesc('orders_count'))
              ->when($sort === 'name',        fn($q) => $q->orderBy('name'));

        $users = $query->paginate(30)->withQueryString();

        $totalUsers    = User::count();
        $activeUsers   = User::where('status', User::STATUS_ACTIVE)->count();
        $registeredToday = User::whereDate('created_at', today())->count();

        $allRoles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name');

        return view('backend.modules.user.index', compact(
            'users', 'totalUsers', 'activeUsers', 'registeredToday', 'allRoles'
        ));
    }

    public function toggleStatus(User $user)
    {
        // Prevent locking out super admins
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Cannot change Super Admin status.');
        }

        $user->update([
            'status' => $user->status == User::STATUS_ACTIVE
                ? User::STATUS_INACTIVE
                : User::STATUS_ACTIVE,
        ]);

        return back()->with('success', 'User status updated.');
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Cannot change Super Admin role.');
        }

        $request->validate(['role' => ['required', 'string', 'exists:roles,name']]);

        $user->syncRoles([$request->input('role')]);

        return back()->with('success', 'Role updated to "' . $request->input('role') . '".');
    }
}
