<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\Payment\NagadService;
use Illuminate\Console\Command;

class CheckNagadKeys extends Command
{
    protected $signature = 'nagad:check-keys';

    protected $description = 'Validate the Nagad merchant private key and PG public key configured in Settings';

    public function handle(NagadService $nagad): int
    {
        $diagnostics = $nagad->keyDiagnostics();

        $this->table(['Check', 'Result'], [
            ['Mode', (string) Setting::effective('payment.nagad.mode', 'sandbox')],
            ['Base URL', $nagad->baseUrl()],
            ['Merchant ID', (string) Setting::effective('payment.nagad.merchant_id', '') ?: '(missing)'],
            ['Server public IPv4', (string) Setting::effective('payment.nagad.server_ip', '') ?: '(not set)'],
            ['Merchant private key', $diagnostics['merchant_key_ok'] ? 'OK' : 'INVALID'],
            ['Merchant key size', $diagnostics['merchant_key_bits'] ? $diagnostics['merchant_key_bits'].' bit' : '—'],
            ['Merchant key SHA-256', $diagnostics['merchant_key_fingerprint'] ?: '—'],
            ['Nagad PG public key', $diagnostics['pg_key_ok'] ? 'OK' : 'INVALID'],
            ['PG key size', $diagnostics['pg_key_bits'] ? $diagnostics['pg_key_bits'].' bit' : '—'],
            ['PG key SHA-256', $diagnostics['pg_key_fingerprint'] ?: '—'],
            ['PG key equals merchant key', $diagnostics['keys_match'] ? 'YES — WRONG KEY' : 'No'],
            ['Signature self-test', $diagnostics['signature_test'] ? 'OK' : 'FAILED'],
            ['Encryption self-test', $diagnostics['encryption_test'] ? 'OK' : 'FAILED'],
        ]);

        foreach ($diagnostics['errors'] as $error) {
            $this->error($error);
        }

        if ($diagnostics['keys_match']) {
            $this->error('The Nagad PG Public Key is your own merchant public key. Paste the public key issued by Nagad instead.');
        }

        if (Setting::effective('payment.nagad.mode', 'sandbox') === 'live'
            && trim((string) Setting::effective('payment.nagad.server_ip', '')) === '') {
            $this->warn('Live mode: set the Server Public IPv4 in Settings so Nagad can match your whitelisted server.');
        }

        $ready = $diagnostics['merchant_key_ok']
            && $diagnostics['pg_key_ok']
            && ! $diagnostics['keys_match']
            && $diagnostics['signature_test']
            && $diagnostics['encryption_test'];

        if (! $ready) {
            $this->error('Nagad keys are NOT ready.');

            return self::FAILURE;
        }

        $this->info('Nagad keys are structurally valid and can encrypt/sign locally.');
        $this->warn('Only Nagad can confirm that the PG public key belongs to them. If initialize still returns "Decryption failed", request the correct PG public key for this mode (sandbox or live) from Nagad.');

        return self::SUCCESS;
    }
}
