<?php

namespace Database\Seeders;

use App\Support\AdminPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every permission defined in config/admin_permissions.php and applies
 * the default role grants. Safe to re-run: permissions are only created when
 * missing and role grants are only applied while a role still has none, so it
 * never overwrites permissions an administrator changed in the UI.
 */
class PermissionSeeder extends Seeder
{
    final public function run(): void
    {
        $guard = AdminPermission::guard();

        $this->createMissingPermissions($guard);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /** @var \Illuminate\Support\Collection<string, Permission> $permissions */
        $permissions = Permission::query()
            ->where('guard_name', $guard)
            ->get()
            ->keyBy('name');

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->get()
            ->keyBy('name');

        $superName = AdminPermission::superRole();

        if ($super = $roles->get($superName)) {
            // Locked: the super role is never editable from the UI and always
            // holds every permission.
            if (! $super->is_locked) {
                $super->forceFill(['is_locked' => true])->save();
            }

            if ($permissions->isNotEmpty()) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
                $super->syncPermissions($permissions->values());
            }
        }

        $assignedRoleIds = DB::table(config('permission.table_names.role_has_permissions'))
            ->distinct()
            ->pluck(config('permission.column_names.role_pivot_key') ?? 'role_id')
            ->all();

        foreach (array_keys((array) config('admin_permissions.default_roles', [])) as $roleName) {
            if ($roleName === $superName) {
                continue;
            }

            $role = $roles->get($roleName);
            if (! $role) {
                continue;
            }

            // Only seed the first time: once a role owns any permission an
            // administrator is in control of the matrix.
            if (in_array($role->getKey(), $assignedRoleIds, false)) {
                continue;
            }

            $names = AdminPermission::defaultsFor($roleName);

            if ($names === []) {
                continue;
            }

            $grant = collect($names)
                ->map(fn (string $name) => $permissions->get($name))
                ->filter()
                ->values();

            if ($grant->isNotEmpty()) {
                app(PermissionRegistrar::class)->forgetCachedPermissions();
                $role->syncPermissions($grant);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (isset($this->command)) {
            $this->command?->info(sprintf(
                'Permissions: %d defined, %d roles seeded.',
                Permission::query()->count(),
                Role::query()->count()
            ));
        }
    }

    private function createMissingPermissions(string $guard): void
    {
        $existing = Permission::query()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->all();

        $missing = array_values(array_diff(AdminPermission::names(), $existing));

        if ($missing === []) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($missing as $name) {
            $rows[] = [
                'name' => $name,
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            Permission::query()->insert($chunk);
        }
    }
}
