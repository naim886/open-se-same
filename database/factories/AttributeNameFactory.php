<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeName>
 */
class AttributeNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Color', 'Size', 'Material', 'Brand', 'Style',
                'Pattern', 'Texture', 'Weight', 'Length', 'Width'
            ]),
            'status' => $this->faker->randomElement([1, 2]),
        ];
    }
}
