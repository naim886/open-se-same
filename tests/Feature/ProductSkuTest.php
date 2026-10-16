<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_form_pages_render_for_an_admin(): void
    {
        $admin = $this->admin();
        $product = $this->makeProduct();

        $this->actingAs($admin)->get(route('product.create'))->assertOk();
        $this->actingAs($admin)->get(route('product.edit', $product))->assertOk();
        $this->actingAs($admin)->get(route('product.index'))->assertOk();
    }

    public function test_a_simple_product_stores_its_sku_and_brand(): void
    {
        $brand = $this->makeBrand();
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin())->post(route('product.store'), [
            'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
            'category_id'       => $category->id,
            'brand_id'          => $brand->id,
            'sku'               => 'SKU-100',
            'name'              => 'Saree',
            'slug'              => 'saree',
            'short_description' => 'Beautiful saree',
            'price'             => 1000,
            'stock'             => 5,
            'status'            => '1',
            'sort_order'        => 1,
        ]);

        $response->assertRedirect(route('product.index'));

        $product = Product::query()->firstOrFail();
        $this->assertSame('SKU-100', $product->sku);
        $this->assertSame($brand->id, $product->brand_id);
        $this->assertTrue($product->brand->is($brand));
    }

    public function test_a_product_sku_must_be_unique(): void
    {
        $this->makeProduct(['sku' => 'SKU-100']);

        $response = $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->simpleProductPayload(['sku' => 'SKU-100']));

        $response->assertSessionHasErrors('sku');
        $this->assertSame(1, Product::query()->count());
    }

    public function test_every_variation_of_a_variant_product_stores_its_own_sku(): void
    {
        $brand = $this->makeBrand();

        $response = $this->actingAs($this->admin())->post(route('product.store'), $this->variantProductPayload([
            $this->variant(['sku' => 'SAREE-RED-L', 'attribute_value_ids' => [1]]),
            $this->variant(['sku' => 'SAREE-BLUE-L', 'attribute_value_ids' => [2]]),
        ], ['brand_id' => $brand->id]));

        $response->assertRedirect(route('product.index'));

        $product = Product::query()->firstOrFail();
        $this->assertSame($brand->id, $product->brand_id);
        $this->assertSame(
            ['SAREE-RED-L', 'SAREE-BLUE-L'],
            $product->variations()->orderBy('sort_order')->pluck('sku')->all()
        );
    }

    public function test_duplicate_variation_skus_are_rejected(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->variantProductPayload([
                $this->variant(['sku' => 'DUPLICATE', 'attribute_value_ids' => [1]]),
                $this->variant(['sku' => 'DUPLICATE', 'attribute_value_ids' => [2]]),
            ]));

        $response->assertSessionHasErrors('variants.1.sku');
        $this->assertSame(0, Product::query()->count());
    }

    public function test_a_variation_sku_cannot_reuse_another_products_sku(): void
    {
        $this->makeProduct(['sku' => 'TAKEN']);

        $response = $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->variantProductPayload([
                $this->variant(['sku' => 'TAKEN']),
            ]));

        $response->assertSessionHasErrors('variants.0.sku');
        $this->assertSame(0, ProductVariation::query()->count());
    }

    public function test_a_product_sku_cannot_reuse_another_variations_sku(): void
    {
        $product = $this->makeProduct(['product_type' => Product::PRODUCT_TYPE_VARIANT, 'sku' => null]);
        $existing = $product->variations()->create(['sku' => 'TAKEN', 'price' => 10, 'stock' => 1]);

        $response = $this->actingAs($this->admin())
            ->from(route('product.create'))
            ->post(route('product.store'), $this->simpleProductPayload(['sku' => 'TAKEN']));

        $response->assertSessionHasErrors('sku');
        $this->assertSame('TAKEN', $existing->fresh()->sku);
    }

    public function test_an_existing_variation_keeps_its_own_sku_when_the_product_is_updated(): void
    {
        $product = $this->makeProduct(['product_type' => Product::PRODUCT_TYPE_VARIANT, 'sku' => null]);
        $variation = $product->variations()->create(['sku' => 'KEEP-ME', 'price' => 10, 'stock' => 1]);

        $response = $this->actingAs($this->admin())->put(route('product.update', $product), [
            'product_type'      => Product::PRODUCT_TYPE_VARIANT,
            'category_id'       => $product->category_id,
            'name'              => $product->name,
            'slug'              => $product->slug,
            'short_description' => $product->short_description,
            'status'            => '1',
            'sort_order'        => 1,
            'variants'          => [
                [
                    'id'                  => $variation->id,
                    'attribute_name_ids'  => [1],
                    'attribute_value_ids' => [1],
                    'sku'                 => 'KEEP-ME',
                    'price'               => 25,
                    'stock'               => 4,
                ],
            ],
        ]);

        $response->assertRedirect(route('product.index'));

        $variation->refresh();
        $this->assertSame('KEEP-ME', $variation->sku);
        $this->assertEquals(25, (float) $variation->price);
    }

    private function simpleProductPayload(array $overrides = []): array
    {
        return array_merge([
            'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
            'category_id'       => $this->makeCategory()->id,
            'name'              => 'Saree',
            'slug'              => 'saree-' . uniqid(),
            'short_description' => 'Beautiful saree',
            'price'             => 1000,
            'stock'             => 5,
            'status'            => '1',
            'sort_order'        => 1,
        ], $overrides);
    }

    private function variantProductPayload(array $variants, array $overrides = []): array
    {
        return array_merge([
            'product_type'      => Product::PRODUCT_TYPE_VARIANT,
            'category_id'       => $this->makeCategory()->id,
            'name'              => 'Saree',
            'slug'              => 'saree-' . uniqid(),
            'short_description' => 'Beautiful saree',
            'status'            => '1',
            'sort_order'        => 1,
            'variants'          => $variants,
        ], $overrides);
    }

    private function variant(array $overrides = []): array
    {
        return array_merge([
            'attribute_name_ids'  => [1],
            'attribute_value_ids' => [1],
            'sku'                 => 'SKU-' . uniqid(),
            'price'               => 100,
            'stock'               => 2,
        ], $overrides);
    }

    private function makeProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
            'category_id'       => $this->makeCategory()->id,
            'name'              => 'Saree',
            'slug'              => 'saree-' . uniqid(),
            'short_description' => 'Beautiful saree',
            'price'             => 1000,
            'stock'             => 5,
            'status'            => Product::STATUS_ACTIVE,
            'sort_order'        => 1,
        ], $attributes));
    }

    private function makeCategory(): Category
    {
        return Category::query()->create([
            'name' => 'Women',
            'slug' => 'women-' . uniqid(),
        ]);
    }

    private function makeBrand(): Brand
    {
        return Brand::query()->create([
            'name'              => 'Yellow',
            'slug'              => 'yellow-' . uniqid(),
            'short_description' => 'Clothing brand',
            'status'            => Brand::STATUS_ACTIVE,
            'sort_order'        => 1,
        ]);
    }

    private function admin(): User
    {
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }
}
