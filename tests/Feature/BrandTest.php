<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_pages_render_for_an_admin(): void
    {
        $brand = $this->makeBrand();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('brand.index'))->assertOk();
        $this->actingAs($admin)->get(route('brand.create'))->assertOk();
        $this->actingAs($admin)->get(route('brand.show', $brand))->assertOk();
        $this->actingAs($admin)->get(route('brand.edit', $brand))->assertOk();
    }

    public function test_an_admin_can_create_a_brand(): void
    {
        $response = $this->actingAs($this->admin())->post(route('brand.store'), [
            'name'              => 'Yellow',
            'slug'              => 'yellow',
            'short_description' => 'Clothing brand',
            'status'            => (string) Brand::STATUS_ACTIVE,
            'sort_order'        => 3,
        ]);

        $response->assertRedirect(route('brand.index'));
        $this->assertDatabaseHas('brands', [
            'name'       => 'Yellow',
            'slug'       => 'yellow',
            'sort_order' => 3,
            'status'     => Brand::STATUS_ACTIVE,
        ]);
    }

    public function test_a_brand_slug_must_be_unique(): void
    {
        $this->makeBrand(['slug' => 'yellow']);

        $response = $this->actingAs($this->admin())
            ->from(route('brand.create'))
            ->post(route('brand.store'), [
                'name'              => 'Yellow Two',
                'slug'              => 'yellow',
                'short_description' => 'Another brand',
                'status'            => (string) Brand::STATUS_ACTIVE,
                'sort_order'        => 1,
            ]);

        $response->assertSessionHasErrors('slug');
        $this->assertSame(1, Brand::query()->count());
    }

    public function test_an_admin_can_update_a_brand(): void
    {
        $brand = $this->makeBrand();

        $response = $this->actingAs($this->admin())->put(route('brand.update', $brand), [
            'name'              => 'Updated Brand',
            'slug'              => 'updated-brand',
            'short_description' => 'Updated description',
            'status'            => (string) Brand::STATUS_INACTIVE,
            'sort_order'        => 9,
        ]);

        $response->assertRedirect(route('brand.index'));

        $brand->refresh();
        $this->assertSame('Updated Brand', $brand->name);
        $this->assertSame('updated-brand', $brand->slug);
        $this->assertSame(Brand::STATUS_INACTIVE, (int) $brand->status);
        $this->assertFalse($brand->isActive());
    }

    public function test_a_brand_without_products_can_be_deleted(): void
    {
        $brand = $this->makeBrand();

        $this->actingAs($this->admin())
            ->delete(route('brand.destroy', $brand))
            ->assertRedirect(route('brand.index'));

        $this->assertSoftDeleted($brand);
    }

    public function test_a_brand_with_products_cannot_be_deleted(): void
    {
        $brand = $this->makeBrand();

        Product::query()->create([
            'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
            'name'              => 'Saree',
            'slug'              => 'saree',
            'short_description' => 'Beautiful saree',
            'status'            => Product::STATUS_ACTIVE,
            'brand_id'          => $brand->id,
        ]);

        $this->actingAs($this->admin())->delete(route('brand.destroy', $brand));

        $this->assertNotSoftDeleted($brand);
    }

    private function makeBrand(array $attributes = []): Brand
    {
        return Brand::query()->create(array_merge([
            'name'              => 'Yellow',
            'slug'              => 'yellow',
            'short_description' => 'Clothing brand',
            'status'            => Brand::STATUS_ACTIVE,
            'sort_order'        => 1,
        ], $attributes));
    }

    private function admin(): User
    {
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }
}
