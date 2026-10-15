<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A label that can be attached to a customer (identified by phone number).
 */
class CustomerTag extends Model
{
    protected $guarded = [];

    /** Tags seeded for every install. */
    public const DEFAULTS = [
        ['name' => 'VIP', 'slug' => 'vip', 'color' => '#d4af37'],
        ['name' => 'Frequent Buyer', 'slug' => 'frequent-buyer', 'color' => '#0d6efd'],
        ['name' => 'Wholesale', 'slug' => 'wholesale', 'color' => '#6f42c1'],
        ['name' => 'Blacklist', 'slug' => 'blacklist', 'color' => '#dc3545'],
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(CustomerTagAssignment::class);
    }

    public static function makeSlug(string $name): string
    {
        return Str::slug($name) ?: 'tag-' . Str::random(6);
    }

    /**
     * Assigned tags grouped by customer phone, for a batch of phones.
     *
     * Keeps the customer list to a single extra query instead of one per row.
     *
     * @param  array<int, string>  $phones
     * @return array<string, array<int, object>>
     */
    public static function forPhones(array $phones): array
    {
        $phones = array_values(array_filter($phones));

        if (empty($phones)) {
            return [];
        }

        return DB::table('customer_tag_assignments as a')
            ->join('customer_tags as t', 't.id', '=', 'a.customer_tag_id')
            ->whereIn('a.phone', $phones)
            ->orderBy('t.name')
            ->get(['a.phone', 't.id', 't.name', 't.color'])
            ->groupBy('phone')
            ->map(fn ($group) => $group->values()->all())
            ->all();
    }
}
