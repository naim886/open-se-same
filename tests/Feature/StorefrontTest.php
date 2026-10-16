<?php

namespace Tests\Feature;

use App\Models\AttributeName;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Homepage blocks
    // ─────────────────────────────────────────────────────────────

    public function test_homepage_falls_back_to_the_banner_and_category_tiles(): void
    {
        $this->makeCategory(['name' => 'Gadgets', 'slug' => 'gadgets']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Shop by', false)
            ->assertSee('Gadgets');
    }

    public function test_homepage_renders_a_configured_category_block_with_its_banner(): void
    {
        $category = $this->makeCategory([
            'name'   => 'Gadgets',
            'slug'   => 'gadgets',
            'banner' => 'uploads/category/banner/gadgets.webp',
        ]);

        $product = $this->makeProduct(['category_id' => $category->id, 'name' => 'Smart Watch']);

        HomepageSection::query()->create([
            'type'        => HomepageSection::TYPE_CATEGORY,
            'category_id' => $category->id,
            'status'      => HomepageSection::STATUS_ACTIVE,
            'sort_order'  => 0,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('uploads/category/banner/gadgets.webp')
            ->assertSee('Gadgets')
            ->assertSee($product->name);
    }

    public function test_homepage_renders_blocks_in_configured_order(): void
    {
        $category = $this->makeCategory(['name' => 'Gadgets', 'slug' => 'gadgets']);
        $this->makeProduct(['category_id' => $category->id, 'name' => 'Smart Watch']);

        HomepageSection::query()->create([
            'type'       => HomepageSection::TYPE_NEW_PRODUCTS,
            'status'     => HomepageSection::STATUS_ACTIVE,
            'sort_order' => 1,
        ]);
        HomepageSection::query()->create([
            'type'        => HomepageSection::TYPE_CATEGORY,
            'category_id' => $category->id,
            'status'      => HomepageSection::STATUS_ACTIVE,
            'sort_order'  => 0,
        ]);

        $this->get('/')->assertOk()->assertSeeInOrder(['Gadgets', 'New']);
    }

    public function test_inactive_homepage_blocks_are_not_rendered(): void
    {
        $shown  = $this->makeCategory(['name' => 'ShownCategory', 'slug' => 'shown-category']);
        $hidden = $this->makeCategory(['name' => 'HiddenCategory', 'slug' => 'hidden-category']);

        $this->makeProduct(['category_id' => $shown->id, 'name' => 'Shown Product']);
        $this->makeProduct(['category_id' => $hidden->id, 'name' => 'Hidden Product']);

        HomepageSection::query()->create([
            'type'        => HomepageSection::TYPE_CATEGORY,
            'category_id' => $shown->id,
            'status'      => HomepageSection::STATUS_ACTIVE,
            'sort_order'  => 0,
        ]);
        HomepageSection::query()->create([
            'type'        => HomepageSection::TYPE_CATEGORY,
            'category_id' => $hidden->id,
            'status'      => HomepageSection::STATUS_INACTIVE,
            'sort_order'  => 1,
        ]);

        // The category names also appear in the site navigation, so the block
        // itself is verified through the products it would have listed.
        $this->get('/')
            ->assertOk()
            ->assertSee('Shown Product')
            ->assertDontSee('Hidden Product');
    }

    // ─────────────────────────────────────────────────────────────
    // Brand page
    // ─────────────────────────────────────────────────────────────

    public function test_brand_page_shows_its_banner_and_only_its_own_products(): void
    {
        $brand = $this->makeBrand(['banner' => 'uploads/brand/banner/hero.webp']);
        $otherBrand = $this->makeBrand(['name' => 'Other Brand', 'slug' => 'other-brand']);

        $this->makeProduct(['brand_id' => $brand->id, 'name' => 'Brand Product']);
        $this->makeProduct(['brand_id' => $otherBrand->id, 'name' => 'Rival Product']);

        $this->get(route('shop.brand', $brand->slug))
            ->assertOk()
            ->assertSee('uploads/brand/banner/hero.webp')
            ->assertSee('Brand Product')
            ->assertDontSee('Rival Product');
    }

    public function test_inactive_brand_is_not_reachable(): void
    {
        $brand = $this->makeBrand(['status' => Brand::STATUS_INACTIVE]);

        $this->get(route('shop.brand', $brand->slug))->assertNotFound();
    }

    // ─────────────────────────────────────────────────────────────
    // Category page: banner, sub category accordion, variation filters
    // ─────────────────────────────────────────────────────────────

    public function test_category_page_shows_its_banner_and_collapsible_sub_categories(): void
    {
        $parent = $this->makeCategory([
            'name'   => 'Fashion',
            'slug'   => 'fashion',
            'banner' => 'uploads/category/banner/fashion.webp',
        ]);

        $this->makeCategory([
            'name'        => 'Sarees',
            'slug'        => 'sarees',
            'category_id' => $parent->id,
        ]);

        $this->get(route('shop.category', $parent->slug))
            ->assertOk()
            ->assertSee('uploads/category/banner/fashion.webp')
            ->assertSee('filter-category-toggle', false)
            ->assertSee('filter-subcategory-list', false)
            ->assertSee(route('shop.category', 'sarees'));
    }

    public function test_parent_category_page_includes_sub_category_products(): void
    {
        $parent = $this->makeCategory(['name' => 'Parent Cat', 'slug' => 'parent-cat']);
        $child  = $this->makeCategory(['name' => 'Child Cat', 'slug' => 'child-cat', 'category_id' => $parent->id]);

        $this->makeProduct(['category_id' => $child->id, 'name' => 'Child Product']);

        $this->get(route('shop.category', $parent->slug))
            ->assertOk()
            ->assertSee('Child Product');
    }

    public function test_variation_filters_narrow_the_listing(): void
    {
        $category = $this->makeCategory();
        $color    = AttributeName::query()->create(['name' => 'Color', 'status' => 1]);
        $red      = AttributeValue::query()->create(['attribute_name_id' => $color->id, 'name' => 'Red', 'status' => 1]);
        $blue     = AttributeValue::query()->create(['attribute_name_id' => $color->id, 'name' => 'Blue', 'status' => 1]);

        $this->makeVariantProduct($category, $color, $red, 'Red Saree');
        $this->makeVariantProduct($category, $color, $blue, 'Blue Saree');

        // Both products show, and the filter UI offers the attribute values.
        $this->get(route('shop.category', $category->slug))
            ->assertOk()
            ->assertSee('Red Saree')
            ->assertSee('Blue Saree')
            ->assertSee('Filter by Variation')
            ->assertSee('name="variation[' . $color->id . '][]"', false);

        // Selecting "Red" keeps only the red variation.
        $this->get(route('shop.category', $category->slug) . '?variation[' . $color->id . '][]=' . $red->id)
            ->assertOk()
            ->assertSee('Red Saree')
            ->assertDontSee('Blue Saree');
    }

    public function test_brand_page_also_supports_variation_filters(): void
    {
        $brand    = $this->makeBrand();
        $category = $this->makeCategory();
        $size     = AttributeName::query()->create(['name' => 'Size', 'status' => 1]);
        $small    = AttributeValue::query()->create(['attribute_name_id' => $size->id, 'name' => 'Small', 'status' => 1]);

        $product = $this->makeVariantProduct($category, $size, $small, 'Small Shirt');
        $product->update(['brand_id' => $brand->id]);

        $this->get(route('shop.brand', $brand->slug))
            ->assertOk()
            ->assertSee('Filter by Variation')
            ->assertSee('Small');
    }

    // ─────────────────────────────────────────────────────────────
    // Product detail
    // ─────────────────────────────────────────────────────────────

    public function test_product_detail_shows_a_linkable_brand_logo(): void
    {
        Queue::fake();

        $brand   = $this->makeBrand(['logo' => 'uploads/brand/logo/brand.webp']);
        $product = $this->makeProduct(['brand_id' => $brand->id]);

        $this->get(route('shop.product', $product->slug))
            ->assertOk()
            ->assertSee('product-brand-chip', false)
            ->assertSee('uploads/brand/logo/brand.webp')
            ->assertSee(route('shop.brand', $brand->slug));
    }

    public function test_mobile_navigation_drawer_is_present(): void
    {
        $this->makeBrand(['name' => 'Yellow Brand']);
        $this->makeCategory(['name' => 'Gadgets', 'slug' => 'gadgets']);

        $this->get('/')
            ->assertOk()
            ->assertSee('mobileNavOffcanvas', false)
            ->assertSee('mobile-nav-group-title', false)
            ->assertSee('Yellow Brand');
    }

    // ─────────────────────────────────────────────────────────────
    // Fixtures
    // ─────────────────────────────────────────────────────────────

    private function makeCategory(array $attributes = []): Category
    {
        return Category::query()->create(array_merge([
            'name'   => 'Category',
            'slug'   => 'category-' . uniqid(),
            'status' => Category::STATUS_ACTIVE,
        ], $attributes));
    }

    private function makeBrand(array $attributes = []): Brand
    {
        return Brand::query()->create(array_merge([
            'name'              => 'Yellow',
            'slug'              => 'brand-' . uniqid(),
            'short_description' => 'A brand',
            'status'            => Brand::STATUS_ACTIVE,
            'sort_order'        => 1,
        ], $attributes));
    }

    private function makeProduct(array $attributes = []): Product
    {
        $name = $attributes['name'] ?? 'Product';

        return Product::query()->create(array_merge([
            'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
            'name'              => $name,
            'slug'              => Str::slug($name) . '-' . uniqid(),
            'short_description' => 'A product',
            'price'             => 1000,
            'stock'             => 5,
            'status'            => Product::STATUS_ACTIVE,
            'sort_order'        => 1,
        ], $attributes));
    }

    private function makeVariantProduct(Category $category, AttributeName $name, AttributeValue $value, string $title): Product
    {
        $product = $this->makeProduct([
            'category_id'  => $category->id,
            'name'         => $title,
            'slug'         => Str::slug($title) . '-' . uniqid(),
            'product_type' => Product::PRODUCT_TYPE_VARIANT,
        ]);

        $variation = $product->variations()->create([
            'price'      => 100,
            'stock'      => 3,
            'status'     => 1,
            'sort_order' => 0,
        ]);

        $variation->variationAttributes()->create([
            'attribute_name_id'  => $name->id,
            'attribute_value_id' => $value->id,
        ]);

        return $product;
    }
}
