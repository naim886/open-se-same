<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Brand extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const LOGO_PATH = '/uploads/brand/logo/';
    public const LOGO_WIDTH = 400;
    public const LOGO_HEIGHT = 400;

    public const BANNER_PATH = '/uploads/brand/banner/';
    public const BANNER_WIDTH = 1920;
    public const BANNER_HEIGHT = 480;

    public function getBrands()
    {
        return self::query()
            ->withCount('products')
            ->orderBy('sort_order', 'asc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeBrand(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    final public function updateBrand(Request $request): bool
    {
        return $this->update($this->prepareDataForUpdate($request));
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

        if ($request->hasFile('logo')) {
            $data['logo'] = (new ImageUploadManager())
                ->file($request->file('logo'))
                ->name(generateUniqueImageName($request->input('slug') . '-logo'))
                ->path(self::LOGO_PATH)
                ->width(self::LOGO_WIDTH)
                ->height(self::LOGO_HEIGHT)
                ->upload();
        }

        if ($request->hasFile('banner')) {
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

    private function prepareDataForUpdate(Request $request): array
    {
        $data = [
            'name'              => $request->input('name'),
            'slug'              => Str::slug($request->input('slug')),
            'short_description' => $request->input('short_description'),
            'status'            => $request->input('status'),
            'sort_order'        => $request->input('sort_order'),
        ];

        if ($request->hasFile('logo')) {
            $this->deleteImage($this->getRawOriginal('logo'), self::LOGO_PATH);
            $data['logo'] = (new ImageUploadManager())
                ->file($request->file('logo'))
                ->name(generateUniqueImageName($request->input('slug') . '-logo'))
                ->path(self::LOGO_PATH)
                ->width(self::LOGO_WIDTH)
                ->height(self::LOGO_HEIGHT)
                ->upload();
        }

        if ($request->hasFile('banner')) {
            $this->deleteImage($this->getRawOriginal('banner'), self::BANNER_PATH);
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

    /**
     * Remove a stored image. Older rows may hold either a bare file name or a
     * full "uploads/..." path, so both shapes are handled.
     */
    final public function deleteImage(?string $image, string $defaultPath): void
    {
        if (!$image) {
            return;
        }

        $path = str_contains($image, '/') ? ltrim($image, '/') : ltrim($defaultPath . $image, '/');

        ImageUploadManager::deletePhoto($path);
    }

    /**
     * @return Collection
     */
    final public function getBrandAssoc(): Collection
    {
        return self::query()->orderBy('name')->pluck('name', 'id');
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->resolveImageUrl($this->getRawOriginal('logo'), self::LOGO_PATH);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->resolveImageUrl($this->getRawOriginal('banner'), self::BANNER_PATH);
    }

    private function resolveImageUrl(?string $image, string $defaultPath): ?string
    {
        if (!$image) {
            return null;
        }

        return str_contains($image, '/')
            ? asset('storage' . $image)
            : asset('storage' . $defaultPath . $image);
    }

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name');
    }
}
