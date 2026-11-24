<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\AdminPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->get(['id', 'name', 'description', 'is_locked'])
            ->sortBy(static fn (Role $role) => sprintf(
                '%d-%04d-%s',
                $role->is_locked ? 0 : 1,
                AdminPermission::rolePriority($role->name),
                $role->name
            ))
            ->values();

        // Select the requested role, otherwise the first editable one.
        $activeRoleId = (int) $request->integer('role');
        if (! $roles->contains('id', $activeRoleId)) {
            $activeRoleId = (int) ($roles->firstWhere('is_locked', false)?->id ?? $roles->first()?->id ?? 0);
        }

        // One query for the whole pivot table. With 20 roles x 300 permissions
        // this is 6000 rows, which is far cheaper than a query per module.
        $assigned = DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->get(['role_id', 'permission_id']);

        $matrix = [];
        foreach ($assigned as $row) {
            $matrix[(int) $row->role_id][] = (int) $row->permission_id;
        }

        $permissions = Permission::query()
            ->where('guard_name', AdminPermission::guard())
            ->orderBy('name')
            ->get(['id', 'name']);

        // Group by module, preserving the registry order. Anything created
        // outside the registry lands in an "Other" group so it stays editable.
        $groups = [];
        foreach (array_keys(AdminPermission::modules()) as $module) {
            $groups[$module] = [
                'key' => $module,
                'label' => AdminPermission::moduleLabel($module),
                'icon' => AdminPermission::moduleIcon($module),
                'permissions' => [],
            ];
        }

        foreach ($permissions as $permission) {
            $module = AdminPermission::moduleOf($permission->name) ?? 'other';

            if (! isset($groups[$module])) {
                $groups[$module] = [
                    'key' => 'other',
                    'label' => 'Other',
                    'icon' => 'circle',
                    'permissions' => [],
                ];
            }

            $groups[$module]['permissions'][] = [
                'id' => (int) $permission->id,
                'name' => $permission->name,
                'label' => AdminPermission::label($permission->name),
            ];
        }

        $groups = array_values(array_filter($groups, static fn (array $group) => $group['permissions'] !== []));

        return view('backend.modules.permission.index', [
            'roles' => $roles,
            'groups' => $groups,
            'matrix' => $matrix,
            'activeRoleId' => $activeRoleId,
            'permissionCount' => $permissions->count(),
        ]);
    }

    /**
     * Applies permission changes for one or more roles.
     *
     * The browser only sends what actually changed, e.g.
     * { roles: [ { role_id: 3, grant: [12, 44], revoke: [7] } ] }
     * so a save costs one insert + one delete per touched role, no matter how
     * large the matrix is.
     */
    public function sync(Request $request): JsonResponse
    {
        $roleTable = config('permission.table_names.roles', 'roles');
        $permissionTable = config('permission.table_names.permissions', 'permissions');
        $pivot = config('permission.table_names.role_has_permissions', 'role_has_permissions');
        $roleKey = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $permissionKey = config('permission.column_names.permission_pivot_key') ?? 'permission_id';

        $data = $request->validate([
            'roles' => ['required', 'array', 'max:200'],
            'roles.*.role_id' => ['required', 'integer', 'exists:'.$roleTable.',id'],
            'roles.*.grant' => ['array', 'max:2000'],
            'roles.*.grant.*' => ['integer', 'exists:'.$permissionTable.',id'],
            'roles.*.revoke' => ['array', 'max:2000'],
            'roles.*.revoke.*' => ['integer', 'exists:'.$permissionTable.',id'],
        ]);

        $lockedIds = Role::query()->where('is_locked', true)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $user = $request->user();
        $ownRoleIds = $user->roles()->pluck($roleTable.'.id')->map(fn ($id) => (int) $id)->all();
        $isSuper = $user->hasRole(AdminPermission::superRole());
        $managePermId = Permission::query()->where('name', 'roles.manage')->value('id');

        $applied = 0;
        $skipped = [];

        foreach ($data['roles'] as $change) {
            $roleId = (int) $change['role_id'];

            if (in_array($roleId, $lockedIds, true)) {
                $skipped[] = $roleId;

                continue;
            }

            $grant = array_values(array_unique(array_map('intval', $change['grant'] ?? [])));
            $revoke = array_values(array_unique(array_map('intval', $change['revoke'] ?? [])));

            // A permission cannot be granted and revoked in the same payload.
            $revoke = array_values(array_diff($revoke, $grant));

            if ($grant === [] && $revoke === []) {
                continue;
            }

            // Never let a non-super admin remove their own ability to manage
            // roles (they would lock themselves out of this screen).
            if (! $isSuper
                && $managePermId
                && in_array($roleId, $ownRoleIds, true)
                && in_array((int) $managePermId, $revoke, true)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'You cannot remove the "roles.manage" permission from your own role.',
                ], 422);
            }

            DB::transaction(static function () use ($pivot, $roleKey, $permissionKey, $roleId, $grant, $revoke) {
                if ($grant !== []) {
                    $rows = array_map(
                        static fn (int $permissionId) => [$roleKey => $roleId, $permissionKey => $permissionId],
                        $grant
                    );

                    foreach (array_chunk($rows, 400) as $chunk) {
                        DB::table($pivot)->insertOrIgnore($chunk);
                    }
                }

                if ($revoke !== []) {
                    DB::table($pivot)
                        ->where($roleKey, $roleId)
                        ->whereIn($permissionKey, $revoke)
                        ->delete();
                }
            });

            $applied += count($grant) + count($revoke);
        }

        // Spatie caches the permission map; drop it so the very next request
        // (including this admin's own session) sees the new grants.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $counts = Role::query()
            ->whereIn('id', array_column($data['roles'], 'role_id'))
            ->withCount('permissions')
            ->pluck('permissions_count', 'id');

        return response()->json([
            'ok' => true,
            'applied' => $applied,
            'skipped' => $skipped,
            'counts' => $counts,
            'message' => $applied === 0
                ? 'No changes to save.'
                : $applied.' permission change'.($applied === 1 ? '' : 's').' saved.',
        ]);
    }
}
