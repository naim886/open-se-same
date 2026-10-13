<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const COVER_PHOTO_PATH   = '/uploads/blogs/';
    public const COVER_PHOTO_WIDTH  = 1200;
    public const COVER_PHOTO_HEIGHT = 630;

    final public function faqs(): HasMany
    {
        return $this->hasMany(BlogFaq::class)->orderBy('sort_order');
    }

    final public function getBlogs()
    {
        return self::query()
            ->orderBy('created_at', 'desc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeBlog(Request $request): Model
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = ltrim(self::COVER_PHOTO_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('cover_photo'))
                ->name(generateUniqueImageName($request->input('slug') ?: $request->input('title')))
                ->path(self::COVER_PHOTO_PATH)
                ->width(self::COVER_PHOTO_WIDTH)
                ->height(self::COVER_PHOTO_HEIGHT)
                ->upload();
        }

        $blog = self::query()->create($data);
        $this->syncFaqs($blog, $request);

        return $blog;
    }

    final public function updateBlog(Request $request): bool
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('cover_photo')) {
            if ($this->cover_photo) {
                ImageUploadManager::deletePhoto($this->cover_photo);
            }
            $data['cover_photo'] = ltrim(self::COVER_PHOTO_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('cover_photo'))
                ->name(generateUniqueImageName($request->input('slug') ?: $request->input('title')))
                ->path(self::COVER_PHOTO_PATH)
                ->width(self::COVER_PHOTO_WIDTH)
                ->height(self::COVER_PHOTO_HEIGHT)
                ->upload();
        }

        $result = $this->update($data);
        $this->syncFaqs($this, $request);

        return $result;
    }

    final public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    final public function getReadTimeAttribute(): int
    {
        $raw = (int) ($this->attributes['read_time'] ?? 0);
        if ($raw > 0) {
            return $raw;
        }
        // Auto-calculate: ~200 words per minute, strip HTML tags
        $wordCount = str_word_count(strip_tags((string) ($this->attributes['description'] ?? '')));
        return max(1, (int) ceil($wordCount / 200));
    }

    private function prepareData(Request $request): array
    {
        $title       = $request->input('title');
        $slug        = $request->input('slug') ?: Str::slug($title);
        $metaTitle   = $request->input('meta_title') ?: $title;
        $description = $request->input('description', '');
        $wordCount   = str_word_count(strip_tags((string) $description));
        $readTime    = (int) $request->input('read_time', max(1, (int) ceil($wordCount / 200)));

        return [
            'title'            => $title,
            'slug'             => $slug,
            'meta_title'       => $metaTitle,
            'meta_description' => $request->input('meta_description'),
            'description'      => $description,
            'author'           => $request->input('author'),
            'status'           => $request->input('status', self::STATUS_ACTIVE),
            'read_time'        => $readTime,
        ];
    }

    private function syncFaqs(Model $blog, Request $request): void
    {
        $questions = $request->input('faq_question', []);
        $answers   = $request->input('faq_answer', []);

        $blog->faqs()->delete();

        foreach ($questions as $i => $question) {
            if (!empty($question) && !empty($answers[$i])) {
                $blog->faqs()->create([
                    'question'   => $question,
                    'answer'     => $answers[$i],
                    'sort_order' => $i,
                ]);
            }
        }
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
