<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Payment\NagadService;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\NagadTestKeys;
use Tests\TestCase;

class NagadCryptoTest extends TestCase
{
    use NagadTestKeys;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $property = (new \ReflectionClass(Setting::class))->getProperty('memo');
        $property->setAccessible(true);
        $property->setValue(null, null);

        parent::tearDown();
    }

    public function test_nagad_requests_can_be_decrypted_and_verified_by_nagad(): void
    {
        Queue::fake();
        $this->configureKeys($this->merchantTestPrivateKey(), $this->nagadTestPublicKey());

        $order = $this->makeOrder();
        $nagadPrivate = openssl_pkey_get_private($this->nagadTestPrivateKey());
        $merchantPublic = openssl_pkey_get_public($this->merchantTestPublicKey());

        $this->assertNotFalse($nagadPrivate);
        $this->assertNotFalse($merchantPublic);

        Http::fake(function (ClientRequest $request) use ($order, $nagadPrivate, $merchantPublic) {
            $payload = $request->data();

            $this->assertArrayHasKey('sensitiveData', $payload);
            $this->assertArrayHasKey('signature', $payload);
            $this->assertTrue(openssl_private_decrypt(
                base64_decode($payload['sensitiveData'], true),
                $plain,
                $nagadPrivate
            ));
            $this->assertNotSame('', $plain);

            // Nagad verifies our signature with the merchant public key.
            $this->assertSame(1, openssl_verify(
                $plain,
                base64_decode($payload['signature'], true),
                $merchantPublic,
                OPENSSL_ALGO_SHA256
            ));

            $sensitive = json_decode($plain, true);

            $this->assertSame('OS'.$order->order_number, $sensitive['orderId']);
            $this->assertSame('TESTMERCHANT', $sensitive['merchantId']);

            if (str_contains($request->url(), '/check-out/initialize/')) {
                $this->assertSame($sensitive['datetime'], $payload['dateTime']);
                $this->assertSame(40, strlen($sensitive['challenge']));

                $responsePlain = json_encode([
                    'paymentReferenceId' => 'PR-E2E',
                    'challenge' => $sensitive['challenge'],
                ]);

                $this->assertTrue(openssl_public_encrypt(
                    $responsePlain,
                    $encrypted,
                    $this->merchantTestPublicKey()
                ));

                return Http::response([
                    'sensitiveData' => base64_encode($encrypted),
                    'signature' => 'signature',
                ]);
            }

            $this->assertStringContainsString('/check-out/complete/', $request->url());
            $this->assertSame('050', $sensitive['currencyCode']);
            $this->assertSame('1070.00', number_format((float) $sensitive['amount'], 2, '.', ''));

            return Http::response([
                'callBackUrl' => 'https://sandbox.nagad.test/pay',
                'status' => 'Success',
            ]);
        });

        $result = app(PaymentService::class)->start($order);

        $this->assertSame('SUCCESS', $result['status']);
        $this->assertSame('https://sandbox.nagad.test/pay', $result['url']);
    }

    public function test_it_refuses_a_pg_key_that_is_actually_the_merchant_key(): void
    {
        Queue::fake();
        $this->configureKeys($this->merchantTestPrivateKey(), $this->merchantTestPublicKey());
        Http::fake();

        $result = app(PaymentService::class)->start($this->makeOrder());

        $this->assertSame('FAILED', $result['status']);
        $this->assertStringContainsString('merchant public key', (string) $result['message']);
        Http::assertNothingSent();
    }

    public function test_it_accepts_raw_key_bodies_and_escaped_newlines(): void
    {
        $privateBody = preg_replace('/-----[^-]+-----|\s+/', '', $this->merchantTestPrivateKey());
        $publicBody = preg_replace('/-----[^-]+-----|\s+/', '', $this->nagadTestPublicKey());

        $this->configureKeys(
            (string) $privateBody,
            chunk_split((string) $publicBody, 64, '\\n')
        );

        $diagnostics = app(NagadService::class)->keyDiagnostics();

        $this->assertTrue($diagnostics['merchant_key_ok']);
        $this->assertTrue($diagnostics['pg_key_ok']);
        $this->assertFalse($diagnostics['keys_match']);
        $this->assertTrue($diagnostics['signature_test']);
        $this->assertTrue($diagnostics['encryption_test']);
    }

    public function test_settings_reject_the_merchant_public_key_as_the_nagad_pg_key(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('Super Admin');

        $admin = \App\Models\User::factory()->create();
        $admin->assignRole('Super Admin');

        $settings = Setting::map();
        $settings['payment.nagad.enabled'] = '1';
        $settings['payment.nagad.mode'] = 'sandbox';
        $settings['payment.nagad.merchant_id'] = 'TESTMERCHANT';
        $settings['payment.nagad.merchant_private_key'] = $this->merchantTestPrivateKey();
        $settings['payment.nagad.pg_public_key'] = $this->merchantTestPublicKey();

        $this->actingAs($admin)
            ->put(route('settings.update'), ['settings' => $settings])
            ->assertSessionHas('error');

        $this->assertFalse(Setting::bool('payment.nagad.enabled'));
    }

    private function configureKeys(string $privateKey, string $publicKey): void
    {
        Setting::set('payment.nagad.enabled', '1');
        Setting::set('payment.nagad.mode', 'sandbox');
        Setting::set('payment.nagad.merchant_id', 'TESTMERCHANT');
        Setting::set('payment.nagad.merchant_private_key', $privateKey);
        Setting::set('payment.nagad.pg_public_key', $publicKey);
    }

    private function makeOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Nagad Crypto Category',
            'slug' => 'nagad-crypto-category-'.uniqid(),
            'status' => Category::STATUS_ACTIVE,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_type' => Product::PRODUCT_TYPE_SIMPLE,
            'name' => 'Nagad Crypto Product',
            'slug' => 'nagad-crypto-product-'.uniqid(),
            'short_description' => 'A product',
            'price' => 1000,
            'stock' => 10,
            'status' => Product::STATUS_ACTIVE,
        ]);

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
            'payment_method' => Order::PAYMENT_METHOD_NAGAD,
            'analytics_context' => ['ip' => '127.0.0.1', 'user_agent' => 'PHPUnit'],
        ]);
    }
}
