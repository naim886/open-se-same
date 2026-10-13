<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SteadfastService
{
    private string $baseUrl   = 'https://portal.packzy.com/api/v1';
    private string $apiKey    = 'ys97julfgukthikhhjovu9x92dw6bvbf';
    private string $secretKey = 'j46ac25jwy0hmm6txluoar3m';

    private function headers(): array
    {
        return [
            'Api-Key'      => $this->apiKey,
            'Secret-Key'   => $this->secretKey,
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
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/create_order', $data);

        return $response->json() ?? ['status' => 0, 'message' => 'Empty response from courier API'];
    }

    /**
     * Check delivery status by consignment ID.
     */
    public function checkStatus(string $consignmentId): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl . "/status_by_cid/{$consignmentId}");

        return $response->json() ?? [];
    }

    /**
     * Check current balance.
     */
    public function getBalance(): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl . '/get_balance');

        return $response->json() ?? [];
    }
}
