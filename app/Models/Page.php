<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const COVER_PHOTO_PATH   = '/uploads/pages/';
    public const COVER_PHOTO_WIDTH  = 1200;
    public const COVER_PHOTO_HEIGHT = 630;

    final public function getPages()
    {
        return self::query()
            ->orderBy('created_at', 'desc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storePage(Request $request): Model
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = ltrim(self::COVER_PHOTO_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('cover_photo'))
                ->name(generateUniqueImageName($request->input('slug')))
                ->path(self::COVER_PHOTO_PATH)
                ->width(self::COVER_PHOTO_WIDTH)
                ->height(self::COVER_PHOTO_HEIGHT)
                ->upload();
        }

        return self::query()->create($data);
    }

    final public function updatePage(Request $request): bool
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('cover_photo')) {
            if ($this->cover_photo) {
                ImageUploadManager::deletePhoto($this->cover_photo);
            }
            $data['cover_photo'] = ltrim(self::COVER_PHOTO_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('cover_photo'))
                ->name(generateUniqueImageName($request->input('slug')))
                ->path(self::COVER_PHOTO_PATH)
                ->width(self::COVER_PHOTO_WIDTH)
                ->height(self::COVER_PHOTO_HEIGHT)
                ->upload();
        }

        return $this->update($data);
    }

    private function prepareData(Request $request): array
    {
        $title     = $request->input('title');
        $slug      = $request->input('slug') ?: Str::slug($title);
        $metaTitle = $request->input('meta_title') ?: $title;

        return [
            'title'            => $title,
            'slug'             => $slug,
            'meta_title'       => $metaTitle,
            'meta_description' => $request->input('meta_description'),
            'description'      => $request->input('description'),
            'status'           => $request->input('status', self::STATUS_ACTIVE),
        ];
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
