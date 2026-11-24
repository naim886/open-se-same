<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_meta_pixel_is_loaded_with_the_new_id_on_storefront_pages(): void
    {
        Queue::fake();

        $this->get('/')
            ->assertOk()
            ->assertSee('2238997253621126')
            ->assertSee("fbq('track', 'PageView')", false);
    }

    public function test_the_meta_pixel_id_is_dynamic_from_settings(): void
    {
        Queue::fake();
        Setting::set('analytics.meta.pixel_id', '999999999999999');

        $this->get('/')
            ->assertOk()
            ->assertSee('999999999999999')
            ->assertDontSee('2238997253621126');
    }

    public function test_the_quick_order_page_also_fires_page_view(): void
    {
        Queue::fake();

        $category = Category::query()->create([
            'name' => 'Meta Pixel Category',
            'slug' => 'meta-pixel-category-'.uniqid(),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_type' => Product::PRODUCT_TYPE_SIMPLE,
            'name' => 'Meta Pixel Product',
            'slug' => 'meta-pixel-product-'.Str::random(8),
            'short_description' => 'A product',
            'price' => 1000,
            'stock' => 10,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $this->get(route('shop.quick-order', $product->slug))
            ->assertOk()
            ->assertSee('2238997253621126')
            ->assertSee("fbq('track', 'PageView')", false);
    }

    protected function tearDown(): void
    {
        $property = (new \ReflectionClass(Setting::class))->getProperty('memo');
        $property->setAccessible(true);
        $property->setValue(null, null);

        parent::tearDown();
    }
}
