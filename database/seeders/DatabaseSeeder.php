<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    final public function run(): void
    {
        $this->call([
            // Access control first — the admin account is assigned a role below.
            RoleSeeder::class,
            AdminUserSeeder::class,

            // Catalogue
            CategorySeeder::class,
            AttributeNameSeeder::class,
            AttributeValueSeeder::class,
            BannerSeeder::class,
            ProductSeeder::class,

            // Finance & marketing
            ExpenseSeeder::class,
            NewsLetterSeeder::class,
        ]);
    }
}
