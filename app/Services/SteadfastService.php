<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SteadfastService
{
    private function baseUrl(): string
    {
        return rtrim((string) Setting::get('courier.steadfast.base_url', 'https://portal.packzy.com/api/v1'), '/');
    }

    private function apiKey(): string
    {
        return trim((string) Setting::effective('courier.steadfast.api_key', ''));
    }

    private function secretKey(): string
    {
        return trim((string) Setting::effective('courier.steadfast.secret_key', ''));
    }

    private function headers(): array
    {
        return [
            'Api-Key'      => $this->apiKey(),
            'Secret-Key'   => $this->secretKey(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create a single order via Steadfast API.
     *
     * @param array $data Keys: invoice, recipient_name, recipient_phone, recipient_address, cod_amount, note, item_description
     * @return array Decoded JSON response
     */
    public function createOrder(array $data): array
    {
        if ($this->apiKey() === '' || $this->secretKey() === '') {
            return [
                'status'  => 0,
                'message' => 'Steadfast API credentials are not configured in Settings.',
            ];
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(45)
            ->post($this->baseUrl() . '/create_order', $data);

        return $response->json() ?? [
            'status'  => 0,
            'message' => 'Empty response from Steadfast API (HTTP ' . $response->status() . ').',
        ];
    }

    /**
     * Check delivery status by consignment ID.
     */
    public function checkStatus(string $consignmentId): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(45)
            ->get($this->baseUrl() . "/status_by_cid/{$consignmentId}");

        return $response->json() ?? [];
    }

    /**
     * Check current balance.
     */
    public function getBalance(): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(45)
            ->get($this->baseUrl() . '/get_balance');

        return $response->json() ?? [];
    }
}
