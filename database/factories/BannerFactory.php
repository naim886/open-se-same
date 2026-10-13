<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'image' => 'uploads/banners/sample-banner.jpg',
            'button_text' => $this->faker->randomElement(['Shop Now', 'Learn More', 'Get Started', 'View Collection']),
            'button_url' => $this->faker->url(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement([Banner::STATUS_ACTIVE, Banner::STATUS_INACTIVE]),
        ];
    }

    /**
     * Indicate that the banner is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Banner::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the banner is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Banner::STATUS_INACTIVE,
        ]);
    }
}
