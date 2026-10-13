<?php

namespace Database\Seeders;

use App\Models\AttributeName;
use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have some attribute names
        $attributeNames = [
            'Color' => ['Red', 'Blue', 'Green', 'Yellow', 'Black', 'White', 'Pink', 'Purple'],
            'Size' => ['XS', 'Small', 'Medium', 'Large', 'XL', 'XXL'],
            'Material' => ['Cotton', 'Silk', 'Polyester', 'Wool', 'Linen', 'Denim'],
            'Brand' => ['Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour'],
            'Style' => ['Casual', 'Formal', 'Sport', 'Vintage', 'Modern'],
            'Pattern' => ['Solid', 'Striped', 'Dotted', 'Floral', 'Geometric']
        ];

        foreach ($attributeNames as $attributeNameStr => $values) {
            // Find or create the attribute name
            $attributeName = AttributeName::firstOrCreate([
                'name' => $attributeNameStr
            ], [
                'name' => $attributeNameStr,
                'status' => 1
            ]);

            // Create attribute values for this attribute name
            foreach ($values as $value) {
                AttributeValue::firstOrCreate([
                    'attribute_name_id' => $attributeName->id,
                    'name' => $value
                ], [
                    'attribute_name_id' => $attributeName->id,
                    'name' => $value,
                    'status' => 1
                ]);
            }
        }
    }
}
