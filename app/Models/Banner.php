<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const BANNER_PATH   = 'uploads/banners/';
    public const BANNER_WIDTH  = 1920;
    public const BANNER_HEIGHT = 520;

    final public function getBanners()
    {
        return self::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeBanner(Request $request): Model
    {
        $bannerData = $this->prepareData($request);

        // Handle image upload
        if ($request->hasFile('image')) {
            $bannerData['image'] = ltrim(self::BANNER_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('image'))
                ->name(generateUniqueImageName('banner'))
                ->path(self::BANNER_PATH)
                ->width(self::BANNER_WIDTH)
                ->height(self::BANNER_HEIGHT)
                ->upload();
        }

        return self::query()->create($bannerData);
    }

    final public function updateBanner(Request $request): bool
    {
        $bannerData = $this->prepareData($request);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($this->image) {
                ImageUploadManager::deletePhoto($this->image);
            }
            $bannerData['image'] = ltrim(self::BANNER_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('image'))
                ->name(generateUniqueImageName('banner'))
                ->path(self::BANNER_PATH)
                ->width(self::BANNER_WIDTH)
                ->height(self::BANNER_HEIGHT)
                ->upload();
        }

        return $this->update($bannerData);
    }

    private function prepareData(Request $request): array
    {
        return [
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'button_text' => $request->input('button_text'),
            'button_url'  => $request->input('button_url'),
            'sort_order'  => $request->input('sort_order'),
            'status'      => $request->input('status'),
        ];
    }

    final public function getBannerAssoc(): Collection
    {
        return self::query()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('sort_order', 'asc')
            ->pluck('title', 'id');
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Scope for active banners
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope for ordered banners
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}
