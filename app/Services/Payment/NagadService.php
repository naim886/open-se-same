<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class NagadService
{
    public function enabled(): bool
    {
        return Setting::bool('payment.nagad.enabled')
            && trim((string) Setting::effective('payment.nagad.merchant_id', '')) !== ''
            && trim((string) Setting::effective('payment.nagad.merchant_private_key', '')) !== ''
            && trim((string) Setting::effective('payment.nagad.pg_public_key', '')) !== '';
    }

    public function isLive(): bool
    {
        return Setting::effective('payment.nagad.mode', 'sandbox') === 'live';
    }

    public function baseUrl(): string
    {
        $custom = trim((string) Setting::effective('payment.nagad.base_url', ''));

        if ($custom !== '') {
            return rtrim($custom, '/');
        }

        return $this->isLive()
            ? 'https://api.mynagad.com/api/dfs'
            : 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs';
    }

    /**
     * Initialize and complete the Nagad checkout, returning the redirect URL.
     *
     * @return array<string, mixed>
     */
    public function initiate(Order $order): array
    {
        $merchantId = trim((string) Setting::effective('payment.nagad.merchant_id', ''));
        $orderId = (string) $order->order_number;
        $amount = number_format((float) $order->total_amount, 2, '.', '');
        $dateTime = now()->format('YmdHis');
        $challenge = Str::random(40);

        $sensitive = [
            'merchantId' => $merchantId,
            'datetime' => $dateTime,
            'orderId' => $orderId,
            'challenge' => $challenge,
        ];

        $body = [
            'dateTime' => $dateTime,
            'sensitiveData' => $this->encryptData(json_encode($sensitive)),
            'signature' => $this->signData(json_encode($sensitive)),
        ];

        $merchantNumber = trim((string) Setting::effective('payment.nagad.merchant_number', ''));

        if ($merchantNumber !== '') {
            $body['accountNumber'] = $merchantNumber;
        }

        $initializeUrl = $this->baseUrl()
            .'/check-out/initialize/'
            .rawurlencode($merchantId).'/'
            .rawurlencode($orderId);

        $response = $this->client()->post($initializeUrl, $body);
        $result = $response->json();

        if (! is_array($result) || empty($result['sensitiveData']) || empty($result['signature'])) {
            Log::error('[Nagad] Initialize response invalid', [
                'status' => $response->status(),
                'response' => is_array($result) ? $result : $response->body(),
            ]);

            return [
                'status' => 'FAILED',
                'message' => $this->errorMessage($result, 'Nagad initialization failed.'),
            ];
        }

        $plain = json_decode($this->decryptData($result['sensitiveData']), true);

        if (! is_array($plain) || empty($plain['paymentReferenceId'])) {
            Log::error('[Nagad] Could not decrypt initialize response', ['response' => $result]);

            return [
                'status' => 'FAILED',
                'message' => 'Nagad returned an invalid encrypted response. Check the merchant keys in Settings.',
            ];
        }

        $paymentReferenceId = (string) $plain['paymentReferenceId'];
        $challenge = (string) ($plain['challenge'] ?? $challenge);

        $completeSensitive = [
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050',
            'amount' => $amount,
            'challenge' => $challenge,
        ];

        $completeBody = [
            'sensitiveData' => $this->encryptData(json_encode($completeSensitive)),
            'signature' => $this->signData(json_encode($completeSensitive)),
            'merchantCallbackURL' => route('payment.nagad.callback'),
        ];

        $completeUrl = $this->baseUrl().'/check-out/complete/'.rawurlencode($paymentReferenceId);
        $complete = $this->client()->post($completeUrl, $completeBody);
        $completeResult = $complete->json();

        if (! is_array($completeResult)) {
            Log::error('[Nagad] Complete response invalid', [
                'status' => $complete->status(),
                'body' => $complete->body(),
            ]);

            return [
                'status' => 'FAILED',
                'message' => 'Nagad returned an invalid checkout response.',
            ];
        }

        $completeResult['paymentReferenceId'] = $paymentReferenceId;
        $completeResult['_http_status'] = $complete->status();

        return $completeResult;
    }

    /**
     * Verify a Nagad payment by its payment reference ID.
     *
     * @return array<string, mixed>
     */
    public function verify(string $paymentReferenceId): array
    {
        try {
            $url = $this->baseUrl().'/verify/payment/'.rawurlencode($paymentReferenceId);

            $response = $this->client()->get($url);
            $body = $response->json();
        } catch (\Throwable $e) {
            app_error_log('Nagad Verify', $e);

            return [];
        }

        if (! is_array($body)) {
            Log::error('[Nagad] Verify response invalid', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $body;
    }

    private function client()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => $this->clientIp(),
            'X-KM-Client-Type' => 'PC_WEB',
        ])->asJson()->timeout(45);
    }

    private function clientIp(): string
    {
        $ip = request()->header('CF-Connecting-IP')
            ?? request()->header('X-Forwarded-For')
            ?? request()->ip();

        return trim(explode(',', (string) $ip)[0]) ?: '127.0.0.1';
    }

    private function encryptData(string $data): string
    {
        $key = openssl_get_publickey($this->publicKey());

        if ($key === false || ! openssl_public_encrypt($data, $encrypted, $key)) {
            throw new RuntimeException('Invalid Nagad PG public key. Check the key in Settings.');
        }

        return base64_encode($encrypted);
    }

    private function signData(string $data): string
    {
        $key = openssl_get_privatekey($this->privateKey());

        if ($key === false || ! openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Invalid Nagad merchant private key. Check the key in Settings.');
        }

        return base64_encode($signature);
    }

    private function decryptData(string $encrypted): string
    {
        $key = openssl_get_privatekey($this->privateKey());

        if ($key === false) {
            throw new RuntimeException('Invalid Nagad merchant private key. Check the key in Settings.');
        }

        if (! openssl_private_decrypt(base64_decode($encrypted), $plain, $key)) {
            return '';
        }

        return (string) $plain;
    }

    private function privateKey(): string
    {
        $key = str_replace(['\\n', '\\r', "\r"], ["\n", '', ''], trim((string) Setting::effective('payment.nagad.merchant_private_key', '')));

        if (str_contains($key, 'BEGIN')) {
            return $key;
        }

        return "-----BEGIN RSA PRIVATE KEY-----\n"
            .chunk_split(preg_replace('/\s+/', '', $key) ?? $key, 64, "\n")
            .'-----END RSA PRIVATE KEY-----';
    }

    private function publicKey(): string
    {
        $key = str_replace(['\\n', '\\r', "\r"], ["\n", '', ''], trim((string) Setting::effective('payment.nagad.pg_public_key', '')));

        if (str_contains($key, 'BEGIN')) {
            return $key;
        }

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(preg_replace('/\s+/', '', $key) ?? $key, 64, "\n")
            .'-----END PUBLIC KEY-----';
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    private function errorMessage(?array $result, string $fallback): string
    {
        if (is_array($result)) {
            return (string) ($result['message'] ?? $result['reason'] ?? $fallback);
        }

        return $fallback;
    }
}
