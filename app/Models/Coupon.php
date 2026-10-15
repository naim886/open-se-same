<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public const TYPE_FIXED   = 'fixed';
    public const TYPE_PERCENT = 'percent';

    public const TYPE_LIST = [
        self::TYPE_FIXED   => 'Fixed amount (৳)',
        self::TYPE_PERCENT => 'Percentage (%)',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /** Coupons that are active and inside their date window. */
    public function scopeRedeemable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /** Normalise a user-supplied code. */
    public static function normaliseCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    /**
     * Discount this coupon gives on a subtotal, capped so it can never exceed
     * the subtotal itself.
     */
    public function discountFor(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = $this->type === self::TYPE_PERCENT
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->type === self::TYPE_PERCENT && $this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Scheduled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Expired';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'Used up';
        }

        return 'Active';
    }

    public function valueLabel(): string
    {
        return $this->type === self::TYPE_PERCENT
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '%'
            : '৳' . number_format((float) $this->value, 2);
    }
}
