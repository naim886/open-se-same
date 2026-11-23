<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoService
{
    private function baseUrl(): string
    {
        return rtrim((string) Setting::get('courier.pathao.base_url', 'https://api-hermes.pathao.com'), '/');
    }

    private function clientId(): string
    {
        return trim((string) Setting::effective('courier.pathao.client_id', ''));
    }

    private function clientSecret(): string
    {
        return trim((string) Setting::effective('courier.pathao.client_secret', ''));
    }

    private function username(): string
    {
        return trim((string) Setting::effective('courier.pathao.username', ''));
    }

    private function password(): string
    {
        return (string) Setting::effective('courier.pathao.password', '');
    }

    private function accessTokenValue(): string
    {
        return trim((string) Setting::effective('courier.pathao.access_token', ''));
    }

    private function refreshTokenValue(): string
    {
        return trim((string) Setting::effective('courier.pathao.refresh_token', ''));
    }

    private function tokenExpiresAt(): int
    {
        return (int) Setting::effective('courier.pathao.token_expires_at', 0);
    }

    /**
     * Return a valid access token, refreshing or re-issuing it when needed.
     */
    public function accessToken(bool $force = false): ?string
    {
        $current = $this->accessTokenValue();

        if (! $force && $current !== '' && $this->tokenExpiresAt() > now()->addMinutes(2)->timestamp) {
            return $current;
        }

        if (! $force && $this->refreshTokenValue() !== '') {
            $token = $this->requestToken([
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->refreshTokenValue(),
            ]);

            if ($token !== null) {
                return $token;
            }
        }

        return $this->requestToken([
            'grant_type' => 'password',
            'username'   => $this->username(),
            'password'   => $this->password(),
        ]);
    }

    /**
     * Create a new order through the Pathao Merchant API.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        $token = $this->accessToken();

        if ($token === null) {
            return [
                'code'    => 401,
                'message' => 'Pathao authentication failed. Check the Pathao credentials in Settings.',
            ];
        }

        $response = $this->request('post', '/aladdin/api/v1/orders', $payload, $token);

        if ($response->status() === 401) {
            $token = $this->accessToken(true);

            if ($token !== null) {
                $response = $this->request('post', '/aladdin/api/v1/orders', $payload, $token);
            }
        }

        return $this->decodeResponse($response);
    }

    /**
     * Fetch a short status summary for one consignment.
     *
     * @return array<string, mixed>
     */
    public function getOrderInfo(string $consignmentId): array
    {
        $token = $this->accessToken();

        if ($token === null) {
            return ['code' => 401, 'message' => 'Pathao authentication failed.'];
        }

        $response = $this->request('get', '/aladdin/api/v1/orders/' . rawurlencode($consignmentId) . '/info', [], $token);

        if ($response->status() === 401) {
            $token = $this->accessToken(true);

            if ($token !== null) {
                $response = $this->request('get', '/aladdin/api/v1/orders/' . rawurlencode($consignmentId) . '/info', [], $token);
            }
        }

        return $this->decodeResponse($response);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requestToken(array $payload): ?string
    {
        if ($this->clientId() === '' || $this->clientSecret() === '') {
            Log::channel('pathao')->warning('Pathao token request skipped: client credentials are missing.');

            return null;
        }

        if (($payload['grant_type'] ?? '') === 'password' && ($this->username() === '' || $this->password() === '')) {
            Log::channel('pathao')->warning('Pathao token request skipped: merchant username/password are missing.');

            return null;
        }

        $payload = array_merge([
            'client_id'     => $this->clientId(),
            'client_secret' => $this->clientSecret(),
        ], $payload);

        try {
            $response = Http::acceptJson()
                ->timeout(45)
                ->post($this->baseUrl() . '/aladdin/api/v1/issue-token', $payload);
        } catch (\Throwable $e) {
            Log::channel('pathao')->error('Pathao token request failed', ['message' => $e->getMessage()]);

            return null;
        }

        $body = $response->json();

        if (! is_array($body) || empty($body['access_token'])) {
            Log::channel('pathao')->warning('Pathao token response did not contain an access token', [
                'status'  => $response->status(),
                'message' => is_array($body) ? ($body['message'] ?? null) : null,
            ]);

            return null;
        }

        $expiresIn = max(60, (int) ($body['expires_in'] ?? 432000));

        Setting::set('courier.pathao.access_token', (string) $body['access_token']);
        Setting::set('courier.pathao.token_expires_at', (string) (now()->timestamp + $expiresIn - 60));

        if (! empty($body['refresh_token'])) {
            Setting::set('courier.pathao.refresh_token', (string) $body['refresh_token']);
        }

        return (string) $body['access_token'];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function request(string $method, string $path, array $payload, string $token): Response
    {
        $client = Http::acceptJson()
            ->withToken($token)
            ->timeout(45);

        return $method === 'get'
            ? $client->get($this->baseUrl() . $path)
            : $client->post($this->baseUrl() . $path, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(Response $response): array
    {
        $body = $response->json();

        if (! is_array($body)) {
            $body = [
                'code'    => $response->status(),
                'message' => 'Pathao returned an empty or non-JSON response.',
            ];
        }

        $body['_http_status'] = $response->status();

        return $body;
    }
}
