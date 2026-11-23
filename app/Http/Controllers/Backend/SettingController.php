<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Payment\NagadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Keys submitted as checkboxes — absent means "off". */
    private const BOOLEAN_KEYS = [
        'notification.order_confirmation',
        'notification.admin_new_order',
        'notification.admin_low_stock',
        'payment.sslcommerz.enabled',
        'payment.nagad.enabled',
    ];

    /** Social links that must be empty or a valid URL. */
    private const URL_KEYS = [
        'social.facebook_url',
        'social.instagram_url',
        'social.youtube_url',
        'social.tiktok_url',
        'social.messenger_url',
        'social.whatsapp_url',
        'courier.steadfast.base_url',
        'courier.pathao.base_url',
        'payment.sslcommerz.base_url',
        'payment.nagad.base_url',
    ];

    /** Keys that must be non-negative numbers. */
    private const NUMERIC_KEYS = [
        'delivery.inside_dhaka',
        'delivery.outside_dhaka',
        'delivery.express',
        'delivery.super_express',
        'delivery.free',
        'inventory.low_stock_threshold',
        'crm.repeat_min_orders',
    ];

    public function index(): View
    {
        return view('backend.modules.setting.index', [
            'settings' => Setting::map(),
            'deliveryLabels' => Order::DELIVERY_LIST,
            'booleanKeys' => self::BOOLEAN_KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Field names use array notation (`settings[delivery.inside_dhaka]`)
        // because PHP rewrites literal dots in form field names to underscores.
        $input = (array) $request->input('settings', []);

        $values = [];

        foreach (array_keys(Setting::DEFAULTS) as $key) {
            if (in_array($key, self::BOOLEAN_KEYS, true)) {
                $values[$key] = empty($input[$key]) ? '0' : '1';

                continue;
            }

            $value = isset($input[$key]) ? trim((string) $input[$key]) : '';

            if (in_array($key, self::NUMERIC_KEYS, true)) {
                if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
                    return back()->withInput()->with('error', "Please enter a valid non-negative amount for '{$key}'.");
                }
            }

            if ($key === 'notification.admin_email' && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return back()->withInput()->with('error', 'Please enter a valid admin notification email address.');
            }

            if (in_array($key, self::URL_KEYS, true) && $value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                return back()->withInput()->with('error', "Please enter a valid URL for '{$key}'.");
            }

            if ($key === 'courier.pathao.store_id' && $value !== '' && (! ctype_digit($value) || (int) $value < 1)) {
                return back()->withInput()->with('error', 'Pathao store ID must be a positive number.');
            }

            if ($key === 'courier.pathao.delivery_type' && $value !== '' && ! in_array($value, ['12', '48'], true)) {
                return back()->withInput()->with('error', 'Pathao delivery type must be 48 (Normal) or 12 (On Demand).');
            }

            if ($key === 'courier.pathao.item_type' && $value !== '' && ! in_array($value, ['1', '2'], true)) {
                return back()->withInput()->with('error', 'Pathao item type must be 1 (Document) or 2 (Parcel).');
            }

            if ($key === 'courier.pathao.item_weight' && $value !== '' && (! is_numeric($value) || (float) $value < 0.5 || (float) $value > 10)) {
                return back()->withInput()->with('error', 'Pathao item weight must be between 0.5 kg and 10 kg.');
            }

            if ($key === 'payment.nagad.server_ip' && $value !== ''
                && ! filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return back()->withInput()->with('error', 'Nagad server IP must be a valid public IPv4 address.');
            }

            if (in_array($key, ['payment.sslcommerz.mode', 'payment.nagad.mode'], true)
                && ! in_array($value, ['sandbox', 'live'], true)) {
                return back()->withInput()->with('error', 'Payment gateway mode must be either sandbox or live.');
            }

            $values[$key] = $value;
        }

        if ($values['payment.sslcommerz.enabled'] === '1'
            && (trim($values['payment.sslcommerz.store_id']) === '' || trim($values['payment.sslcommerz.store_password']) === '')) {
            return back()->withInput()->with('error', 'SSLCommerz is enabled but its Store ID or Store Password is empty.');
        }

        if ($values['payment.nagad.enabled'] === '1'
            && (trim($values['payment.nagad.merchant_id']) === ''
                || trim($values['payment.nagad.merchant_private_key']) === ''
                || trim($values['payment.nagad.pg_public_key']) === '')) {
            return back()->withInput()->with('error', 'Nagad is enabled but its Merchant ID or keys are empty.');
        }

        if ($values['payment.nagad.enabled'] === '1') {
            $nagadKeys = (new NagadService(
                $values['payment.nagad.merchant_private_key'],
                $values['payment.nagad.pg_public_key']
            ))->keyDiagnostics();

            if (! $nagadKeys['merchant_key_ok']) {
                return back()->withInput()->with('error', 'The Nagad merchant private key could not be parsed. Paste the complete private key issued by Nagad.');
            }

            if (! $nagadKeys['pg_key_ok']) {
                return back()->withInput()->with('error', 'The Nagad PG public key could not be parsed. Paste the public key issued by Nagad.');
            }

            if ($nagadKeys['keys_match']) {
                return back()->withInput()->with('error', 'The Nagad PG Public Key is your own merchant public key. Paste the public key issued by Nagad, not the public key generated from your private key.');
            }
        }

        $pathaoCredentialKeys = [
            'courier.pathao.base_url',
            'courier.pathao.client_id',
            'courier.pathao.client_secret',
            'courier.pathao.username',
            'courier.pathao.password',
        ];

        $pathaoCredentialsChanged = false;

        foreach ($pathaoCredentialKeys as $key) {
            if (($values[$key] ?? '') !== (string) Setting::effective($key, '')) {
                $pathaoCredentialsChanged = true;
                break;
            }
        }

        Setting::setMany($values);

        // A token issued with old credentials must not be reused.
        if ($pathaoCredentialsChanged) {
            Setting::set('courier.pathao.access_token', '');
            Setting::set('courier.pathao.refresh_token', '');
            Setting::set('courier.pathao.token_expires_at', '0');
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
