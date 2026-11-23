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
    private $merchantKey = null;

    private $pgKey = null;

    public function __construct(
        private readonly ?string $merchantPrivateKeyOverride = null,
        private readonly ?string $pgPublicKeyOverride = null,
    ) {}

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
     * Nagad requires an alphanumeric merchant order id; our order numbers are
     * numeric only, so prefix them with the store initials.
     */
    public static function orderIdFor(Order $order): string
    {
        return 'OS'.preg_replace('/[^A-Za-z0-9]/', '', (string) $order->order_number);
    }

    /**
     * Initialize and complete the Nagad checkout, returning the redirect URL.
     *
     * @return array<string, mixed>
     */
    public function initiate(Order $order): array
    {
        $merchantId = trim((string) Setting::effective('payment.nagad.merchant_id', ''));
        $orderId = self::orderIdFor($order);
        $amount = number_format((float) $order->total_amount, 2, '.', '');

        // Catch the most common configuration mistakes before Nagad does; its
        // "Decryption failed" response gives no useful clue to the admin.
        $keyError = $this->keyConfigurationError();

        if ($keyError !== null) {
            Log::error('[Nagad] Key configuration invalid', [
                'message' => $keyError,
                'keys' => $this->keySummary(),
            ]);

            return [
                'status' => 'FAILED',
                'message' => $keyError,
            ];
        }

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
                'order_id' => $orderId,
                'keys' => $this->keySummary(),
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
        $key = $this->pgKeyResource();

        if (! openssl_public_encrypt($data, $encrypted, $key)) {
            throw new RuntimeException('Nagad PG public key could not encrypt the payment data.');
        }

        return base64_encode($encrypted);
    }

    private function signData(string $data): string
    {
        $key = $this->merchantKeyResource();

        if (! openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Nagad merchant private key could not sign the payment data.');
        }

        return base64_encode($signature);
    }

    private function decryptData(string $encrypted): string
    {
        $key = $this->merchantKeyResource();

        if (! openssl_private_decrypt(base64_decode($encrypted), $plain, $key)) {
            return '';
        }

        return (string) $plain;
    }

    private function merchantKeyResource()
    {
        if ($this->merchantKey !== null) {
            return $this->merchantKey;
        }

        $raw = $this->normalizeKeyText(
            $this->merchantPrivateKeyOverride
                ?? (string) Setting::effective('payment.nagad.merchant_private_key', '')
        );

        if ($raw === '' || $raw === '0') {
            throw new RuntimeException('Nagad merchant private key is missing in Settings.');
        }

        foreach ($this->keyCandidates($raw, 'RSA PRIVATE KEY', 'PRIVATE KEY') as $candidate) {
            $key = openssl_pkey_get_private($candidate);

            if ($key !== false) {
                return $this->merchantKey = $key;
            }
        }

        throw new RuntimeException('Nagad merchant private key is invalid. Paste the complete RSA private key issued for your merchant account.');
    }

    private function pgKeyResource()
    {
        if ($this->pgKey !== null) {
            return $this->pgKey;
        }

        $raw = $this->normalizeKeyText(
            $this->pgPublicKeyOverride
                ?? (string) Setting::effective('payment.nagad.pg_public_key', '')
        );

        if ($raw === '' || $raw === '0') {
            throw new RuntimeException('Nagad PG public key is missing in Settings.');
        }

        foreach ($this->keyCandidates($raw, 'PUBLIC KEY', 'RSA PUBLIC KEY') as $candidate) {
            $key = openssl_pkey_get_public($candidate);

            if ($key !== false) {
                return $this->pgKey = $key;
            }
        }

        throw new RuntimeException('Nagad PG public key is invalid. Paste the public key supplied by Nagad, not your own merchant public key.');
    }

    /**
     * Return PEM candidates for a raw/base64 key and for already-wrapped PEM.
     *
     * @return array<int, string>
     */
    private function keyCandidates(string $key, string $primaryLabel, string $alternateLabel): array
    {
        if (str_contains($key, 'BEGIN')) {
            return [$key];
        }

        $body = preg_replace('/\s+/', '', $key) ?: $key;
        $lines = chunk_split($body, 64, "\n");

        return [
            "-----BEGIN {$primaryLabel}-----\n{$lines}-----END {$primaryLabel}-----",
            "-----BEGIN {$alternateLabel}-----\n{$lines}-----END {$alternateLabel}-----",
        ];
    }

    private function normalizeKeyText(string $key): string
    {
        $key = trim($key);
        $key = str_replace(['\\r\\n', '\\n', '\\r', "\r\n", "\r"], "\n", $key);

        // Strip surrounding quotes if the key was pasted from JSON.
        if (preg_match('/^(["\'])(.*)\1$/s', $key, $matches)) {
            $key = trim($matches[2]);
        }

        return $key;
    }

    /**
     * Validate the configured keys and run local crypto self-tests.
     *
     * @return array<string, mixed>
     */
    public function keyDiagnostics(): array
    {
        $result = [
            'merchant_key_ok' => false,
            'merchant_key_bits' => null,
            'merchant_key_fingerprint' => null,
            'pg_key_ok' => false,
            'pg_key_bits' => null,
            'pg_key_fingerprint' => null,
            'keys_match' => false,
            'signature_test' => false,
            'encryption_test' => false,
            'errors' => [],
        ];

        try {
            $merchant = $this->merchantKeyResource();
            $details = openssl_pkey_get_details($merchant);
            $result['merchant_key_ok'] = is_array($details);
            $result['merchant_key_bits'] = $details['bits'] ?? null;
            $result['merchant_key_fingerprint'] = $this->fingerprint($merchant);
        } catch (\Throwable $e) {
            $result['errors'][] = $e->getMessage();
        }

        try {
            $pg = $this->pgKeyResource();
            $details = openssl_pkey_get_details($pg);
            $result['pg_key_ok'] = is_array($details);
            $result['pg_key_bits'] = $details['bits'] ?? null;
            $result['pg_key_fingerprint'] = $this->fingerprint($pg);
        } catch (\Throwable $e) {
            $result['errors'][] = $e->getMessage();
        }

        if ($result['merchant_key_ok'] && $result['pg_key_ok']) {
            $result['keys_match'] = hash_equals(
                (string) $result['merchant_key_fingerprint'],
                (string) $result['pg_key_fingerprint']
            );

            try {
                $merchant = $this->merchantKeyResource();
                $details = openssl_pkey_get_details($merchant);
                $merchantPublic = is_array($details) && isset($details['key'])
                    ? openssl_pkey_get_public($details['key'])
                    : false;

                $signed = openssl_sign('nagad-key-self-test', $signature, $merchant, OPENSSL_ALGO_SHA256);

                $result['signature_test'] = $signed
                    && $merchantPublic !== false
                    && openssl_verify('nagad-key-self-test', $signature, $merchantPublic, OPENSSL_ALGO_SHA256) === 1;
            } catch (\Throwable $e) {
                $result['errors'][] = 'Signature self-test failed: '.$e->getMessage();
            }

            try {
                $result['encryption_test'] = openssl_public_encrypt(
                    'nagad-key-self-test',
                    $encrypted,
                    $this->pgKeyResource()
                );
            } catch (\Throwable $e) {
                $result['errors'][] = 'Encryption self-test failed: '.$e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Human-readable problem with the configured keys, or null when usable.
     */
    private function keyConfigurationError(): ?string
    {
        $diagnostics = $this->keyDiagnostics();

        if (! $diagnostics['merchant_key_ok']) {
            return 'Nagad merchant private key is missing or invalid. Check Settings → Payment Gateways → Nagad.';
        }

        if (! $diagnostics['pg_key_ok']) {
            return 'Nagad PG public key is missing or invalid. Check Settings → Payment Gateways → Nagad.';
        }

        if ($diagnostics['keys_match']) {
            return 'The Nagad PG Public Key is your own merchant public key. Paste the public key issued by Nagad, not the public key generated from your merchant private key.';
        }

        if ((int) $diagnostics['merchant_key_bits'] < 2048 || (int) $diagnostics['pg_key_bits'] < 2048) {
            return 'Nagad keys must be at least 2048-bit RSA keys.';
        }

        if (! $diagnostics['signature_test']) {
            return 'The Nagad merchant private key cannot create a valid signature. Check that the complete key was pasted.';
        }

        if (! $diagnostics['encryption_test']) {
            return 'The Nagad PG public key cannot encrypt payment data. Check the key format in Settings.';
        }

        return null;
    }

    /**
     * Safe key diagnostics for logs (never includes the keys themselves).
     *
     * @return array<string, mixed>
     */
    public function keySummary(): array
    {
        $diagnostics = $this->keyDiagnostics();

        return [
            'merchant_key_ok' => $diagnostics['merchant_key_ok'],
            'merchant_key_bits' => $diagnostics['merchant_key_bits'],
            'merchant_key_sha256' => $diagnostics['merchant_key_fingerprint']
                ? substr((string) $diagnostics['merchant_key_fingerprint'], 0, 16)
                : null,
            'pg_key_ok' => $diagnostics['pg_key_ok'],
            'pg_key_bits' => $diagnostics['pg_key_bits'],
            'pg_key_sha256' => $diagnostics['pg_key_fingerprint']
                ? substr((string) $diagnostics['pg_key_fingerprint'], 0, 16)
                : null,
            'keys_match' => $diagnostics['keys_match'],
            'signature_test' => $diagnostics['signature_test'],
            'encryption_test' => $diagnostics['encryption_test'],
        ];
    }

    private function fingerprint($key): ?string
    {
        $details = openssl_pkey_get_details($key);

        return is_array($details) && isset($details['key'])
            ? hash('sha256', $details['key'])
            : null;
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
