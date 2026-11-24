<?php

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * Deployments need the permission catalogue in place as soon as migrations
 * have run, otherwise every backend route would 403 until someone remembers
 * to call the seeder. This runs the idempotent seeders exactly once: if any
 * permission already exists the migration is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Permission::query()->exists()
            && DB::table(config('permission.table_names.role_has_permissions'))->exists()) {
            return;
        }

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();
    }

    public function down(): void
    {
        // Seeded data is intentionally left in place.
    }
};
