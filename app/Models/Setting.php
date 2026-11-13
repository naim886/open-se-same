<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple key/value store for admin-editable configuration.
 *
 * Every setting the application reads is declared in DEFAULTS so a missing
 * database row can never break a page — reads always fall back to the default
 * declared here. Values are stored as strings and cast on read.
 */
class Setting extends Model
{
    protected $guarded = [];

    /**
     * Registry of known settings with their defaults. `get()` never returns
     * null for a key listed here, even before anything has been saved.
     */
    public const DEFAULTS = [
        // Delivery charges (Taka) per delivery area.
        'delivery.inside_dhaka'  => '70',
        'delivery.outside_dhaka' => '120',
        'delivery.express'       => '150',
        'delivery.super_express' => '0',
        'delivery.free'          => '0',

        // Company identity used on invoices and delivery labels.
        'invoice.company_name'    => 'OPEN SESAME.COM LIMITED',
        'invoice.company_address' => 'Suite 2, Level 9, Rupayan Trade Center, 114 Nazrul Islam Avenue, Banglamotor, Ramna, Dhaka-1205',
        'invoice.company_phone'   => '+8801826339988, +8801826339933',
        'invoice.company_email'   => 'opensesamebd@gmail.com',
        'invoice.company_bin'     => '004261738-0208',
        'invoice.company_mushak'  => 'Mushak-6.3',
        'invoice.company_website' => 'opensesame.com.bd',
        'invoice.vat_note'        => '*** VAT INCLUDED IN THE PRODUCT PRICE ***',
        'invoice.footer_note'     => 'Thank you for shopping with us.',
        'invoice.terms'           => '',

        // Notification switches.
        'notification.order_confirmation' => '1',
        'notification.admin_new_order'    => '1',
        'notification.admin_low_stock'    => '1',
        'notification.admin_email'        => '',

        // Low stock alert threshold used by the dashboard and alerts.
        'inventory.low_stock_threshold' => '5',

        // CRM: order count at which a customer counts as "repeat".
        'crm.repeat_min_orders' => '2',

        // Storefront social links. Leave a value empty to hide that icon.
        'social.facebook_url'  => 'https://www.facebook.com/opensesame1',
        'social.instagram_url' => 'https://www.instagram.com/open_se_same/',
        'social.youtube_url'   => '',
        'social.tiktok_url'    => '',
        'social.messenger_url' => 'https://m.me/opensesame1',
        'social.whatsapp_url'  => 'https://wa.me/+8801826339988',
    ];

    /** Request-scoped memo so a page load never queries settings twice. */
    protected static ?array $memo = null;

    /**
     * All settings merged over the declared defaults.
     */
    public static function map(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        try {
            $stored = static::query()->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            // Settings table not migrated yet — fall back to defaults only.
            app_error_log('Setting Map Load', $e);
            $stored = [];
        }

        return static::$memo = array_merge(static::DEFAULTS, $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::map()[$key] ?? null;

        return $value !== null && $value !== '' ? $value : ($default ?? (static::DEFAULTS[$key] ?? null));
    }

    /**
     * Like get(), but an explicitly saved empty string stays empty instead of
     * falling back to the default. Used for optional links (social icons)
     * where saving an empty field must hide the link.
     */
    public static function effective(string $key, mixed $default = null): mixed
    {
        $map = static::map();

        return array_key_exists($key, $map) ? $map[$key] : ($default ?? (static::DEFAULTS[$key] ?? null));
    }

    public static function bool(string $key): bool
    {
        return filter_var(static::get($key), FILTER_VALIDATE_BOOLEAN);
    }

    public static function float(string $key): float
    {
        return (float) static::get($key);
    }

    public static function int(string $key): int
    {
        return (int) static::get($key);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        static::$memo = null;
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }
}
