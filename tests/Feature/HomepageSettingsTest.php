<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomepageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_settings_page_renders_for_an_admin(): void
    {
        $this->makeCategory();

        $this->actingAs($this->admin())
            ->get(route('homepage-settings.index'))
            ->assertOk()
            ->assertSee('Homepage Settings');
    }

    public function test_an_admin_can_save_homepage_blocks(): void
    {
        $category = $this->makeCategory();

        $response = $this->actingAs($this->admin())->put(route('homepage-settings.update'), [
            'sections' => [
                ['type' => HomepageSection::TYPE_BANNER, 'status' => '1', 'title' => ''],
                [
                    'type'        => HomepageSection::TYPE_CATEGORY,
                    'status'      => '1',
                    'category_id' => $category->id,
                    'title'       => 'Featured Gadgets',
                ],
            ],
        ]);

        $response->assertRedirect(route('homepage-settings.index'));

        $this->assertDatabaseHas('homepage_sections', [
            'type'       => HomepageSection::TYPE_BANNER,
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('homepage_sections', [
            'type'        => HomepageSection::TYPE_CATEGORY,
            'category_id' => $category->id,
            'title'       => 'Featured Gadgets',
            'sort_order'  => 1,
        ]);
    }

    public function test_a_category_block_requires_a_category(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('homepage-settings.index'))
            ->put(route('homepage-settings.update'), [
                'sections' => [
                    ['type' => HomepageSection::TYPE_CATEGORY, 'status' => '1'],
                ],
            ]);

        $response->assertSessionHasErrors('sections.0.category_id');
        $this->assertSame(0, HomepageSection::query()->count());
    }

    public function test_saving_updates_existing_blocks_and_drops_removed_ones(): void
    {
        $kept = HomepageSection::query()->create([
            'type'       => HomepageSection::TYPE_BANNER,
            'status'     => HomepageSection::STATUS_ACTIVE,
            'sort_order' => 0,
        ]);

        $removed = HomepageSection::query()->create([
            'type'       => HomepageSection::TYPE_NEW_PRODUCTS,
            'status'     => HomepageSection::STATUS_ACTIVE,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin())->put(route('homepage-settings.update'), [
            'sections' => [
                ['id' => $kept->id, 'type' => HomepageSection::TYPE_TOP_SELLING, 'status' => '0', 'title' => null],
            ],
        ])->assertRedirect(route('homepage-settings.index'));

        $this->assertDatabaseHas('homepage_sections', [
            'id'     => $kept->id,
            'type'   => HomepageSection::TYPE_TOP_SELLING,
            'status' => HomepageSection::STATUS_INACTIVE,
        ]);
        $this->assertDatabaseMissing('homepage_sections', ['id' => $removed->id]);
    }

    private function makeCategory(): Category
    {
        return Category::query()->create([
            'name'   => 'Gadgets',
            'slug'   => 'gadgets',
            'status' => Category::STATUS_ACTIVE,
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
