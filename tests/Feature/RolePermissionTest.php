<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Migrations seed the catalogue, but the seeder is idempotent so making
        // this explicit keeps the tests independent of migration order.
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    // ── catalogue ────────────────────────────────────────────────────────

    public function test_permission_catalogue_is_seeded_grouped_by_module(): void
    {
        $names = AdminPermission::names();

        $this->assertGreaterThan(50, count($names));
        $this->assertContains('orders.view', $names);
        $this->assertContains('roles.manage', $names);

        foreach ($names as $name) {
            $this->assertNotNull(AdminPermission::moduleOf($name), "Permission {$name} has no module");
            $this->assertSame(1, Permission::query()->where('name', $name)->count(), "Missing permission {$name}");
        }
    }

    public function test_every_admin_route_resolves_to_a_permission(): void
    {
        $unmapped = [];

        foreach (app('router')->getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'admin')) {
                continue;
            }

            if (AdminPermission::resolve($route) === null) {
                $unmapped[] = ($route->getName() ?? $uri).' ['.implode('|', $route->methods()).']';
            }
        }

        $this->assertSame([], $unmapped, 'Admin routes without a permission mapping: '.implode(', ', $unmapped));
    }

    public function test_explicit_route_permissions_exist_in_the_catalogue(): void
    {
        foreach (config('admin_permissions.route_permissions', []) as $route => $permission) {
            $this->assertTrue(
                AdminPermission::exists($permission),
                "Route {$route} points at unknown permission {$permission}"
            );
        }
    }

    // ── access control ───────────────────────────────────────────────────

    public function test_super_admin_bypasses_every_permission_check(): void
    {
        // Even with an empty catalogue the super role must get through.
        DB::table(config('permission.table_names.role_has_permissions'))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->get(route('roles.index'))
            ->assertOk();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_admin_can_open_role_and_permission_screens(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Roles')
            ->assertSee('Super Admin')
            ->assertSee('New Role');

        $this->actingAs($admin)
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('Role Permissions')
            ->assertSee('id="permMatrix"', false)
            ->assertSee('Orders')
            ->assertSee('roles.manage');
    }

    public function test_manager_cannot_manage_roles_but_can_use_operational_modules(): void
    {
        $manager = $this->userWithRole('Manager');

        $this->actingAs($manager)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('permissions.index'))->assertForbidden();

        // Manager has orders.view / products.view / expenses.* by default.
        $this->actingAs($manager)->get(route('orders.index'))->assertOk();
        $this->actingAs($manager)->get(route('product.index'))->assertOk();
        $this->actingAs($manager)->get(route('expense.index'))->assertOk();
    }

    public function test_operation_role_is_limited_to_its_modules(): void
    {
        $operation = $this->userWithRole('Operation');

        $this->actingAs($operation)->get(route('orders.index'))->assertOk();
        $this->actingAs($operation)->get(route('expense.index'))->assertForbidden();
        $this->actingAs($operation)->get(route('settings.index'))->assertForbidden();
    }

    public function test_user_without_backend_permissions_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Registered Customer');

        $this->actingAs($user)->get(route('dashboard.index'))->assertForbidden();
    }

    public function test_a_new_dynamic_role_immediately_controls_access(): void
    {
        $role = Role::create(['name' => 'Warehouse', 'guard_name' => 'web']);
        $role->syncPermissions(['orders.view']);

        $user = User::factory()->create();
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs(User::find($user->id))->get(route('orders.index'))->assertOk();
        $this->actingAs(User::find($user->id))->get(route('product.index'))->assertForbidden();
        $this->actingAs(User::find($user->id))->get(route('dashboard.index'))->assertForbidden();
    }

    // ── matrix sync ──────────────────────────────────────────────────────

    public function test_sync_applies_only_the_sent_diff(): void
    {
        $manager = Role::findByName('Manager');
        $before = $manager->permissions()->count();
        $grantable = Permission::findByName('roles.view');
        $revokable = Permission::findByName('products.update');

        $this->assertFalse($manager->hasPermissionTo('roles.view'));
        $this->assertTrue($manager->hasPermissionTo('products.update'));

        $response = $this->actingAs($this->userWithRole('Admin'))
            ->postJson(route('permissions.sync'), [
                'roles' => [[
                    'role_id' => $manager->id,
                    'grant' => [$grantable->id],
                    'revoke' => [$revokable->id],
                ]],
            ]);

        $response->assertOk()->assertJson(['ok' => true, 'applied' => 2]);

        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $manager->id,
            'permission_id' => $grantable->id,
        ]);
        $this->assertDatabaseMissing('role_has_permissions', [
            'role_id' => $manager->id,
            'permission_id' => $revokable->id,
        ]);
        $this->assertSame($before, $manager->permissions()->count());

        // The permission cache must be dropped so the next request sees it.
        $fresh = User::factory()->create();
        $fresh->assignRole($manager);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertTrue(User::find($fresh->id)->can('roles.view'));
    }

    public function test_sync_ignores_an_empty_diff(): void
    {
        $manager = Role::findByName('Manager');

        $this->actingAs($this->userWithRole('Admin'))
            ->postJson(route('permissions.sync'), [
                'roles' => [['role_id' => $manager->id, 'grant' => [], 'revoke' => []]],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'applied' => 0]);
    }

    public function test_locked_role_cannot_be_changed_through_the_matrix(): void
    {
        $super = Role::findByName('Super Admin');
        $permission = Permission::findByName('orders.view');
        $before = $super->permissions()->count();

        $this->actingAs($this->userWithRole('Admin'))
            ->postJson(route('permissions.sync'), [
                'roles' => [['role_id' => $super->id, 'grant' => [], 'revoke' => [$permission->id]]],
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'applied' => 0]);

        $this->assertSame($before, $super->permissions()->count());
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $super->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_admin_cannot_remove_role_management_from_their_own_role(): void
    {
        // Give the Manager role the ability to manage roles, then act as one.
        Role::findByName('Manager')->givePermissionTo('roles.manage');

        $managerUser = $this->userWithRole('Manager');
        $managerRole = Role::findByName('Manager');
        $manage = Permission::findByName('roles.manage');

        $this->actingAs($managerUser)
            ->postJson(route('permissions.sync'), [
                'roles' => [['role_id' => $managerRole->id, 'grant' => [], 'revoke' => [$manage->id]]],
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertTrue(Role::findByName('Manager')->hasPermissionTo('roles.manage'));
    }

    // ── role crud ────────────────────────────────────────────────────────

    public function test_role_can_be_created_renamed_and_deleted(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Warehouse', 'description' => 'Stock team'])
            ->assertRedirect();

        $role = Role::findByName('Warehouse');
        $this->assertSame('Stock team', $role->description);
        $this->assertFalse((bool) $role->is_locked);

        $this->actingAs($admin)
            ->put(route('roles.update', $role->id), ['name' => 'Warehouse & Stock', 'description' => null])
            ->assertRedirect();

        $this->assertSame('Warehouse & Stock', Role::find($role->id)->name);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role->id))
            ->assertRedirect();

        $this->assertNull(Role::find($role->id));
    }

    public function test_locked_role_cannot_be_renamed_or_deleted(): void
    {
        $admin = $this->userWithRole('Admin');
        $super = Role::findByName('Super Admin');

        $this->actingAs($admin)
            ->put(route('roles.update', $super->id), ['name' => 'Root'])
            ->assertSessionHas('error');

        $this->assertSame('Super Admin', Role::find($super->id)->name);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $super->id))
            ->assertSessionHas('error');

        $this->assertNotNull(Role::find($super->id));
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $admin = $this->userWithRole('Admin');
        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'web']);

        User::factory()->create()->assignRole($role);

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $role->id))
            ->assertSessionHas('error');

        $this->assertNotNull(Role::find($role->id));
    }

    public function test_duplicate_role_names_are_rejected(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('roles.store'), ['name' => 'Manager'])
            ->assertSessionHasErrors('name');
    }

    // ── users page ───────────────────────────────────────────────────────

    public function test_only_roles_that_can_change_settings_may_clear_the_cache(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->postJson(route('cache.clear'))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($this->userWithRole('Manager'))
            ->postJson(route('cache.clear'))
            ->assertForbidden();
    }

    public function test_only_super_admin_can_assign_the_super_role(): void
    {
        $admin = $this->userWithRole('Admin');
        $target = User::factory()->create();
        $target->assignRole('Operation');

        $this->actingAs($admin)
            ->patch(route('admin.users.updateRole', $target->id), ['role' => 'Super Admin'])
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->patch(route('admin.users.updateRole', $target->id), ['role' => 'Super Admin'])
            ->assertRedirect();

        $this->assertTrue(User::find($target->id)->hasRole('Super Admin'));
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
