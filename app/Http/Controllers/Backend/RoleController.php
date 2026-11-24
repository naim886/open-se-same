<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->get()
            ->sortBy(static fn (Role $role) => sprintf(
                '%d-%04d-%s',
                $role->is_locked ? 0 : 1,
                AdminPermission::rolePriority($role->name),
                $role->name
            ))
            ->values();

        return view('backend.modules.role.index', [
            'roles' => $roles,
            'permissionCount' => count(AdminPermission::names()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guard = AdminPermission::guard();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', $this->uniqueName($guard)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::query()->create([
            'name' => trim($data['name']),
            'guard_name' => $guard,
            'description' => $data['description'] ?? null,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('permissions.index', ['role' => $role->getKey()])
            ->with('success', 'Role "'.$role->name.'" created. Tick the permissions it should have.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_locked) {
            return back()->with('error', 'The "'.$role->name.'" role is locked and cannot be changed.');
        }

        $guard = AdminPermission::guard();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', $this->uniqueName($guard, $role->getKey())],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $oldName = $role->name;

        $role->forceFill([
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
        ])->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Role "'.$oldName.'" updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_locked) {
            return back()->with('error', 'The "'.$role->name.'" role is locked and cannot be deleted.');
        }

        $userCount = $role->users()->count();

        if ($userCount > 0) {
            return back()->with('error', sprintf(
                'Role "%s" is still assigned to %d user(s). Move them to another role first.',
                $role->name,
                $userCount
            ));
        }

        $name = $role->name;
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Role "'.$name.'" deleted.');
    }

    private function uniqueName(string $guard, ?int $ignoreId = null): \Illuminate\Validation\Rules\Unique
    {
        $rule = Rule::unique(config('permission.table_names.roles', 'roles'), 'name')
            ->where('guard_name', $guard);

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId);
        }

        return $rule;
    }
}
