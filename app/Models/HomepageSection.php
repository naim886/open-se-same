<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * A single block of the composed storefront homepage. Admins add, order and
 * toggle these from the "Homepage Settings" screen; the storefront renders one
 * section per row, in `sort_order`.
 */
class HomepageSection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    public const TYPE_BANNER       = 'banner';
    public const TYPE_FLASH_SALE   = 'flash_sale';
    public const TYPE_TOP_SELLING  = 'top_selling';
    public const TYPE_NEW_PRODUCTS = 'new_products';
    public const TYPE_CATEGORY     = 'category';
    public const TYPE_TREASURES    = 'treasures';

    public const TYPE_LIST = [
        self::TYPE_BANNER       => 'Banner Slider',
        self::TYPE_FLASH_SALE   => 'Flash Sale',
        self::TYPE_TOP_SELLING  => 'Top Selling Products',
        self::TYPE_NEW_PRODUCTS => 'Newly Added Products',
        self::TYPE_CATEGORY     => 'Category Products',
        self::TYPE_TREASURES    => 'Unlock Hidden Treasures',
    ];

    /**
     * Fixed treasure icons, in the order they must appear on the homepage.
     * Only the label and link of each item are editable in the admin panel.
     */
    public const TREASURE_ICONS = [
        ['key' => 'pen',    'label' => 'Pen',    'icon' => 'images/pen.png'],
        ['key' => 'car',    'label' => 'Car',    'icon' => 'images/car.png'],
        ['key' => 'ramen',  'label' => 'Ramen',  'icon' => 'images/ramen.png'],
        ['key' => 'gum',    'label' => 'Gum',    'icon' => 'images/gum.png'],
        ['key' => 'others', 'label' => 'Others', 'icon' => 'images/others.png'],
    ];


    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    /** Block types that only make sense with a category attached. */
    public const CATEGORY_TYPES = [
        self::TYPE_CATEGORY,
    ];

    /**
     * Active blocks in display order.
     */
    public static function ordered(): Collection
    {
        return self::query()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Every block, including inactive ones — used by the settings screen.
     */
    public static function getSections(): Collection
    {
        return self::query()
            ->with('category:id,name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Replace the configured blocks with the submitted rows. Rows missing an
     * `id` are created, rows whose id is absent are deleted.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function syncSections(array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $type = $row['type'] ?? null;

            if (!array_key_exists($type, self::TYPE_LIST)) {
                continue;
            }

            $attributes = [
                'type'        => $type,
                'category_id' => in_array($type, self::CATEGORY_TYPES, true) ? (($row['category_id'] ?? null) ?: null) : null,
                'title'       => trim((string) ($row['title'] ?? '')) ?: null,
                'settings'    => $type === self::TYPE_TREASURES
                    ? ['items' => self::treasureItems($row['settings'] ?? null)]
                    : null,
                'status'      => (int) ($row['status'] ?? self::STATUS_INACTIVE),
                'sort_order'  => $index,
            ];

            $id = !empty($row['id']) ? (int) $row['id'] : null;

            if ($id) {
                $section = self::query()->find($id);

                if ($section) {
                    // Model update so the `settings` array cast is applied.
                    $section->update($attributes);
                    $keptIds[] = $id;
                }

                continue;
            }

            $keptIds[] = self::query()->create($attributes)->id;
        }

        // Rows the admin removed are dropped; an empty selection clears the page.
        self::query()->whereNotIn('id', $keptIds)->delete();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Return the five fixed treasure items with any admin overrides applied.
     *
     * @return array<int, array<string, string>>
     */
    public static function treasureItems(?array $settings = null): array
    {
        $configured = $settings['items'] ?? [];
        $items      = [];

        foreach (self::TREASURE_ICONS as $index => $default) {
            $row   = is_array($configured[$index] ?? null) ? $configured[$index] : [];
            $label = trim((string) ($row['label'] ?? ''));
            $link  = trim((string) ($row['link'] ?? ''));

            $items[] = [
                'key'   => $default['key'],
                'label' => $label !== '' ? $label : $default['label'],
                'link'  => $link,
                'icon'  => $default['icon'],
            ];
        }

        return $items;
    }

    public function requiresCategory(): bool
    {
        return in_array($this->type, self::CATEGORY_TYPES, true);
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function getTypeTextAttribute(): string
    {
        return self::TYPE_LIST[$this->type] ?? 'Unknown';
    }
}
