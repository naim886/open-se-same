<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
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
            'settings'       => Setting::map(),
            'deliveryLabels' => Order::DELIVERY_LIST,
            'booleanKeys'    => self::BOOLEAN_KEYS,
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

            $values[$key] = $value;
        }

        Setting::setMany($values);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
