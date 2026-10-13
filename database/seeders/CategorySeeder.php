<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Walk-in Closets',
                'slug' => 'walk-in-closets',
                'short_description' => 'Luxurious walk-in closet systems with custom organization solutions.',
                'icon' => '/uploads/category/icon/walk-in-closets.jpg',
                'banner' => '/uploads/category/banner/walk-in-closets-banner.jpg',
                'sort_order' => 1,
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Reach-in Closets',
                'slug' => 'reach-in-closets',
                'short_description' => 'Space-efficient reach-in closet solutions perfect for bedrooms.',
                'icon' => '/uploads/category/icon/reach-in-closets.jpg',
                'banner' => '/uploads/category/banner/reach-in-closets-banner.jpg',
                'sort_order' => 2,
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Pantry Systems',
                'slug' => 'pantry-systems',
                'short_description' => 'Organized pantry storage solutions for kitchens and utility areas.',
                'icon' => '/uploads/category/icon/pantry-systems.jpg',
                'banner' => '/uploads/category/banner/pantry-systems-banner.jpg',
                'sort_order' => 3,
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Home Office',
                'slug' => 'home-office',
                'short_description' => 'Custom storage and organization solutions for home offices.',
                'icon' => '/uploads/category/icon/home-office.jpg',
                'banner' => '/uploads/category/banner/home-office-banner.jpg',
                'sort_order' => 4,
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Garage Organization',
                'slug' => 'garage-organization',
                'short_description' => 'Complete garage storage and organization systems.',
                'icon' => '/uploads/category/icon/garage-organization.jpg',
                'banner' => '/uploads/category/banner/garage-organization-banner.jpg',
                'sort_order' => 5,
                'status' => Category::STATUS_ACTIVE,
            ],
            [
                'name' => 'Laundry Room',
                'slug' => 'laundry-room',
                'short_description' => 'Efficient storage solutions for laundry rooms and utility areas.',
                'icon' => '/uploads/category/icon/laundry-room.jpg',
                'banner' => '/uploads/category/banner/laundry-room-banner.jpg',
                'sort_order' => 6,
                'status' => Category::STATUS_ACTIVE,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
