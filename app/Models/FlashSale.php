<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashSale extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
                    ->withPivot('sale_price')
                    ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeRunning($query)
    {
        $now = now();
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('starts_at', '<=', $now)
                     ->where('ends_at', '>=', $now);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRunning(): bool
    {
        $now = now();
        return $this->isActive()
            && $this->starts_at <= $now
            && $this->ends_at   >= $now;
    }

    public function statusLabel(): string
    {
        if (!$this->isActive()) return 'Inactive';
        $now = now();
        if ($this->starts_at > $now) return 'Scheduled';
        if ($this->ends_at   < $now) return 'Expired';
        return 'Live';
    }

    // ── Static: get flash sale price for a product (cached per request) ──
    public static function getFlashPriceForProduct(int $productId): ?float
    {
        static $loaded = false;
        static $saleProducts = null; // keyed by product_id => sale_price

        if (!$loaded) {
            $loaded = true;
            $now    = now();
            $sale   = static::where('status', self::STATUS_ACTIVE)
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>=', $now)
                ->with('products')
                ->first();

            if ($sale) {
                $saleProducts = $sale->products->mapWithKeys(fn($p) => [
                    $p->id => $p->pivot->sale_price,
                ]);
            }
        }

        if (!$saleProducts || !$saleProducts->has($productId)) {
            return null;
        }

        $pivotPrice = $saleProducts->get($productId);
        return $pivotPrice !== null ? (float) $pivotPrice : null;
    }

    // Apply the running flash sale price onto a Product model instance (mutates sale_price).
    public static function applyFlashPrice(Product $product): void
    {
        $flashPrice = static::getFlashPriceForProduct($product->id);
        if ($flashPrice !== null) {
            $product->sale_price = $flashPrice;
        }
    }
}
