<?php

namespace App\Services;

use App\Manager\Image\ImageUploadManager;
use App\Models\AttributeName;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProductCsvImporter
{
    private array $stats = [
        'rows'                    => 0,
        'products_created'        => 0,
        'products_updated'        => 0,
        'products_skipped'        => 0,
        'variations_created'      => 0,
        'variations_updated'      => 0,
        'source_products_migrated'=> 0,
        'categories_created'      => 0,
        'brands_created'          => 0,
        'images_downloaded'       => 0,
        'images_failed'           => 0,
        'errors'                  => [],
    ];

    /** @var array<string, Category> */
    private array $categoryCache = [];

    /** @var array<string, Brand> */
    private array $brandCache = [];

    /** @var array<string, string> */
    private array $imageFileCache = [];

    private string $tempDir = '';

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function import(string $csvPath, array $options = []): array
    {
        $options = array_merge([
            'download_images'   => true,
            'import_variations' => true,
            'update_existing'   => true,
            'replace_images'    => false,
            'limit'             => 0,
        ], $options);

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $this->tempDir = storage_path('app/csv-images');
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0777, true);
        }

        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the CSV file.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);
            throw new \RuntimeException('The CSV file is empty.');
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers    = array_map(static fn ($header) => trim((string) $header), $headers);

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $row = array_pad($row, count($headers), '');
            $data = @array_combine($headers, array_slice($row, 0, count($headers)));

            if ($data !== false) {
                $rows[] = $data;
            }
        }

        fclose($handle);

        $this->stats['rows'] = count($rows);

        $parentRows = [];

        foreach ($rows as $data) {
            if (strtolower(trim((string) ($data['Type'] ?? ''))) !== 'variable') {
                continue;
            }

            $key = $this->normalizeName($data['Name'] ?? '');
            if ($key !== '') {
                $parentRows[] = ['key' => $key, 'row' => $data];
            }
        }

        usort($parentRows, static fn ($a, $b) => strlen($b['key']) <=> strlen($a['key']));

        $groups             = [];
        $simpleRows         = [];
        $orphanVariations   = [];

        foreach ($rows as $data) {
            $type = strtolower(trim((string) ($data['Type'] ?? '')));

            if ($type === 'simple') {
                $simpleRows[] = $data;
                continue;
            }

            if ($type === 'variable') {
                $key = $this->normalizeName($data['Name'] ?? '');
                if ($key !== '' && !isset($groups[$key])) {
                    $groups[$key] = ['parent' => $data, 'variations' => []];
                }
                continue;
            }

            if ($type === 'variation') {
                $parent = $this->findParentForVariation($data['Name'] ?? '', $parentRows);

                if ($parent !== null) {
                    $key = $this->normalizeName($parent['Name'] ?? '');
                    if ($key !== '' && isset($groups[$key])) {
                        $groups[$key]['variations'][] = $data;
                    }
                } else {
                    $orphanVariations[] = $data;
                }
            }
        }

        $processed = 0;

        foreach ($simpleRows as $data) {
            $this->importSimpleProduct($data, $options);
            $processed++;

            if ((int) $options['limit'] > 0 && $processed >= (int) $options['limit']) {
                break;
            }
        }

        if ((int) $options['limit'] === 0 || $processed < (int) $options['limit']) {
            foreach ($groups as $group) {
                $this->importVariableGroup($group['parent'], $group['variations'], $options);
                $processed++;

                if ((int) $options['limit'] > 0 && $processed >= (int) $options['limit']) {
                    break;
                }
            }
        }

        if ((int) $options['limit'] === 0 || $processed < (int) $options['limit']) {
            foreach ($orphanVariations as $data) {
                $this->importSimpleProduct($data, $options, 'variation');
                $processed++;

                if ((int) $options['limit'] > 0 && $processed >= (int) $options['limit']) {
                    break;
                }
            }
        }

        $this->cleanupTempFiles();

        return $this->stats;
    }

    /**
     * Import a variable parent and its variations.
     *
     * @param  array<string, mixed>  $parentRow
     * @param  array<int, array<string, mixed>>  $variationRows
     * @param  array<string, mixed>  $options
     */
    private function importVariableGroup(array $parentRow, array $variationRows, array $options): void
    {
        try {
            $name = $this->cleanText($parentRow['Name'] ?? '');
            if ($name === '') {
                $this->stats['products_skipped']++;
                return;
            }

            $sku          = Product::normalizeSku($parentRow['SKU'] ?? null);
            $categories   = $this->resolveCategories((string) ($parentRow['Categories'] ?? ''));
            $brandId      = $categories['brand_name'] !== null
                ? $this->findOrCreateBrand($categories['brand_name'])->id
                : null;

            $existing = $sku
                ? Product::withTrashed()->where('sku', $sku)->first()
                : $this->findExistingProductByNameAndCategories($name, $brandId, $categories['category_ids']);

            if ($existing && $existing->trashed()) {
                $existing->restore();
            }

            if ($existing && !$options['update_existing']) {
                $this->stats['products_skipped']++;
                return;
            }

            $prices      = [];
            $salePrices  = [];
            $stockTotal  = 0;

            foreach ($variationRows as $variationRow) {
                $price = $this->decimal($variationRow['Regular price'] ?? null);
                if ($price > 0) {
                    $prices[] = $price;
                }

                $sale = $this->decimal($variationRow['Sale price'] ?? null);
                if ($sale > 0) {
                    $salePrices[] = $sale;
                }

                $stockTotal += $this->stockValue($variationRow);
            }

            $minPrice = $prices !== [] ? min($prices) : $this->decimal($parentRow['Regular price'] ?? null);
            $minSale  = $salePrices !== [] ? min($salePrices) : null;

            if ($minSale !== null && ($minPrice <= 0 || $minSale >= $minPrice)) {
                $minSale = null;
            }

            $published = $this->isPublished($parentRow, true);
            $status    = $published && ($minPrice > 0 || $variationRows !== [])
                ? Product::STATUS_ACTIVE
                : Product::STATUS_INACTIVE;

            $short = $this->plainText($parentRow['Short description'] ?? '');
            if ($short === '' && $variationRows !== []) {
                $short = $this->plainText($variationRows[0]['Short description'] ?? '');
            }
            if ($short === '') {
                $short = Str::limit($name, 500, '');
            }

            $description = $this->cleanDescription($parentRow['Description'] ?? '');
            if ($description === '' && $variationRows !== []) {
                $description = $this->cleanDescription($variationRows[0]['Description'] ?? '');
            }

            $data = [
                'product_type'      => Product::PRODUCT_TYPE_VARIANT,
                'category_id'       => $categories['primary_category_id'] ?? $categories['category_ids'][0] ?? null,
                'brand_id'          => $brandId,
                'sku'               => $sku,
                'name'              => $name,
                'slug'              => $this->uniqueProductSlug($name, $existing?->id),
                'short_description' => $short,
                'description'       => $description,
                'cost'              => null,
                'price'             => $minPrice,
                'sale_price'        => $minSale,
                'stock'             => $stockTotal,
                'meta_title'        => Str::limit($name, 255, ''),
                'meta_description'  => Str::limit($short, 500, ''),
                'status'            => $status,
                'sort_order'        => 1,
                'stock_text'        => null,
                'key_benefits'      => [],
            ];

            if ($existing) {
                $existing->update($data);
                $product = $existing;
                $this->stats['products_updated']++;
            } else {
                $product = Product::create($data);
                $this->stats['products_created']++;
            }

            if ($categories['category_ids'] !== []) {
                $product->categories()->sync($categories['category_ids']);
            } elseif ($product->category_id) {
                $product->categories()->sync([(int) $product->category_id]);
            }

            if ($options['import_variations'] && $variationRows !== []) {
                $galleryPaths = $this->syncCsvVariations($product, $variationRows, $parentRow, $options);

                if ($galleryPaths !== []) {
                    $this->buildParentImagesFromVariationImages($product, $galleryPaths);
                } else {
                    $this->importImages($product, (string) ($parentRow['Images'] ?? ''), $options);
                }

                $product->refresh();
                $variations = $product->variations()->get();

                if ($variations->isNotEmpty()) {
                    $prices    = $variations->map(fn ($variation) => (float) ($variation->sale_price ?: $variation->price))->filter();
                    $salePrices = $variations->pluck('sale_price')->filter();

                    $product->update([
                        'price'      => $prices->min() ?: $product->price,
                        'sale_price' => $salePrices->min() ?: null,
                        'stock'      => $variations->sum('stock'),
                    ]);
                }
            } elseif (!$product->primary_photo) {
                $this->importImages($product, (string) ($parentRow['Images'] ?? ''), $options);
            }
        } catch (Throwable $e) {
            $this->stats['products_skipped']++;
            $this->stats['errors'][] = ($parentRow['SKU'] ?? '') . ': ' . $e->getMessage();
            Log::warning('Product CSV variable group import failed', [
                'sku'     => $parentRow['SKU'] ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create/update ProductVariation records and their label attribute.
     *
     * @param  array<int, array<string, mixed>>  $variationRows
     * @param  array<string, mixed>  $parentRow
     * @param  array<string, mixed>  $options
     * @return array<int, string> image paths collected for the parent gallery
     */
    private function syncCsvVariations(Product $parent, array $variationRows, array $parentRow, array $options): array
    {
        $attributeName = AttributeName::withTrashed()->firstOrCreate(
            ['name' => 'Variation'],
            ['status' => AttributeName::STATUS_ACTIVE]
        );

        if ($attributeName->trashed()) {
            $attributeName->restore();
        }

        $keptIds      = [];
        $galleryPaths = [];

        foreach ($variationRows as $index => $row) {
            $variationName = $this->cleanText($row['Name'] ?? '');
            $label         = $this->variationLabel($variationName, $this->cleanText($parentRow['Name'] ?? ''), $index);
            $sku           = Product::normalizeSku($row['SKU'] ?? null);

            $price = $this->decimal($row['Regular price'] ?? null);
            if ($price <= 0) {
                $price = $this->decimal($parentRow['Regular price'] ?? null);
            }

            $sale = $this->decimal($row['Sale price'] ?? null);
            if ($sale <= 0 || ($price > 0 && $sale >= $price)) {
                $sale = null;
            }

            $stock     = $this->stockValue($row);
            $published = $this->isPublished($row, $this->isPublished($parentRow, true));
            $status    = $published && $price > 0 ? Product::STATUS_ACTIVE : Product::STATUS_INACTIVE;

            $variation = null;

            if ($sku !== null) {
                $variation = $parent->variations()->withTrashed()->where('sku', $sku)->first();
            }

            if (!$variation) {
                $variation = $parent->variations()
                    ->withTrashed()
                    ->whereHas('variationAttributes.attributeValue', static fn ($query) => $query->where('name', $label))
                    ->first();
            }

            if ($variation && $variation->trashed()) {
                $variation->restore();
            }

            $sourceProduct = null;
            if (!$variation || !$variation->image) {
                $sourceProduct = $this->findSourceProductForVariation($variationName, $sku);
            }

            $variationData = [
                'sku'        => $sku,
                'price'      => $price,
                'sale_price' => $sale,
                'cost'       => null,
                'stock'      => $stock,
                'status'     => $status,
                'sort_order' => $index + 1,
            ];

            if ($sourceProduct?->primary_photo) {
                $variationData['image'] = $sourceProduct->primary_photo;
            } elseif (!$variation?->image && $options['download_images']) {
                $downloaded = $this->downloadAndStoreVariationImage(
                    (string) ($row['Images'] ?? ''),
                    $parent->slug . '-' . ($label !== '' ? Str::slug($label) : 'variation-' . ($index + 1))
                );

                if ($downloaded !== null) {
                    $variationData['image'] = $downloaded;
                }
            }

            if ($variation) {
                $variation->update($variationData);
                $this->stats['variations_updated']++;
            } else {
                $variation = $parent->variations()->create($variationData);
                $this->stats['variations_created']++;
            }

            $attributeValue = AttributeValue::withTrashed()->firstOrCreate(
                [
                    'attribute_name_id' => $attributeName->id,
                    'name'              => Str::limit($label !== '' ? $label : $variationName, 255, ''),
                ],
                ['status' => AttributeValue::STATUS_ACTIVE]
            );

            if ($attributeValue->trashed()) {
                $attributeValue->restore();
            }

            $variation->variationAttributes()->delete();
            $variation->variationAttributes()->create([
                'attribute_name_id'  => $attributeName->id,
                'attribute_value_id' => $attributeValue->id,
            ]);

            $keptIds[] = $variation->id;

            if ($variation->image) {
                $galleryPaths[] = $variation->image;
            }

            if ($sourceProduct) {
                $galleryPaths = array_merge($galleryPaths, $sourceProduct->photos->pluck('photo_path')->all());

                // Hide the old standalone variation product without deleting its
                // image files: the variation and parent gallery reuse them.
                $sourceProduct->update([
                    'sku'  => null,
                    'slug' => $sourceProduct->slug . '-migrated-' . $sourceProduct->id,
                ]);
                $sourceProduct->categories()->detach();
                $sourceProduct->delete();
                $this->stats['source_products_migrated']++;
            }
        }

        $parent->variations()->whereNotIn('id', $keptIds)->delete();

        return array_values(array_unique(array_filter($galleryPaths)));
    }

    /**
     * Import a simple product (or an orphan variation when no parent exists).
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $options
     */
    private function importSimpleProduct(array $row, array $options, string $type = 'simple'): void
    {
        try {
            $name = $this->cleanText($row['Name'] ?? '');
            if ($name === '') {
                $this->stats['products_skipped']++;
                return;
            }

            $sku          = Product::normalizeSku($row['SKU'] ?? null);
            $categories   = $this->resolveCategories((string) ($row['Categories'] ?? ''));
            $brandId      = $categories['brand_name'] !== null
                ? $this->findOrCreateBrand($categories['brand_name'])->id
                : null;

            $existing = $sku
                ? Product::withTrashed()->where('sku', $sku)->first()
                : $this->findExistingProductByNameAndCategories($name, $brandId, $categories['category_ids']);

            if ($existing && $existing->trashed()) {
                $existing->restore();
            }

            if ($existing && !$options['update_existing']) {
                $this->stats['products_skipped']++;
                return;
            }

            $price = $this->decimal($row['Regular price'] ?? null);
            $sale  = $this->decimal($row['Sale price'] ?? null);

            if ($sale <= 0 || ($price > 0 && $sale >= $price)) {
                $sale = null;
            }

            $stock     = $this->stockValue($row);
            $published = $this->isPublished($row, true);
            $status    = $published && $price > 0 ? Product::STATUS_ACTIVE : Product::STATUS_INACTIVE;

            $short = $this->plainText($row['Short description'] ?? '');
            if ($short === '') {
                $short = Str::limit($name, 500, '');
            }

            $data = [
                'product_type'      => Product::PRODUCT_TYPE_SIMPLE,
                'category_id'       => $categories['primary_category_id'] ?? $categories['category_ids'][0] ?? null,
                'brand_id'          => $brandId,
                'sku'               => $sku,
                'name'              => $name,
                'slug'              => $this->uniqueProductSlug($name, $existing?->id),
                'short_description' => $short,
                'description'       => $this->cleanDescription($row['Description'] ?? ''),
                'cost'              => null,
                'price'             => $price,
                'sale_price'        => $sale,
                'stock'             => $stock,
                'meta_title'        => Str::limit($name, 255, ''),
                'meta_description'  => Str::limit($short, 500, ''),
                'status'            => $status,
                'sort_order'        => 1,
                'stock_text'        => null,
                'key_benefits'      => [],
            ];

            if ($existing) {
                $existing->update($data);
                $product = $existing;
                $this->stats['products_updated']++;
            } else {
                $product = Product::create($data);
                $this->stats['products_created']++;
            }

            if ($categories['category_ids'] !== []) {
                $product->categories()->sync($categories['category_ids']);
            } elseif ($product->category_id) {
                $product->categories()->sync([(int) $product->category_id]);
            }

            if ($options['download_images']) {
                $this->importImages($product, (string) ($row['Images'] ?? ''), $options);
            }
        } catch (Throwable $e) {
            $this->stats['products_skipped']++;
            $this->stats['errors'][] = ($row['SKU'] ?? '') . ': ' . $e->getMessage();
            Log::warning('Product CSV row import failed', [
                'sku'     => $row['SKU'] ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int, array{key: string, row: array<string, mixed>}>  $parentRows
     * @return array<string, mixed>|null
     */
    private function findParentForVariation(string $variationName, array $parentRows): ?array
    {
        $name = $this->normalizeName($variationName);

        foreach ($parentRows as $parentRow) {
            if (str_starts_with($name, $parentRow['key'] . ' -')) {
                return $parentRow['row'];
            }
        }

        return null;
    }

    private function variationLabel(string $variationName, string $parentName, int $index): string
    {
        $variationName = $this->cleanText($variationName);
        $parentName    = $this->cleanText($parentName);

        if ($parentName !== '' && str_starts_with($variationName, $parentName)) {
            $label = trim(substr($variationName, strlen($parentName)));
            $label = trim($label, " -\t\n\r\0\x0B");

            if ($label !== '') {
                return $label;
            }
        }

        $parts = preg_split('/\s+-\s+/', $variationName);
        $label = trim((string) end($parts));

        return $label !== '' ? $label : 'Variation ' . ($index + 1);
    }

    /**
     * @param  array<int, int>  $categoryIds
     */
    private function findExistingProductByNameAndCategories(string $name, ?int $brandId, array $categoryIds): ?Product
    {
        $targetCategoryIds = $categoryIds;
        sort($targetCategoryIds);

        $candidates = Product::withTrashed()
            ->with('categories')
            ->where('name', $name)
            ->when($brandId, static fn ($query) => $query->where('brand_id', $brandId))
            ->orderByDesc('id')
            ->get();

        foreach ($candidates as $candidate) {
            $candidateCategoryIds = $candidate->categories
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();
            sort($candidateCategoryIds);

            if ($candidateCategoryIds === $targetCategoryIds) {
                return $candidate;
            }
        }

        return null;
    }

    private function findSourceProductForVariation(string $variationName, ?string $sku): ?Product
    {
        if ($sku !== null) {
            $product = Product::query()
                ->with('photos')
                ->where('sku', $sku)
                ->where('product_type', Product::PRODUCT_TYPE_SIMPLE)
                ->first();

            if ($product) {
                return $product;
            }
        }

        return Product::query()
            ->with('photos')
            ->whereNull('sku')
            ->where('name', $variationName)
            ->where('product_type', Product::PRODUCT_TYPE_SIMPLE)
            ->first();
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function buildParentImagesFromVariationImages(Product $parent, array $paths): void
    {
        $paths = array_values(array_unique(array_filter($paths)));

        if ($paths === []) {
            return;
        }

        $parent->loadMissing('photos');
        $existingPaths = $parent->photos->pluck('photo_path')->all();

        if (!$parent->primary_photo) {
            $parent->update(['primary_photo' => array_shift($paths)]);
            $parent->refresh();
        }

        $sortOrder = (int) ($parent->photos->max('sort_order') ?? 0) + 1;

        foreach ($paths as $path) {
            if ($path === $parent->primary_photo || in_array($path, $existingPaths, true)) {
                continue;
            }

            $parent->photos()->create([
                'photo_path' => $path,
                'sort_order' => $sortOrder++,
                'status'     => ProductPhoto::STATUS_ACTIVE,
            ]);
        }
    }

    private function downloadAndStoreVariationImage(string $imagesCsv, string $baseName): ?string
    {
        $urls = array_values(array_filter(
            array_map('trim', explode(',', $imagesCsv)),
            static fn ($url) => $url !== ''
        ));

        if ($urls === [] || !filter_var($urls[0], FILTER_VALIDATE_URL)) {
            return null;
        }

        $tempPath = $this->downloadImage($urls[0]);

        if ($tempPath === null) {
            $this->stats['images_failed']++;
            return null;
        }

        try {
            $imageName = generateUniqueImageName($baseName);

            $filename = (new ImageUploadManager())
                ->file($tempPath)
                ->name($imageName)
                ->path(ProductVariation::VARIATION_IMAGE_PATH)
                ->width(ProductVariation::VARIATION_IMAGE_WIDTH)
                ->height(ProductVariation::VARIATION_IMAGE_HEIGHT)
                ->upload();

            (new ImageUploadManager())
                ->file($tempPath)
                ->name($imageName)
                ->path(ProductVariation::VARIATION_IMAGE_THUMB_PATH)
                ->width(Product::THUMB_WIDTH)
                ->height(Product::THUMB_HEIGHT)
                ->upload();

            $this->stats['images_downloaded']++;

            return ltrim(ProductVariation::VARIATION_IMAGE_PATH, '/') . $filename;
        } catch (Throwable $e) {
            $this->stats['images_failed']++;
            Log::warning('Product CSV variation image processing failed', [
                'url'     => $urls[0],
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{category_ids: array<int, int>, primary_category_id: int|null, brand_name: string|null}
     */
    private function resolveCategories(string $categoriesCsv): array
    {
        $categoryIds       = [];
        $primaryCategoryId = null;
        $brandName         = null;

        if (trim($categoriesCsv) === '') {
            return ['category_ids' => [], 'primary_category_id' => null, 'brand_name' => null];
        }

        foreach (preg_split('/\s*,\s*/', $categoriesCsv) as $pathIndex => $path) {
            $path = $this->cleanText($path);
            if ($path === '') {
                continue;
            }

            $parts = array_values(array_filter(
                array_map(fn ($part) => $this->cleanText($part), preg_split('/\s*>\s*/', $path)),
                static fn ($part) => $part !== ''
            ));

            $parentId = null;
            $leafId   = null;

            foreach ($parts as $index => $name) {
                if ($index === 0 && $brandName === null) {
                    $brandName = $name;
                }

                $category = $this->findOrCreateCategory($name, $parentId);
                $categoryIds[] = $category->id;
                $leafId = $category->id;
                $parentId = $category->id;
            }

            if ($pathIndex === 0 && $leafId !== null) {
                $primaryCategoryId = $leafId;
            }
        }

        return [
            'category_ids'        => array_values(array_unique($categoryIds)),
            'primary_category_id' => $primaryCategoryId,
            'brand_name'          => $brandName,
        ];
    }

    private function findOrCreateCategory(string $name, ?int $parentId): Category
    {
        $cacheKey = ((int) ($parentId ?? 0)) . '|' . Str::lower($name);

        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $category = Category::withTrashed()
            ->where('category_id', $parentId)
            ->where('name', $name)
            ->first();

        if ($category && $category->trashed()) {
            $category->restore();
        }

        if (!$category) {
            $base = Str::slug($name) ?: 'category';
            $slug = $base;
            $i    = 2;

            while (Category::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $category = Category::create([
                'name'              => $name,
                'slug'              => $slug,
                'category_id'       => $parentId,
                'short_description' => null,
                'status'            => Category::STATUS_ACTIVE,
                'sort_order'        => 0,
            ]);

            $this->stats['categories_created']++;
        }

        return $this->categoryCache[$cacheKey] = $category;
    }

    private function findOrCreateBrand(string $name): Brand
    {
        $cacheKey = Str::lower($name);

        if (isset($this->brandCache[$cacheKey])) {
            return $this->brandCache[$cacheKey];
        }

        $brand = Brand::withTrashed()->where('name', $name)->first();

        if ($brand && $brand->trashed()) {
            $brand->restore();
        }

        if (!$brand) {
            $base = Str::slug($name) ?: 'brand';
            $slug = $base;
            $i    = 2;

            while (Brand::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $brand = Brand::create([
                'name'              => $name,
                'slug'              => $slug,
                'short_description' => null,
                'status'            => Brand::STATUS_ACTIVE,
                'sort_order'        => 0,
            ]);

            $this->stats['brands_created']++;
        }

        return $this->brandCache[$cacheKey] = $brand;
    }

    private function uniqueProductSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i    = 2;

        while (Product::withTrashed()->where('slug', $slug)
            ->when($ignoreId, static fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function importImages(Product $product, string $imagesCsv, array $options): void
    {
        $urls = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $imagesCsv)),
            static fn ($url) => $url !== ''
        )));

        if ($urls === []) {
            return;
        }

        if ($product->primary_photo && empty($options['replace_images'])) {
            return;
        }

        if ($options['replace_images']) {
            if ($product->primary_photo) {
                ImageUploadManager::deletePhoto($product->primary_photo);
                ImageUploadManager::deletePhoto(Product::toThumbPath($product->primary_photo));
            }

            foreach ($product->photos as $photo) {
                ImageUploadManager::deletePhoto($photo->photo_path);
                ImageUploadManager::deletePhoto(Product::toThumbPath($photo->photo_path));
                $photo->delete();
            }

            $product->update(['primary_photo' => null]);
        }

        $isPrimary = true;
        $sortOrder = 1;

        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $tempPath = $this->downloadImage($url);

            if ($tempPath === null) {
                $this->stats['images_failed']++;
                continue;
            }

            try {
                if ($isPrimary) {
                    $imgName  = generateUniqueImageName($product->slug . '-primary');
                    $filename = (new ImageUploadManager())
                        ->file($tempPath)
                        ->name($imgName)
                        ->path(Product::PRIMARY_PHOTO_PATH)
                        ->width(Product::PRIMARY_PHOTO_WIDTH)
                        ->height(Product::PRIMARY_PHOTO_HEIGHT)
                        ->upload();

                    (new ImageUploadManager())
                        ->file($tempPath)
                        ->name($imgName)
                        ->path(Product::PRIMARY_PHOTO_THUMB_PATH)
                        ->width(Product::THUMB_WIDTH)
                        ->height(Product::THUMB_HEIGHT)
                        ->upload();

                    $product->update([
                        'primary_photo' => ltrim(Product::PRIMARY_PHOTO_PATH, '/') . $filename,
                    ]);

                    $isPrimary = false;
                } else {
                    $imgName  = generateUniqueImageName($product->slug . '-gallery');
                    $filename = (new ImageUploadManager())
                        ->file($tempPath)
                        ->name($imgName)
                        ->path(Product::GALLERY_PATH)
                        ->width(Product::GALLERY_WIDTH)
                        ->height(Product::GALLERY_HEIGHT)
                        ->upload();

                    (new ImageUploadManager())
                        ->file($tempPath)
                        ->name($imgName)
                        ->path(Product::GALLERY_THUMB_PATH)
                        ->width(Product::THUMB_WIDTH)
                        ->height(Product::THUMB_HEIGHT)
                        ->upload();

                    $product->photos()->create([
                        'photo_path' => ltrim(Product::GALLERY_PATH, '/') . $filename,
                        'sort_order' => $sortOrder++,
                        'status'     => ProductPhoto::STATUS_ACTIVE,
                    ]);
                }

                $this->stats['images_downloaded']++;
            } catch (Throwable $e) {
                $this->stats['images_failed']++;
                Log::warning('Product CSV image processing failed', [
                    'url'     => $url,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function downloadImage(string $url): ?string
    {
        if (isset($this->imageFileCache[$url])) {
            return $this->imageFileCache[$url];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; OpenSesameBD-CSV-Importer/1.0)',
            ])->timeout(60)->retry(2, 200)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)) {
                $extension = 'jpg';
            }

            $path = $this->tempDir . DIRECTORY_SEPARATOR . md5($url) . '.' . $extension;

            if (file_put_contents($path, $response->body()) === false) {
                return null;
            }

            return $this->imageFileCache[$url] = $path;
        } catch (Throwable $e) {
            Log::warning('Product CSV image download failed', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function cleanupTempFiles(): void
    {
        foreach ($this->imageFileCache as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->imageFileCache = [];
    }

    private function decimal(mixed $value): float
    {
        $value = trim(str_replace([',', ' '], '', (string) $value));

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function stockValue(array $row): int
    {
        $raw   = trim((string) ($row['Stock'] ?? ''));
        $stock = $raw !== '' && is_numeric($raw) ? (int) $raw : 10;

        return $stock > 0 ? $stock : 10;
    }

    private function isPublished(array $row, bool $default = true): bool
    {
        $value = trim((string) ($row['Published'] ?? ''));

        if ($value === '') {
            return $default;
        }

        return $value === '1';
    }

    private function normalizeName(string $name): string
    {
        $name = $this->cleanText($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return Str::lower($name);
    }

    /**
     * Convert literal escape sequences from the CSV (\\n, \\r\\n, \\t) into
     * normal text, then collapse whitespace for plain-text fields.
     */
    private function plainText(mixed $value, int $limit = 500): string
    {
        $value = $this->cleanDescription($value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace("\xC2\xA0", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return Str::limit(trim($value), $limit, '');
    }

    private function cleanDescription(mixed $value): string
    {
        $value = (string) $value;

        // WooCommerce exports contain literal "\n" / "\r\n" text rather than
        // real line breaks. Replace them (including double-escaped variants).
        $value = str_replace(['\\r\\n', '\\n', '\\r', '\\t'], ' ', $value);
        $value = str_replace(['\\r\\n', '\\n', '\\r', '\\t'], ' ', $value);

        return trim($value);
    }

    private function cleanText(mixed $value): string
    {
        return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
