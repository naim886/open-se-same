<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_name_id' => \App\Models\AttributeName::factory(),
            'name' => $this->faker->randomElement([
                'Red', 'Blue', 'Green', 'Yellow', 'Black', 'White',
                'Small', 'Medium', 'Large', 'XL', 'XXL',
                'Cotton', 'Silk', 'Polyester', 'Wool', 'Linen',
                'Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour'
            ]),
            'status' => $this->faker->randomElement([1, 2]),
        ];
    }
}
