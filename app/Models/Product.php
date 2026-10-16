<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use App\Manager\Image\ImageUploadManager;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'key_benefits'          => 'array',
        'low_stock_notified_at' => 'datetime',
    ];

    /**
     * Clear the low-stock alert flag once stock is replenished, so the admin is
     * warned again the next time it dips below the threshold.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (! $product->isDirty('stock')) {
                return;
            }

            $threshold = Setting::int('inventory.low_stock_threshold');

            if ($threshold > 0 && (int) $product->stock >= $threshold) {
                $product->low_stock_notified_at = null;
            }
        });
    }

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public const PRODUCT_TYPE_SIMPLE = 'simple';
    public const PRODUCT_TYPE_VARIANT = 'group_with_variant';

    public const PRODUCT_TYPE_LIST = [
        self::PRODUCT_TYPE_SIMPLE  => 'Simple Product',
        self::PRODUCT_TYPE_VARIANT => 'Group with Variant',
    ];

    public const PRIMARY_PHOTO_PATH   = '/uploads/products/primary/';
    public const PRIMARY_PHOTO_WIDTH  = 800;
    public const PRIMARY_PHOTO_HEIGHT = 800;

    public const OG_IMAGE_PATH   = '/uploads/products/og/';
    public const OG_IMAGE_WIDTH  = 1200;
    public const OG_IMAGE_HEIGHT = 630;

    public const GALLERY_PATH   = '/uploads/products/gallery/';
    public const GALLERY_WIDTH  = 800;
    public const GALLERY_HEIGHT = 800;

    public const THUMB_WIDTH  = 265;
    public const THUMB_HEIGHT = 265;

    public const PRIMARY_PHOTO_THUMB_PATH = '/uploads/products/primary/thumbs/';
    public const GALLERY_THUMB_PATH       = '/uploads/products/gallery/thumbs/';

    /**
     * Derive the thumbnail path from a full-size path.
     * e.g. uploads/products/primary/abc.webp → uploads/products/primary/thumbs/abc.webp
     */
    public static function toThumbPath(string $path): string
    {
        return dirname($path) . '/thumbs/' . basename($path);
    }

    /**
     * Accessor: thumbnail path for primary_photo.
     */
    public function getPrimaryPhotoThumbAttribute(): ?string
    {
        return $this->primary_photo ? self::toThumbPath($this->primary_photo) : null;
    }

    final public function getProducts()
    {
        return self::query()
            ->with(['category', 'brand', 'photos', 'activeVariations'])
            ->orderBy('created_at', 'desc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeProduct(Request $request): Model
    {
        $productData = $this->prepareData($request);

        // Handle single image uploads
        if ($request->hasFile('primary_photo')) {
            $imgName  = generateUniqueImageName($request->input('slug'));
            $filename = (new ImageUploadManager())
                ->file($request->file('primary_photo'))
                ->name($imgName)
                ->path(self::PRIMARY_PHOTO_PATH)
                ->width(self::PRIMARY_PHOTO_WIDTH)
                ->height(self::PRIMARY_PHOTO_HEIGHT)
                ->upload();
            (new ImageUploadManager())
                ->file($request->file('primary_photo'))
                ->name($imgName)
                ->path(self::PRIMARY_PHOTO_THUMB_PATH)
                ->width(self::THUMB_WIDTH)
                ->height(self::THUMB_HEIGHT)
                ->upload();
            $productData['primary_photo'] = ltrim(self::PRIMARY_PHOTO_PATH, '/') . $filename;
        }

        if ($request->hasFile('og_image')) {
            $productData['og_image'] = ltrim(self::OG_IMAGE_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('og_image'))
                ->name(generateUniqueImageName($request->input('slug') . '-og'))
                ->path(self::OG_IMAGE_PATH)
                ->width(self::OG_IMAGE_WIDTH)
                ->height(self::OG_IMAGE_HEIGHT)
                ->upload();
        }

        // Create the product first
        $product = self::query()->create($productData);

        // Handle gallery photos using ProductPhoto model
        if ($request->hasFile('gallery')) {
            $galleryFiles = $request->file('gallery');

            // Handle both single file and multiple files
            if (!is_array($galleryFiles)) {
                $galleryFiles = [$galleryFiles];
            }

            Log::info('Gallery files detected: ' . count($galleryFiles));
            $sortOrder = 1;
            foreach ($galleryFiles as $file) {
                if ($file && $file->isValid()) {
                    $imgName  = generateUniqueImageName($request->input('slug') . '-gallery');
                    $filename = (new ImageUploadManager())
                        ->file($file)
                        ->name($imgName)
                        ->path(self::GALLERY_PATH)
                        ->width(self::GALLERY_WIDTH)
                        ->height(self::GALLERY_HEIGHT)
                        ->upload();
                    (new ImageUploadManager())
                        ->file($file)
                        ->name($imgName)
                        ->path(self::GALLERY_THUMB_PATH)
                        ->width(self::THUMB_WIDTH)
                        ->height(self::THUMB_HEIGHT)
                        ->upload();
                    $photoPath = ltrim(self::GALLERY_PATH, '/') . $filename;
                    Log::info('Stored gallery photo: ' . $photoPath);

                    $photoRecord = $product->photos()->create([
                        'photo_path' => $photoPath,
                        'sort_order' => $sortOrder,
                        'status'     => 1 // Use direct value instead of constant
                    ]);
                    Log::info('Created photo record with ID: ' . $photoRecord->id);
                    $sortOrder++;
                } else {
                    Log::error('Invalid gallery file detected');
                }
            }
        } else {
            Log::info('No gallery files detected in request');
        }

        // Save specifications
        if ($request->has('specifications')) {
            foreach ($request->specifications as $specification) {
                if (!empty($specification['specification_id']) && !empty($specification['value'])) {
                    $product->specifications()->create([
                        'specification_id' => $specification['specification_id'],
                        'value'            => $specification['value']
                    ]);
                }
            }
        }

        // Save Q&As
        if ($request->has('qas')) {
            foreach ($request->qas as $index => $qa) {
                if (!empty($qa['question'])) {
                    $product->qas()->create([
                        'question'   => $qa['question'],
                        'answer'     => $qa['answer'] ?? '',
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // Save variations for variant products
        if ($request->input('product_type') === self::PRODUCT_TYPE_VARIANT && $request->has('variants')) {
            $this->saveVariations($product, $request);
        }

        return $product;
    }

    final public function updateProduct(Request $request): bool
    {
        $productData = $this->prepareData($request);

        // Handle single image uploads
        if ($request->hasFile('primary_photo')) {
            $imgName  = generateUniqueImageName($request->input('slug'));
            $filename = (new ImageUploadManager())
                ->file($request->file('primary_photo'))
                ->name($imgName)
                ->path(self::PRIMARY_PHOTO_PATH)
                ->width(self::PRIMARY_PHOTO_WIDTH)
                ->height(self::PRIMARY_PHOTO_HEIGHT)
                ->upload();
            (new ImageUploadManager())
                ->file($request->file('primary_photo'))
                ->name($imgName)
                ->path(self::PRIMARY_PHOTO_THUMB_PATH)
                ->width(self::THUMB_WIDTH)
                ->height(self::THUMB_HEIGHT)
                ->upload();
            // Delete old full + thumb after successful upload
            if ($this->primary_photo) {
                ImageUploadManager::deletePhoto($this->primary_photo);
                ImageUploadManager::deletePhoto(self::toThumbPath($this->primary_photo));
            }
            $productData['primary_photo'] = ltrim(self::PRIMARY_PHOTO_PATH, '/') . $filename;
        }

        if ($request->hasFile('og_image')) {
            $newPath = ltrim(self::OG_IMAGE_PATH, '/') . (new ImageUploadManager())
                ->file($request->file('og_image'))
                ->name(generateUniqueImageName($request->input('slug') . '-og'))
                ->path(self::OG_IMAGE_PATH)
                ->width(self::OG_IMAGE_WIDTH)
                ->height(self::OG_IMAGE_HEIGHT)
                ->upload();
            // Delete old image only after successful upload
            if ($this->og_image) {
                ImageUploadManager::deletePhoto($this->og_image);
            }
            $productData['og_image'] = $newPath;
        }

        // Handle gallery photos using ProductPhoto model
        if ($request->hasFile('gallery')) {
            // Delete existing gallery photos
            foreach ($this->photos as $photo) {
                ImageUploadManager::deletePhoto($photo->photo_path);
                ImageUploadManager::deletePhoto(self::toThumbPath($photo->photo_path));
                $photo->delete();
            }

            // Add new gallery photos
            $galleryFiles = $request->file('gallery');

            // Handle both single file and multiple files
            if (!is_array($galleryFiles)) {
                $galleryFiles = [$galleryFiles];
            }

            $sortOrder = 1;
            foreach ($galleryFiles as $file) {
                if ($file && $file->isValid()) {
                    $imgName  = generateUniqueImageName($request->input('slug') . '-gallery');
                    $filename = (new ImageUploadManager())
                        ->file($file)
                        ->name($imgName)
                        ->path(self::GALLERY_PATH)
                        ->width(self::GALLERY_WIDTH)
                        ->height(self::GALLERY_HEIGHT)
                        ->upload();
                    (new ImageUploadManager())
                        ->file($file)
                        ->name($imgName)
                        ->path(self::GALLERY_THUMB_PATH)
                        ->width(self::THUMB_WIDTH)
                        ->height(self::THUMB_HEIGHT)
                        ->upload();
                    $this->photos()->create([
                        'photo_path' => ltrim(self::GALLERY_PATH, '/') . $filename,
                        'sort_order' => $sortOrder,
                        'status'     => ProductPhoto::STATUS_ACTIVE
                    ]);
                    $sortOrder++;
                }
            }
        }

        // Update the product
        $updated = $this->update($productData);

        // Update specifications
        if ($request->has('specifications')) {
            // Delete existing specifications
            $this->specifications()->delete();

            // Add new specifications
            foreach ($request->specifications as $specification) {
                if (!empty($specification['specification_id']) && !empty($specification['value'])) {
                    $this->specifications()->create([
                        'specification_id' => $specification['specification_id'],
                        'value'            => $specification['value']
                    ]);
                }
            }
        }

        // Update Q&As
        $this->qas()->delete();
        if ($request->has('qas')) {
            foreach ($request->qas as $index => $qa) {
                if (!empty($qa['question'])) {
                    $this->qas()->create([
                        'question'   => $qa['question'],
                        'answer'     => $qa['answer'] ?? '',
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // Sync variations for variant products
        if ($request->input('product_type') === self::PRODUCT_TYPE_VARIANT && $request->has('variants')) {
            $this->syncVariations($this, $request);
        }

        return $updated;
    }

    /**
     * Save (create) variations from the request for a freshly created product.
     */
    private function saveVariations(self $product, Request $request): void
    {
        $variantInputs = $request->input('variants', []);
        $variantFiles  = $request->file('variants') ?? [];

        foreach ($variantInputs as $vIndex => $variant) {
            if (empty($variant['attribute_name_ids']) || empty($variant['price'])) {
                continue;
            }

            $variationData = [
                'sku'        => self::normalizeSku($variant['sku'] ?? null),
                'price'      => $variant['price'] ?? null,
                'sale_price' => !empty($variant['sale_price']) ? $variant['sale_price'] : null,
                'cost'       => !empty($variant['cost']) ? $variant['cost'] : null,
                'stock'      => isset($variant['stock']) ? (int) $variant['stock'] : 0,
                'status'     => 1,
                'sort_order' => (int) $vIndex,
            ];

            // Handle per-variation image upload
            $imageFile = $variantFiles[$vIndex]['image'] ?? null;
            if ($imageFile && $imageFile->isValid()) {
                $imgName  = generateUniqueImageName('var-' . $product->slug . '-' . $vIndex);
                $filename = (new ImageUploadManager())
                    ->file($imageFile)
                    ->name($imgName)
                    ->path(ProductVariation::VARIATION_IMAGE_PATH)
                    ->width(ProductVariation::VARIATION_IMAGE_WIDTH)
                    ->height(ProductVariation::VARIATION_IMAGE_HEIGHT)
                    ->upload();
                (new ImageUploadManager())
                    ->file($imageFile)
                    ->name($imgName)
                    ->path(ProductVariation::VARIATION_IMAGE_THUMB_PATH)
                    ->width(self::THUMB_WIDTH)
                    ->height(self::THUMB_HEIGHT)
                    ->upload();
                $variationData['image'] = ltrim(ProductVariation::VARIATION_IMAGE_PATH, '/') . $filename;
            }

            $variation = $product->variations()->create($variationData);

            // Save attribute combos
            $nameIds  = $variant['attribute_name_ids']  ?? [];
            $valueIds = $variant['attribute_value_ids'] ?? [];
            foreach ($nameIds as $i => $nameId) {
                $valueId = $valueIds[$i] ?? null;
                if ($nameId && $valueId) {
                    $variation->variationAttributes()->create([
                        'attribute_name_id'  => $nameId,
                        'attribute_value_id' => $valueId,
                    ]);
                }
            }
        }
    }

    /**
     * Sync variations for an existing product:
     * – Keep variations that still exist (by ID), update them.
     * – Create new variations (no ID).
     * – Soft-delete variations whose IDs are no longer in the request.
     */
    private function syncVariations(self $product, Request $request): void
    {
        $variantInputs  = $request->input('variants', []);
        $variantFiles   = $request->file('variants') ?? [];
        $submittedIds   = [];

        foreach ($variantInputs as $vIndex => $variant) {
            if (empty($variant['attribute_name_ids']) || empty($variant['price'])) {
                continue;
            }

            $existingId = !empty($variant['id']) ? (int) $variant['id'] : null;

            $variationData = [
                'sku'        => self::normalizeSku($variant['sku'] ?? null),
                'price'      => $variant['price'] ?? null,
                'sale_price' => !empty($variant['sale_price']) ? $variant['sale_price'] : null,
                'cost'       => !empty($variant['cost']) ? $variant['cost'] : null,
                'stock'      => isset($variant['stock']) ? (int) $variant['stock'] : 0,
                'status'     => 1,
                'sort_order' => (int) $vIndex,
            ];

            // Handle per-variation image upload
            $imageFile = $variantFiles[$vIndex]['image'] ?? null;
            if ($imageFile && $imageFile->isValid()) {
                $imgName  = generateUniqueImageName('var-' . $product->slug . '-' . $vIndex);
                $filename = (new ImageUploadManager())
                    ->file($imageFile)
                    ->name($imgName)
                    ->path(ProductVariation::VARIATION_IMAGE_PATH)
                    ->width(ProductVariation::VARIATION_IMAGE_WIDTH)
                    ->height(ProductVariation::VARIATION_IMAGE_HEIGHT)
                    ->upload();
                (new ImageUploadManager())
                    ->file($imageFile)
                    ->name($imgName)
                    ->path(ProductVariation::VARIATION_IMAGE_THUMB_PATH)
                    ->width(self::THUMB_WIDTH)
                    ->height(self::THUMB_HEIGHT)
                    ->upload();
                $variationData['image'] = ltrim(ProductVariation::VARIATION_IMAGE_PATH, '/') . $filename;
            }

            if ($existingId) {
                $variation = ProductVariation::find($existingId);
                if ($variation && $variation->product_id === $product->id) {
                    // Keep image if no new file uploaded
                    if (!isset($variationData['image'])) {
                        unset($variationData['image']);
                    }
                    $variation->update($variationData);
                    // Re-sync attributes
                    $variation->variationAttributes()->delete();
                } else {
                    $existingId = null; // treat as new
                }
            }

            if (!$existingId) {
                $variation = $product->variations()->create($variationData);
            }

            // Save attribute combos
            $nameIds  = $variant['attribute_name_ids']  ?? [];
            $valueIds = $variant['attribute_value_ids'] ?? [];
            foreach ($nameIds as $i => $nameId) {
                $valueId = $valueIds[$i] ?? null;
                if ($nameId && $valueId) {
                    $variation->variationAttributes()->create([
                        'attribute_name_id'  => $nameId,
                        'attribute_value_id' => $valueId,
                    ]);
                }
            }

            $submittedIds[] = $variation->id;
        }

        // Soft-delete variations no longer in payload
        $product->variations()
            ->whereNotIn('id', $submittedIds)
            ->each(fn ($v) => $v->delete());
    }

    private function prepareData(Request $request): array
    {
        return [
            'product_type'      => $request->input('product_type'),
            'category_id'       => $request->input('category_id'),
            'brand_id'          => $request->input('brand_id') ?: null,
            'sku'               => self::normalizeSku($request->input('sku')),
            'name'              => $request->input('name'),
            'slug'              => Str::slug($request->input('slug')),
            'short_description' => $request->input('short_description'),
            'description'       => $request->input('description'),
            'cost'              => $request->input('cost'),
            'price'             => $request->input('price'),
            'sale_price'        => $request->input('sale_price'),
            'stock'             => $request->input('stock'),
            'meta_title'        => $request->input('meta_title'),
            'meta_description'  => $request->input('meta_description'),
            'status'            => $request->input('status'),
            'sort_order'        => $request->input('sort_order'),
            'stock_text'        => $request->input('stock_text'),
            'key_benefits'      => array_values(array_filter($request->input('key_benefits', []))),
        ];
    }

    final public function getProductAssoc(): Collection
    {
        return self::query()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function getProductTypeText(): string
    {
        return self::PRODUCT_TYPE_LIST[$this->product_type] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    final public function isSimpleProduct(): bool
    {
        return $this->product_type === self::PRODUCT_TYPE_SIMPLE;
    }

    final public function isVariantProduct(): bool
    {
        return $this->product_type === self::PRODUCT_TYPE_VARIANT;
    }

    /**
     * Returns ['min' => float, 'max' => float] from active variation prices,
     * or null if the product has no active variations.
     */
    public function getPriceRangeAttribute(): ?array
    {
        if (!$this->isVariantProduct()) {
            return null;
        }

        // Reuse already-loaded relation to avoid N+1 on listing pages.
        $variations = $this->relationLoaded('activeVariations')
            ? $this->activeVariations
            : $this->hasMany(ProductVariation::class)
                ->where('status', 1)
                ->get(['id', 'product_id', 'price', 'sale_price']);

        if ($variations->isEmpty()) {
            return null;
        }

        $prices = $variations
            ->map(fn ($v) => (float) ($v->sale_price ?: $v->price))
            ->filter()
            ->sort()
            ->values();

        if ($prices->isEmpty()) {
            return null;
        }

        return ['min' => $prices->first(), 'max' => $prices->last()];
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Trim a SKU and turn blank input into null so the unique index is never
     * tripped by empty strings.
     */
    public static function normalizeSku(?string $sku): ?string
    {
        $sku = trim((string) $sku);

        return $sku === '' ? null : $sku;
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function qas()
    {
        return $this->hasMany(ProductQa::class)->orderBy('sort_order');
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function activePhotos()
    {
        return $this->hasMany(ProductPhoto::class)->where('status', 1)->orderBy('sort_order');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('sort_order');
    }

    public function activeVariations()
    {
        return $this->hasMany(ProductVariation::class)
            ->where('status', 1)
            ->with(['variationAttributes.attributeName', 'variationAttributes.attributeValue'])
            ->orderBy('sort_order');
    }
}

