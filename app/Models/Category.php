<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const ICON_PATH = '/uploads/category/icon/';
    public const ICON_WIDTH = 275;
    public const ICON_HEIGHT = 275;

    public const BANNER_PATH = '/uploads/category/banner/';
    public const BANNER_WIDTH = 1920;
    public const BANNER_HEIGHT = 480;


    public function getCategories()
    {
        return self::query()->orderBy('sort_order', 'asc')->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeCategory(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    private function prepareData(Request $request): array
    {
        $data = [
            'name'              => $request->input('name'),
            'slug'              => Str::slug($request->input('slug')),
            'short_description' => $request->input('short_description'),
            'status'            => $request->input('status'),
            'sort_order'        => $request->input('sort_order'),
        ];

        if ($request->hasFile('icon')) {
            $data['icon'] = (new ImageUploadManager())
                ->file($request->file('icon'))
                ->name(generateUniqueImageName($request->input('slug')))
                ->path(self::ICON_PATH)
                ->width(self::ICON_WIDTH)
                ->height(self::ICON_HEIGHT)
                ->upload();
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = (new ImageUploadManager())
                ->file($request->file('banner'))
                ->name(generateUniqueImageName($request->input('slug')))
                ->path(self::BANNER_PATH)
                ->width(self::BANNER_WIDTH)
                ->height(self::BANNER_HEIGHT)
                ->upload();
        }
        return $data;
    }

    /**
     * @return Collection
     */
    final public function getCategoryAssoc(): Collection
    {
        return self::query()->pluck('name', 'id');
    }

    final public function updateCategory(Request $request): bool
    {
        $data = $this->prepareDataForUpdate($request);
        return $this->update($data);
    }

    private function prepareDataForUpdate(Request $request): array
    {
        $data = [
            'name'              => $request->input('name'),
            'slug'              => Str::slug($request->input('slug')),
            'short_description' => $request->input('short_description'),
            'status'            => $request->input('status'),
            'sort_order'        => $request->input('sort_order'),
        ];

        // Handle icon upload and delete old icon if new one is uploaded
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($this->icon) {
                $oldIconPath = str_replace(asset('storage'), '', $this->getRawOriginal('icon'));
                \Storage::disk('public')->delete($oldIconPath);
            }

            $data['icon'] = (new ImageUploadManager())
                ->file($request->file('icon'))
                ->name(generateUniqueImageName($request->input('slug') . '-icon'))
                ->path(self::ICON_PATH)
                ->width(self::ICON_WIDTH)
                ->height(self::ICON_HEIGHT)
                ->upload();
        }

        // Handle banner upload and delete old banner if new one is uploaded
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($this->banner) {
                $oldBannerPath = str_replace(asset('storage'), '', $this->getRawOriginal('banner'));
                \Storage::disk('public')->delete($oldBannerPath);
            }

            $data['banner'] = (new ImageUploadManager())
                ->file($request->file('banner'))
                ->name(generateUniqueImageName($request->input('slug') . '-banner'))
                ->path(self::BANNER_PATH)
                ->width(self::BANNER_WIDTH)
                ->height(self::BANNER_HEIGHT)
                ->upload();
        }

        return $data;
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Relationship with products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope for ordered categories
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    // Accessor for icon URL
    public function getIconAttribute($value)
    {
        if (!$value) return null;

        // If value already contains a path, use it as is
        if (str_contains($value, '/')) {
            return asset('storage' . $value);
        }

        // Otherwise, construct the path using the constant
        return asset('storage' . self::ICON_PATH . $value);
    }

    // Accessor for banner URL
    public function getBannerAttribute($value)
    {
        if (!$value) return null;

        // If value already contains a path, use it as is
        if (str_contains($value, '/')) {
            return asset('storage' . $value);
        }

        // Otherwise, construct the path using the constant
        return asset('storage' . self::BANNER_PATH . $value);
    }

    // Get icon URL (reliable method)
    public function getIconUrlAttribute()
    {
        $value = $this->getRawOriginal('icon');
        if (!$value) return null;

        // If value already contains a path, use it as is
        if (str_contains($value, '/')) {
            return asset('storage' . $value);
        }

        // Otherwise, construct the path using the constant
        return asset('storage' . self::ICON_PATH . $value);
    }

    // Get banner URL (reliable method)
    public function getBannerUrlAttribute()
    {
        $value = $this->getRawOriginal('banner');
        if (!$value) return null;

        // If value already contains a path, use it as is
        if (str_contains($value, '/')) {
            return asset('storage' . $value);
        }

        // Otherwise, construct the path using the constant
        return asset('storage' . self::BANNER_PATH . $value);
    }
}
