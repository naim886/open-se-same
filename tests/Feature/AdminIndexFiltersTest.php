<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every list screen must render with and without the shared filter form.
     */
    public function test_admin_list_screens_render_with_filters(): void
    {
        $admin = $this->admin();

        $checks = [
            [route('category.index'), ['search' => 'pen', 'status' => '1']],
            [route('brand.index'), ['search' => 'pen', 'status' => '1']],
            [route('banner.index'), ['search' => 'sale', 'status' => '1']],
            [route('page.index'), ['search' => 'about', 'status' => '1']],
            [route('blog.index'), ['search' => 'news', 'status' => '1']],
            [route('attribute-name.index'), ['search' => 'color', 'status' => '1']],
            [route('attribute-value.index'), ['search' => 'red', 'attribute_name_id' => '1', 'status' => '1']],
            [route('specification.index'), ['search' => 'material', 'status' => '1']],
            [route('site-content.index'), ['search' => 'home', 'type' => 'text']],
            [route('flash-sale.index'), ['search' => 'eid', 'status' => '1']],
            [route('top-selling.index'), ['search' => 'watch', 'status' => '1']],
            [route('search-keywords.index'), ['search' => 'car']],
            [route('customers.tags.index'), ['search' => 'vip']],
            [route('accounts.index'), ['search' => 'sale', 'account' => 'bkash', 'type' => 'credit']],
        ];

        foreach ($checks as [$url, $query]) {
            $this->actingAs($admin)
                ->get($url.'?'.http_build_query($query))
                ->assertOk()
                ->assertSee('Filter');
        }
    }

    public function test_category_list_can_be_filtered_by_name_and_status(): void
    {
        $admin = $this->admin();

        Category::query()->create([
            'name' => 'Pentel Pens',
            'slug' => 'pentel-pens',
            'status' => Category::STATUS_ACTIVE,
            'sort_order' => 1,
        ]);

        Category::query()->create([
            'name' => 'Hidden Category',
            'slug' => 'hidden-category',
            'status' => Category::STATUS_INACTIVE,
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('category.index', ['search' => 'Pentel']))
            ->assertOk()
            ->assertSee('Pentel Pens')
            ->assertDontSee('Hidden Category');

        $this->actingAs($admin)
            ->get(route('category.index', ['status' => (string) Category::STATUS_INACTIVE]))
            ->assertOk()
            ->assertSee('Hidden Category')
            ->assertDontSee('Pentel Pens');
    }

    private function admin(): User
    {
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }
}
