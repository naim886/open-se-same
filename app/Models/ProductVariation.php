<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public const VARIATION_IMAGE_PATH       = '/uploads/products/variations/';
    public const VARIATION_IMAGE_THUMB_PATH = '/uploads/products/variations/thumbs/';
    public const VARIATION_IMAGE_WIDTH      = 800;
    public const VARIATION_IMAGE_HEIGHT     = 800;

    // ── Relationships ────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variationAttributes(): HasMany
    {
        return $this->hasMany(ProductVariationAttribute::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    /**
     * Human-readable label e.g. "Color: Red / Size: Large / Material: Siffon"
     */
    public function getLabelAttribute(): string
    {
        return $this->variationAttributes
            ->map(function ($va) {
                $name  = $va->attributeName?->name  ?? '';
                $value = $va->attributeValue?->name ?? '';
                return $name && $value ? "{$name}: {$value}" : '';
            })
            ->filter()
            ->join(' / ');
    }

    /**
     * Absolute URL for the variation image (or null).
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Thumbnail path derived from full-size path.
     */
    public function getImageThumbAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return dirname($this->image) . '/thumbs/' . basename($this->image);
    }

    public function getImageThumbUrlAttribute(): ?string
    {
        return $this->image_thumb ? asset('storage/' . $this->image_thumb) : null;
    }

    /**
     * Serialize this variation for the frontend JS bundle.
     */
    public function toFrontendArray(): array
    {
        return [
            'id'         => $this->id,
            'price'      => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock'      => (int) $this->stock,
            'image_url'  => $this->image_url,
            'label'      => $this->label,
            'attributes' => $this->variationAttributes->map(fn ($va) => [
                'name_id'  => $va->attribute_name_id,
                'value_id' => $va->attribute_value_id,
            ])->values()->all(),
        ];
    }
}
