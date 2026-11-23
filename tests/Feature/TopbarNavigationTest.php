<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TopbarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_login_and_register_in_the_storefront_topbar(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('Register');
    }

    public function test_logged_in_customers_see_their_account_and_logout(): void
    {
        $user = $this->userWithRole('Registered Customer');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Logout');
    }

    public function test_admins_see_the_admin_panel_link_and_logout_on_the_storefront(): void
    {
        $user = $this->userWithRole('Super Admin');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Admin Panel')
            ->assertSee('Logout');
    }

    public function test_the_admin_topbar_has_a_visible_logout_button(): void
    {
        $user = $this->userWithRole('Super Admin');

        $this->actingAs($user)
            ->get(route('category.index'))
            ->assertOk()
            ->assertSee('Logout');
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
