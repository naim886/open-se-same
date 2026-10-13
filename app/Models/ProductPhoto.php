<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPhoto extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    /**
     * Accessor: derive thumbnail path from photo_path.
     * e.g. uploads/products/gallery/abc.webp → uploads/products/gallery/thumbs/abc.webp
     */
    public function getPhotoThumbAttribute(): string
    {
        return \App\Models\Product::toThumbPath($this->photo_path);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Scope for active photos
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope for ordered photos
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
