<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_settings_page_is_split_into_tabs(): void
    {
        $this->actingAs($this->admin())
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Delivery Charges')
            ->assertSee('Invoice &amp; Branding', false)
            ->assertSee('Courier Integrations')
            ->assertSee('Payment Gateways')
            ->assertSee('Analytics &amp; Conversion Tracking', false)
            ->assertSee('Social Media Links')
            ->assertSee('Notifications')
            ->assertSee('Inventory &amp; CRM', false)
            ->assertSee('data-bs-toggle="tab"', false);
    }

    public function test_saving_delivery_does_not_touch_payment_settings(): void
    {
        $settings = Setting::map();
        $settings['delivery.inside_dhaka'] = '99';
        $settings['payment.sslcommerz.enabled'] = '1';
        $settings['payment.sslcommerz.store_id'] = 'should-not-save';
        $settings['payment.sslcommerz.store_password'] = 'should-not-save';

        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'section' => 'delivery',
                'settings' => $settings,
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'delivery']));

        $this->assertSame('99', Setting::get('delivery.inside_dhaka'));
        $this->assertFalse(Setting::bool('payment.sslcommerz.enabled'));
        $this->assertSame('', Setting::map()['payment.sslcommerz.store_id']);
        $this->assertSame('', Setting::map()['payment.sslcommerz.store_password']);
    }

    public function test_saving_notifications_only_updates_notification_settings(): void
    {
        $settings = Setting::map();
        $settings['notification.order_confirmation'] = '1';
        $settings['notification.admin_new_order'] = '1';
        $settings['notification.admin_low_stock'] = '1';
        $settings['notification.admin_email'] = 'admin@example.com';
        $settings['delivery.inside_dhaka'] = '123';

        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'section' => 'notifications',
                'settings' => $settings,
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'notifications']));

        $this->assertTrue(Setting::bool('notification.order_confirmation'));
        $this->assertTrue(Setting::bool('notification.admin_new_order'));
        $this->assertTrue(Setting::bool('notification.admin_low_stock'));
        $this->assertSame('admin@example.com', Setting::get('notification.admin_email'));

        // Delivery was present in the submitted form but must not be changed.
        $this->assertSame('70', Setting::get('delivery.inside_dhaka'));
    }

    public function test_an_unknown_settings_section_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('settings.update'), [
                'section' => 'unknown',
                'settings' => ['delivery.inside_dhaka' => '999'],
            ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('error');

        $this->assertSame('70', Setting::get('delivery.inside_dhaka'));
    }

    protected function tearDown(): void
    {
        $property = (new \ReflectionClass(Setting::class))->getProperty('memo');
        $property->setAccessible(true);
        $property->setValue(null, null);

        parent::tearDown();
    }

    private function admin(): User
    {
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }
}
