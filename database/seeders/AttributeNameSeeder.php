<?php

namespace Database\Seeders;

use App\Models\AttributeName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributeNames = [
            ['name' => 'Color', 'status' => 1],
            ['name' => 'Size', 'status' => 1],
            ['name' => 'Material', 'status' => 1],
            ['name' => 'Brand', 'status' => 1],
            ['name' => 'Style', 'status' => 1],
            ['name' => 'Pattern', 'status' => 1],
            ['name' => 'Texture', 'status' => 2],
            ['name' => 'Weight', 'status' => 1],
            ['name' => 'Length', 'status' => 1],
            ['name' => 'Width', 'status' => 2],
        ];

        foreach ($attributeNames as $attributeName) {
            // Keyed on name so re-seeding updates instead of duplicating.
            AttributeName::firstOrCreate(['name' => $attributeName['name']], $attributeName);
        }
    }
}
