<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    final public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Operation',
            'Sales Executive',
            'Registered Customer',
            'Guest Customer'
        ];

        foreach ($roles as $role) {
            $is_exists = Role::query()->where('name', $role)->exists();

            if ($is_exists) {
                continue;
            }
            Role::create([
                'name' => $role
            ]);
        }
    }
}
