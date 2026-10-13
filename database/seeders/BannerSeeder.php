<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Transform Your Space with Custom Closets',
                'description' => 'Discover our premium collection of custom-designed closet solutions that maximize storage and enhance your home\'s organization.',
                'image' => 'uploads/banners/hero-closet-1.jpg',
                'button_text' => 'Explore Collection',
                'button_url' => '/products',
                'sort_order' => 1,
                'status' => Banner::STATUS_ACTIVE,
            ],
            [
                'title' => 'Luxury Walk-in Closets',
                'description' => 'Experience the ultimate in luxury with our handcrafted walk-in closet systems designed to fit your lifestyle.',
                'image' => 'uploads/banners/hero-closet-2.jpg',
                'button_text' => 'Schedule Consultation',
                'button_url' => '/contact',
                'sort_order' => 2,
                'status' => Banner::STATUS_ACTIVE,
            ],
            [
                'title' => 'Organize in Style',
                'description' => 'From reach-in closets to pantry systems, we create beautiful storage solutions for every room in your home.',
                'image' => 'uploads/banners/hero-closet-3.jpg',
                'button_text' => 'Get Quote',
                'button_url' => '/quote',
                'sort_order' => 3,
                'status' => Banner::STATUS_ACTIVE,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
