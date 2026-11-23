<?php

namespace Tests\Feature;

use App\Jobs\SendFacebookCapiEvent;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Settings are memoised per process; clear it so one test cannot leak
        // enabled gateways into the next.
        $property = (new \ReflectionClass(Setting::class))->getProperty('memo');
        $property->setAccessible(true);
        $property->setValue(null, null);

        parent::tearDown();
    }

    public function test_checkout_only_lists_gateways_that_are_configured(): void
    {
        Queue::fake();

        $product = $this->makeProduct();
        $cart = $this->cartFor($product);

        $this->withSession(['cart' => $cart])
            ->get(route('shop.checkout'))
            ->assertOk()
            ->assertDontSee('value="sslcommerz"', false)
            ->assertDontSee('value="nagad"', false);

        $this->enableSslcommerz();
        $this->enableNagad();

        $this->withSession(['cart' => $cart])
            ->get(route('shop.checkout'))
            ->assertOk()
            ->assertSee('value="sslcommerz"', false)
            ->assertSee('value="nagad"', false);
    }

    public function test_admin_can_enable_gateways_and_credentials_are_required_when_enabled(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Payment Gateways');

        $settings = Setting::map();
        $settings['payment.sslcommerz.enabled'] = '1';
        $settings['payment.sslcommerz.store_id'] = 'teststore';
        $settings['payment.sslcommerz.store_password'] = 'testpass';
        $settings['payment.sslcommerz.mode'] = 'sandbox';

        $this->actingAs($admin)
            ->put(route('settings.update'), ['settings' => $settings])
            ->assertRedirect(route('settings.index'));

        $this->assertTrue(Setting::bool('payment.sslcommerz.enabled'));
        $this->assertSame('teststore', Setting::get('payment.sslcommerz.store_id'));

        // Enabling without credentials is rejected instead of silently failing
        // at checkout.
        $invalid = Setting::map();
        $invalid['payment.nagad.enabled'] = '1';
        $invalid['payment.nagad.merchant_id'] = '';
        $invalid['payment.nagad.merchant_private_key'] = '';
        $invalid['payment.nagad.pg_public_key'] = '';

        $this->actingAs($admin)
            ->put(route('settings.update'), ['settings' => $invalid])
            ->assertSessionHas('error');

        $this->assertFalse(Setting::bool('payment.nagad.enabled'));
    }

    public function test_cash_on_delivery_still_places_the_order_and_tracks_a_purchase(): void
    {
        Queue::fake();

        $product = $this->makeProduct();

        $response = $this->withSession(['cart' => $this->cartFor($product)])
            ->post(route('shop.order.place'), $this->orderPayload($product, [
                'payment_method' => 'cod',
            ]));

        $response->assertRedirect(route('shop.order.success'));

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame(Order::PAYMENT_METHOD_COD, (int) $order->payment_method);
        $this->assertSame(Order::PAYMENT_STATUS_UNPAID, (int) $order->payment_status);
        $this->assertNull($order->purchase_event_id);

        Queue::assertPushed(SendFacebookCapiEvent::class, fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase');

        // The COD success page gets a browser Pixel event id in the session.
        $this->assertNotEmpty(session('purchase_event_id'));
    }

    public function test_online_order_is_created_unpaid_and_redirected_to_sslcommerz_without_a_purchase_event(): void
    {
        Queue::fake();
        $this->enableSslcommerz();

        Http::fake([
            '*gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4/session/abc123',
            ]),
        ]);

        $product = $this->makeProduct();

        $response = $this->withSession(['cart' => $this->cartFor($product)])
            ->post(route('shop.order.place'), $this->orderPayload($product, [
                'payment_method' => 'sslcommerz',
            ]));

        $response->assertRedirect('https://sandbox.sslcommerz.com/gwprocess/v4/session/abc123');

        $order = Order::query()->latest('id')->first();

        $this->assertSame(Order::PAYMENT_METHOD_SSLCOMMERZ, (int) $order->payment_method);
        $this->assertSame(Order::PAYMENT_STATUS_UNPAID, (int) $order->payment_status);
        $this->assertSame('sslcommerz', $order->payment_gateway);
        $this->assertSame((string) $order->order_number, $order->payment_reference);
        $this->assertNull($order->purchase_event_id);

        Queue::assertPushed(SendFacebookCapiEvent::class, fn (SendFacebookCapiEvent $job) => $job->eventName === 'AddPaymentInfo');
        Queue::assertNotPushed(SendFacebookCapiEvent::class, fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase');
    }

    public function test_quick_order_can_start_an_online_payment(): void
    {
        Queue::fake();
        $this->enableSslcommerz();

        Http::fake([
            '*gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4/session/quick',
            ]),
        ]);

        $product = $this->makeProduct();

        $response = $this->post(route('shop.order.quick'), [
            'product_id' => $product->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'House 1, Road 2, Dhaka',
            'quantity' => 1,
            'delivery_location' => Order::DELIVERY_INSIDE_DHAKA,
            'payment_method' => 'sslcommerz',
        ]);

        $response->assertRedirect('https://sandbox.sslcommerz.com/gwprocess/v4/session/quick');

        $order = Order::query()->latest('id')->first();

        $this->assertSame(Order::PAYMENT_METHOD_SSLCOMMERZ, (int) $order->payment_method);
        $this->assertSame(Order::PAYMENT_STATUS_UNPAID, (int) $order->payment_status);
        $this->assertSame('sslcommerz', $order->payment_gateway);
        $this->assertSame((string) $order->order_number, $order->payment_reference);
        $this->assertNull($order->purchase_event_id);

        Queue::assertNotPushed(SendFacebookCapiEvent::class, fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase');
    }

    public function test_nagad_checkout_encrypts_the_request_and_returns_the_redirect_url(): void
    {
        Queue::fake();

        Setting::set('payment.nagad.enabled', '1');
        Setting::set('payment.nagad.mode', 'sandbox');
        Setting::set('payment.nagad.merchant_id', 'TESTMERCHANT');
        Setting::set('payment.nagad.merchant_private_key', $this->nagadPrivateKey());
        Setting::set('payment.nagad.pg_public_key', $this->nagadPublicKey());

        Http::fake([
            '*check-out/initialize/*' => Http::response([
                'sensitiveData' => $this->encryptedNagadInitResponse(),
                'signature' => 'signature',
            ]),
            '*check-out/complete/*' => Http::response([
                'callBackUrl' => 'https://sandbox.nagad.test/pay',
                'status' => 'Success',
            ]),
        ]);

        $order = $this->makeOrder(Order::PAYMENT_METHOD_NAGAD);

        $result = app(\App\Services\Payment\PaymentService::class)->start($order);

        $this->assertSame('SUCCESS', $result['status']);
        $this->assertSame('https://sandbox.nagad.test/pay', $result['url']);
        $this->assertSame('PR-123', $result['reference']);

        $order->refresh();

        $this->assertSame('nagad', $order->payment_gateway);
        $this->assertSame('PR-123', $order->payment_reference);
    }

    public function test_sslcommerz_success_callback_validates_and_marks_the_order_paid(): void
    {
        Queue::fake();
        $this->enableSslcommerz();

        $order = $this->makeOrder(Order::PAYMENT_METHOD_SSLCOMMERZ);

        Http::fake([
            '*validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => (string) $order->order_number,
                'amount' => (string) $order->total_amount,
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK-TRX-123',
            ]),
        ]);

        $response = $this->get(route('payment.sslcommerz.success', [
            'tran_id' => $order->order_number,
            'val_id' => 'VAL-123',
        ]));

        $response->assertRedirect(route('shop.order.success'));

        $order->refresh();

        $this->assertSame(Order::PAYMENT_STATUS_PAID, (int) $order->payment_status);
        $this->assertSame('sslcommerz', $order->payment_gateway);
        $this->assertSame('BANK-TRX-123', $order->payment_transaction_id);
        $this->assertSame('VAL-123', $order->payment_reference);
        $this->assertNotNull($order->paid_at);
        $this->assertNotEmpty($order->purchase_event_id);

        Queue::assertPushed(
            SendFacebookCapiEvent::class,
            fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase'
                && $job->eventId === $order->purchase_event_id
                && $job->customData['order_id'] === (string) $order->order_number
        );

        // The browser Pixel uses this id for deduplication.
        $this->assertSame($order->purchase_event_id, session('purchase_event_id'));
    }

    public function test_sslcommerz_rejects_a_callback_with_the_wrong_amount(): void
    {
        Queue::fake();
        $this->enableSslcommerz();

        $order = $this->makeOrder(Order::PAYMENT_METHOD_SSLCOMMERZ);

        Http::fake([
            '*validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => (string) $order->order_number,
                'amount' => '1.00',
                'currency' => 'BDT',
            ]),
        ]);

        $this->get(route('payment.sslcommerz.success', [
            'tran_id' => $order->order_number,
            'val_id' => 'VAL-123',
        ]))->assertRedirect();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_STATUS_UNPAID, (int) $order->payment_status);
        $this->assertNull($order->purchase_event_id);

        Queue::assertNotPushed(SendFacebookCapiEvent::class, fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase');
    }

    public function test_nagad_callback_verifies_the_payment_before_marking_it_paid(): void
    {
        Queue::fake();
        $this->enableNagad();

        $order = $this->makeOrder(Order::PAYMENT_METHOD_NAGAD);

        Http::fake([
            '*verify/payment/*' => Http::response([
                'status' => 'Success',
                'statusCode' => '000',
                'orderId' => (string) $order->order_number,
                'merchantId' => 'TESTMERCHANT',
                'paymentRefId' => 'PAY-REF-123',
                'amount' => (string) $order->total_amount,
                'issuerPaymentRefNo' => 'ISSUER-123',
            ]),
        ]);

        $response = $this->get(route('payment.nagad.callback', [
            'order_id' => $order->order_number,
            'payment_ref_id' => 'PAY-REF-123',
            'status' => 'success',
        ]));

        $response->assertRedirect(route('shop.order.success'));

        $order->refresh();

        $this->assertSame(Order::PAYMENT_STATUS_PAID, (int) $order->payment_status);
        $this->assertSame('nagad', $order->payment_gateway);
        $this->assertSame('ISSUER-123', $order->payment_transaction_id);
        $this->assertSame('PAY-REF-123', $order->payment_reference);

        Queue::assertPushed(
            SendFacebookCapiEvent::class,
            fn (SendFacebookCapiEvent $job) => $job->eventName === 'Purchase'
                && $job->eventId === $order->purchase_event_id
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Fixtures
    // ─────────────────────────────────────────────────────────────

    private function admin(): User
    {
        Role::findOrCreate('Super Admin');

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function nagadPrivateKey(): string
    {
        return <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAs7rfVub8c6Bno0tWEuF2vS0bmRb5bJgGrxVispVGv3k51be/
EUA9UW5Gwso+HeIRlepFR/4Lk4cgrO9FslZnKpkCsVV5kuuDJp/asWbkTpPMP0pf
RJZ5/3+fOcwzfgttBqKy7ChkcNzSKmYuFzhofp3WQKqPuZi/5ovwxyrwNCqZ2O4v
toeg6m+4KKjtdpOQjlCCSwe0bzWW5QoLW4mUvsVGiB/qcSTVgiLBtf9oYP+GPQGk
AfwzSezgpjn5rgQwmTftQ6OpA2COsE4YC5dboA5PnpFLKkVjUZlkR60YzcKVRzGV
lT5agcxmzNuVTP2k7Z7IKAIczgUKnkid1RDJNwIDAQABAoIBAHQP3APRW/sK/YIM
t0OwBy54v5anqGBA+Cf+BmNOVIZ3MSra4m6wbih0rwtA8MX5Y+PMifrIig81tpsW
VIqqkyqF7qA3ky+WGHZLyjy/NfWsKbxBOa4gAh5E+rynKiVeGYSsciBgoSPk5W7t
1xu1mH0TTfUqb2+pvVqSyeTDzKPSPBhoFxRfGxByC6yTkJvBZKU36Ad7uOExUX4r
oj44PJ5NAb0kEdEJuom4vFyrSVNlfWW3eCo0QxwQMyUdONA8grglySNdzzG/HDWM
rJ447pwT9WOI6Z6J2PVSXiCGUAswaJz2lrxLeRaLPNJQWnfH7NBBgewc3SHJFAWo
ZbaNvHkCgYEA74IrTsTEatK+F1G49jz3GR7+YqTJ2qC7TRvDS64zTPHceFS/37n8
u8zVnj5nH8bjo7hZgWWRoc4o+kHcF8tTEfSn07rU8cGk2BYatKjAWd33VPsQDOiv
uZaZFhh+0lrY4CwqQPgjcZLsH4kln5pzCNYhwJHbDQn7FdZ+npvd//0CgYEAwBr7
3Qo8VwtRIkEbqy5ZEneZOmuATx2OHmfmEKHAEpDgEgoV90qcJzaBpkMrlT6sWsDH
VjFe1+qG1wzE0yW5mcW9Fyyjm3PBttQGHc+gv5BW3Bs7I+wRJoU8U7UrbcCV4aqW
psWh179uh9ituOqL/D1XVMYu2F1z2F6t0YYDEkMCgYA5kubqNm6o5yP+4ww9VQZp
KYQpHkfPoFel/4iAxs1aL8G5w1WkaEcStfib45BSmTLbhlMtwcaySKadEfvgVGYR
qOg3A3Bw9QW1VArzZqVmdO8hpICFOeIUENmX8Cd8VY5QxQ0bNQsAIDtWkVdT+IKy
mxbd/bHUfpO2+irR4dIJ2QKBgBi74K3HAZnbdSjJ5TwXDZTZHTtuV+jXz1KO4gpG
KSq6yH7KGaXrT0ulMvArOdNPbHWK2J96X9HA+nd5x2XAN7NayEvRjYcYUlqt1dPE
xdekR+qy/txTKqohAXFtpBfPcfzKL/ds/rd+iEapWUSzvi5N346kradh8OZs2khd
zsttAoGBAK0I2/iHk4I/nleQUwAeJXguyQQXB3e61rhoxVxVMHTv3dG6WAS1JSHb
a5T12psxeGhK2eOd4v9WfsxiKeSHTl41D4eTxtMTromy4zEDaFto2mbkJZx769ka
5PuezHWao1TPBevQQwkYZmq/Ps1dMcuMOoACJ8VfeenNR8+09yRB
-----END RSA PRIVATE KEY-----
PEM;
    }

    private function nagadPublicKey(): string
    {
        return <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs7rfVub8c6Bno0tWEuF2
vS0bmRb5bJgGrxVispVGv3k51be/EUA9UW5Gwso+HeIRlepFR/4Lk4cgrO9FslZn
KpkCsVV5kuuDJp/asWbkTpPMP0pfRJZ5/3+fOcwzfgttBqKy7ChkcNzSKmYuFzho
fp3WQKqPuZi/5ovwxyrwNCqZ2O4vtoeg6m+4KKjtdpOQjlCCSwe0bzWW5QoLW4mU
vsVGiB/qcSTVgiLBtf9oYP+GPQGkAfwzSezgpjn5rgQwmTftQ6OpA2COsE4YC5db
oA5PnpFLKkVjUZlkR60YzcKVRzGVlT5agcxmzNuVTP2k7Z7IKAIczgUKnkid1RDJ
NwIDAQAB
-----END PUBLIC KEY-----
PEM;
    }

    private function encryptedNagadInitResponse(): string
    {
        return 'pW8Wr40euztUNnAcE/uKEDgDJsDH1st2J+iCfJVMDHaGlyLdS58LOIp7yvkC4olpFtt8KPF8TiG6lkXE+C7BM/e9tVohmQUR4C24vedkSEcf5QID0JXUY/LGkkiQtv1EF+whbYbfgJ6VohejwrJNjsd4Qha7SQEiXf+WXKXAnvin0ah2g+qAa+1XkuyxjY2eAxEGEWxxHY3HBslwvcGyvlGFBtcho5qnqkAnd2gg3XGCPCBmE+OIu6NfjEAbExlKI/7kfCevrO6afYjcKxruevRfNQ5Bp16aDSw+ibg7c0U9QZydI/EFjDOYo3Z9RBLG3EexrpIm273j/eqH8mJTNg==';
    }

    private function enableSslcommerz(): void
    {
        Setting::set('payment.sslcommerz.enabled', '1');
        Setting::set('payment.sslcommerz.mode', 'sandbox');
        Setting::set('payment.sslcommerz.store_id', 'teststore');
        Setting::set('payment.sslcommerz.store_password', 'testpass');
    }

    private function enableNagad(): void
    {
        Setting::set('payment.nagad.enabled', '1');
        Setting::set('payment.nagad.mode', 'sandbox');
        Setting::set('payment.nagad.merchant_id', 'TESTMERCHANT');
        Setting::set('payment.nagad.merchant_private_key', 'dummy');
        Setting::set('payment.nagad.pg_public_key', 'dummy');
    }

    private function makeProduct(): Product
    {
        $category = Category::query()->create([
            'name' => 'Payment Category',
            'slug' => 'payment-category-'.uniqid(),
            'status' => Category::STATUS_ACTIVE,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'product_type' => Product::PRODUCT_TYPE_SIMPLE,
            'name' => 'Payment Product',
            'slug' => 'payment-product-'.uniqid(),
            'short_description' => 'A product',
            'price' => 1000,
            'stock' => 10,
            'status' => Product::STATUS_ACTIVE,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cartFor(Product $product): array
    {
        return [
            (string) $product->id => [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => 1000,
                'sale_price' => null,
                'quantity' => 1,
                'image' => null,
                'category' => 'Payment Category',
                'product_id' => $product->id,
                'variation_id' => null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function orderPayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'House 1, Road 2, Dhaka',
            'customer_email' => 'customer@example.com',
            'delivery_location' => Order::DELIVERY_INSIDE_DHAKA,
            'note' => null,
        ], $overrides);
    }

    private function makeOrder(int $paymentMethod): Order
    {
        $product = $this->makeProduct();

        return (new Order)->storeOrder([
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'House 1, Road 2, Dhaka',
            'customer_email' => 'customer@example.com',
            'product_ids' => [$product->id],
            'product_names' => [$product->name],
            'quantities' => [1],
            'unit_prices' => [1000],
            'product_price' => 1000,
            'delivery_location' => Order::DELIVERY_INSIDE_DHAKA,
            'delivery_charge' => 70,
            'paid_amount' => 0,
            'order_total' => 1070,
            'status' => Order::STATUS_PENDING,
            'source' => 'website',
            'payment_method' => $paymentMethod,
            'analytics_context' => ['ip' => '127.0.0.1', 'user_agent' => 'PHPUnit'],
        ]);
    }
}
