<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => \Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'icon' => '/uploads/category/icon/sample-icon.jpg',
            'banner' => '/uploads/category/banner/sample-banner.jpg',
            'sort_order' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE]),
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Category::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Category::STATUS_INACTIVE,
        ]);
    }
}
