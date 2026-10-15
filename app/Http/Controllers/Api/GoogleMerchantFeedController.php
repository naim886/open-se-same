<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Google Merchant Center Product Feed
 *
 * Exposes your product catalogue in three formats accepted by Google Merchant Center:
 *  • Tab-delimited text (.tsv)  → suitable for scheduled feed / direct upload
 *  • RSS 2.0 XML (.xml)         → suitable for scheduled feed / direct upload
 *  • JSON                       → structured data for programmatic consumption
 *
 * All endpoints are protected by a feed key supplied as a query parameter:
 *   ?key=<GOOGLE_MERCHANT_FEED_KEY>   (set in .env)
 *
 * Routes (add to routes/api.php):
 *   GET /api/v1/merchant/feed.tsv
 *   GET /api/v1/merchant/feed.xml
 *   GET /api/v1/merchant/products
 *
 * Google Merchant Center product data specification:
 *   https://support.google.com/merchants/answer/7052112
 */
class GoogleMerchantFeedController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Config helpers
    // ─────────────────────────────────────────────────────────────

    private function currency(): string
    {
        return config('services.google_merchant.currency', 'BDT');
    }

    private function brand(): string
    {
        return config('services.google_merchant.brand', config('app.name', 'Open Se Same'));
    }

    private function defaultGoogleProductCategory(): string
    {
        // Apparel & Accessories (ID: 166) — suitable for a closets / fashion store
        return config('services.google_merchant.google_product_category', 'Apparel & Accessories');
    }

    private function condition(): string
    {
        return config('services.google_merchant.condition', 'new');
    }

    // ─────────────────────────────────────────────────────────────
    // Auth guard
    // ─────────────────────────────────────────────────────────────

    private function authorise(Request $request): bool
    {
        $key = config('services.google_merchant.feed_key');

        // If no key is configured, deny access to prevent accidental public exposure
        if (empty($key)) {
            return false;
        }

        return hash_equals($key, (string) $request->query('key', ''));
    }

    private function unauthorized(): Response
    {
        return response('Unauthorized', 401, ['Content-Type' => 'text/plain']);
    }

    // ─────────────────────────────────────────────────────────────
    // Endpoints
    // ─────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/merchant/feed.tsv
     *
     * Returns a tab-delimited text feed (UTF-8 BOM so Excel opens it correctly).
     * Variant products produce one row per active variation.
     * Multiple additional images are comma-separated in the additional_image_link column.
     */
    public function tsv(Request $request): Response
    {
        if (!$this->authorise($request)) {
            return $this->unauthorized();
        }

        $items  = $this->buildItems();
        $output = $this->renderTsv($items);

        return response($output, 200, [
            'Content-Type'        => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="merchant-feed.tsv"',
        ]);
    }

    /**
     * GET /api/v1/merchant/feed.xml
     *
     * Returns an RSS 2.0 XML feed with Google's shopping namespace.
     */
    public function xml(Request $request): Response
    {
        if (!$this->authorise($request)) {
            return $this->unauthorized();
        }

        $items  = $this->buildItems();
        $output = $this->renderXml($items);

        return response($output, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * GET /api/v1/merchant/products
     *
     * Returns a JSON array of product items in the Google Content API for Shopping
     * resource shape (https://developers.google.com/shopping-content/reference/rest/v2.1/products).
     */
    public function json(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$this->authorise($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $items = $this->buildItems();

        return response()->json([
            'kind'     => 'content#productsListResponse',
            'currency' => $this->currency(),
            'count'    => $items->count(),
            'resources' => $items->values(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Data builder
    // ─────────────────────────────────────────────────────────────

    /**
     * Load all active products and expand variants into flat feed items.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildItems(): Collection
    {
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with([
                'category',
                'activePhotos',
                'activeVariations.variationAttributes.attributeName',
                'activeVariations.variationAttributes.attributeValue',
            ])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $items = collect();

        foreach ($products as $product) {
            /** @var Product $product */
            if ($product->product_type === Product::PRODUCT_TYPE_VARIANT
                && $product->activeVariations->isNotEmpty()) {
                // One feed item per active variation
                foreach ($product->activeVariations as $variation) {
                    $varItem = $this->buildVariantItem($product, $variation);
                    if ($this->isFeedItemValid($varItem)) {
                        $items->push($varItem);
                    }
                }
            } else {
                // Simple product → single feed item
                $simpleItem = $this->buildSimpleItem($product);
                if ($this->isFeedItemValid($simpleItem)) {
                    $items->push($simpleItem);
                }
            }
        }

        return $items;
    }

    // ─────────────────────────────────────────────────────────────
    // Item builders
    // ─────────────────────────────────────────────────────────────

    private function buildSimpleItem(Product $product): array
    {
        $price     = (float) ($product->price ?? 0);
        $currency  = $this->currency();

        // Only include a sale price when it is positive AND lower than the regular price
        $rawSale   = (float) ($product->sale_price ?? 0);
        $salePrice = ($rawSale > 0 && $rawSale < $price) ? $rawSale : null;

        $availability = $this->mapAvailability($product->stock);
        $description  = $this->buildDescription($product);
        $title        = $this->cleanText($product->name, 150);

        return [
            // ── Required ───────────────────────────────────────────
            'id'                      => 'mc-' . $product->id,
            'title'                   => $title,
            'description'             => $description,
            'link'                    => url('/product/' . $product->slug),
            'image_link'              => $this->imageUrl($product->primary_photo),
            'availability'            => $availability,
            'price'                   => number_format($price, 2, '.', '') . ' ' . $currency,
            'condition'               => $this->condition(),

            // ── Recommended ─────────────────────────────────────────
            'sale_price'              => $salePrice !== null
                ? number_format($salePrice, 2, '.', '') . ' ' . $currency
                : '',
            'brand'                   => $this->brand(),
            'google_product_category' => $this->resolveGoogleCategory($product->category),
            'product_type'            => $this->resolveProductType($product->category),
            'identifier_exists'       => 'no',

            // ── Optional / tracking ─────────────────────────────────
            'additional_image_link'   => $this->additionalImageUrls($product),
            'item_group_id'           => '',
            'color'                   => '',
            'size'                    => '',
            'material'                => '',
            'pattern'                 => '',
            'gender'                  => $this->resolveGender($product->category, $title),
            'age_group'               => $this->resolveAgeGroup($product->category),
            'adult'                   => $this->resolveAdult($product),
            'custom_label_0'          => $product->category?->name ?? '',
        ];
    }

    private function buildVariantItem(Product $product, $variation): array
    {
        $price    = (float) ($variation->price ?? $product->price ?? 0);
        $currency = $this->currency();

        // Prefer variation sale price → product sale price, but only if positive AND less than price
        $rawSale   = (float) ($variation->sale_price ?? $product->sale_price ?? 0);
        $salePrice = ($rawSale > 0 && $rawSale < $price) ? $rawSale : null;

        $availability = $this->mapAvailability($variation->stock ?? $product->stock);

        // Extract colour / size / other attributes from the variation
        $color    = '';
        $size     = '';
        $material = '';
        $pattern  = '';

        foreach ($variation->variationAttributes ?? [] as $va) {
            $attrName  = strtolower($va->attributeName?->name ?? '');
            $attrValue = $va->attributeValue?->name ?? '';

            if (in_array($attrName, ['color', 'colour'])) {
                $color = $attrValue;
            } elseif ($attrName === 'size') {
                $size = $attrValue;
            } elseif ($attrName === 'material') {
                $material = $attrValue;
            } elseif ($attrName === 'pattern') {
                $pattern = $attrValue;
            }
        }

        // Build a readable variant title
        $variantLabel = collect([$color, $size, $material, $pattern])
            ->filter()
            ->implode(' / ');

        $title = $this->cleanText($product->name . ($variantLabel ? ' - ' . $variantLabel : ''), 150);

        // Variant photo: use variation's own image field, fall back to product primary
        $imageLink = $this->imageUrl($variation->image ?? $product->primary_photo);

        $description = $this->buildDescription($product);

        return [
            // ── Required ───────────────────────────────────────────
            'id'                      => 'mc-' . $product->id . '-v-' . $variation->id,
            'title'                   => $title,
            'description'             => $description,
            'link'                    => url('/product/' . $product->slug),
            'image_link'              => $imageLink,
            'availability'            => $availability,
            'price'                   => number_format($price, 2, '.', '') . ' ' . $currency,
            'condition'               => $this->condition(),

            // ── Recommended ─────────────────────────────────────────
            'sale_price'              => $salePrice !== null
                ? number_format($salePrice, 2, '.', '') . ' ' . $currency
                : '',
            'brand'                   => $this->brand(),
            'google_product_category' => $this->resolveGoogleCategory($product->category),
            'product_type'            => $this->resolveProductType($product->category),
            'identifier_exists'       => 'no',

            // ── Variant attributes ──────────────────────────────────
            'item_group_id'           => 'mc-' . $product->id,
            'color'                   => $color,
            'size'                    => $size,
            'material'                => $material,
            'pattern'                 => $pattern,

            // ── Optional ────────────────────────────────────────────
            'additional_image_link'   => $this->additionalImageUrls($product),
            'gender'                  => $this->resolveGender($product->category, $title),
            'age_group'               => $this->resolveAgeGroup($product->category),
            'adult'                   => $this->resolveAdult($product),
            'custom_label_0'          => $product->category?->name ?? '',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Renderers
    // ─────────────────────────────────────────────────────────────

    /** Build the TSV string with UTF-8 BOM. */
    private function renderTsv(Collection $items): string
    {
        if ($items->isEmpty()) {
            return "\xEF\xBB\xBF";
        }

        // Per Google spec, repeated attributes (e.g. additional_image_link) are
        // comma-separated in a single column cell for tab-delimited feeds.
        $columns = [
            'id', 'title', 'description', 'link', 'image_link',
            'availability', 'price', 'sale_price', 'condition',
            'brand', 'google_product_category', 'product_type',
            'item_group_id', 'color', 'size', 'material', 'pattern',
            'gender', 'age_group', 'adult', 'identifier_exists', 'custom_label_0',
            'additional_image_link',
        ];

        $lines   = [];
        $lines[] = implode("\t", $columns);

        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $col) {
                if ($col === 'additional_image_link') {
                    // Comma-join up to 10 additional image URLs in one cell
                    $extras = is_array($item['additional_image_link'])
                        ? array_filter($item['additional_image_link'])
                        : array_filter([$item['additional_image_link'] ?? '']);
                    $row[] = $this->tsvCell(implode(',', array_slice(array_values($extras), 0, 10)));
                } else {
                    $row[] = $this->tsvCell((string) ($item[$col] ?? ''));
                }
            }
            $lines[] = implode("\t", $row);
        }

        // UTF-8 BOM + CRLF line endings (required by Google for TSV)
        return "\xEF\xBB\xBF" . implode("\r\n", $lines) . "\r\n";
    }

    /** Build the RSS 2.0 XML string. */
    private function renderXml(Collection $items): string
    {
        $appName = htmlspecialchars(config('app.name', 'Open Se Same'), ENT_QUOTES, 'UTF-8');
        $appUrl  = htmlspecialchars(config('app.url', url('/')), ENT_QUOTES, 'UTF-8');
        $now     = htmlspecialchars(now()->format('r'), ENT_QUOTES, 'UTF-8');

        $lines   = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $lines[] = '  <channel>';
        $lines[] = '    <title>' . $appName . '</title>';
        $lines[] = '    <link>' . $appUrl . '</link>';
        $lines[] = '    <description>' . $appName . ' product feed for Google Merchant Center</description>';
        $lines[] = '    <pubDate>' . $now . '</pubDate>';

        $googleFields = [
            'id', 'title', 'description', 'link', 'condition',
            'availability', 'price', 'sale_price', 'brand',
            'google_product_category', 'product_type', 'identifier_exists',
            'item_group_id', 'color', 'size', 'material', 'pattern',
            'gender', 'age_group', 'adult', 'custom_label_0',
        ];

        foreach ($items as $item) {
            $lines[] = '    <item>';

            foreach ($googleFields as $field) {
                $value = (string) ($item[$field] ?? '');
                if ($value === '') {
                    continue;
                }

                // title, description, and link must be standard RSS elements without the g: prefix
                if (in_array($field, ['title', 'description', 'link'])) {
                    $lines[] = '      <' . $field . '><![CDATA[' . $this->escapeCdata($value) . ']]></' . $field . '>';
                } else {
                    $lines[] = '      <g:' . $field . '><![CDATA[' . $this->escapeCdata($value) . ']]></g:' . $field . '>';
                }
            }

            // image_link — always output when present
            if (!empty($item['image_link'])) {
                $lines[] = '      <g:image_link><![CDATA[' . $this->escapeCdata($item['image_link']) . ']]></g:image_link>';
            }

            // additional_image_link — one element per extra image
            $extras = is_array($item['additional_image_link'])
                ? $item['additional_image_link']
                : array_filter([$item['additional_image_link'] ?? '']);

            foreach ($extras as $extraUrl) {
                if (empty($extraUrl)) {
                    continue;
                }
                $lines[] = '      <g:additional_image_link><![CDATA[' . $this->escapeCdata($extraUrl) . ']]></g:additional_image_link>';
            }

            $lines[] = '    </item>';
        }

        $lines[] = '  </channel>';
        $lines[] = '</rss>';

        return implode("\n", $lines) . "\n";
    }

    /**
     * Escape a string for safe embedding inside a CDATA section.
     * The only sequence illegal inside CDATA is "]]>" — split it across two sections.
     */
    private function escapeCdata(string $value): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $value);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function mapAvailability(?int $stock): string
    {
        // Null stock means no stock tracking — treat as in_stock
        if ($stock === null) {
            return 'in_stock';
        }
        return $stock > 0 ? 'in_stock' : 'out_of_stock';
    }

    private function imageUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    /** Returns array of additional image URLs from gallery photos. */
    private function additionalImageUrls(Product $product): array
    {
        return $product->activePhotos
            ->map(fn($photo) => $this->imageUrl($photo->photo_path))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Reject items that would be immediately disapproved by Google:
     * missing image, missing price, or empty description.
     */
    private function isFeedItemValid(array $item): bool
    {
        if (empty($item['image_link'])) {
            return false;
        }
        if (empty($item['price']) || str_starts_with($item['price'], '0.00')) {
            return false;
        }
        if (empty($item['description'])) {
            return false;
        }
        return true;
    }

    /**
     * Map of lowercase category name keywords → valid Google product taxonomy full paths.
     * Values MUST be from: https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt
     * Checked in order — put more-specific keywords before broader ones.
     */
    private const CATEGORY_GOOGLE_MAP = [
        // Apparel — map all common miniature car/sleepwear keywords
        // Taxonomy ID 5713: Apparel & Accessories > Clothing > Sleepwear & Loungewear
        'miniature car'     => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'sleepwear'    => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'nightwear'    => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'nightgown'    => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'stocking'     => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'bodysuit'     => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'bodystocking' => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'bra'          => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'costume'      => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        'onesie'       => 'Apparel & Accessories > Clothing > Sleepwear & Loungewear',
        // Home & Garden — real preserved / dried flowers
        // Taxonomy ID 6936: Home & Garden > Decor > Dried Flowers
        'preserved'    => 'Home & Garden > Decor > Dried Flowers',
        'rose'         => 'Home & Garden > Decor > Dried Flowers',
        'flower'       => 'Home & Garden > Decor > Dried Flowers',
    ];

    /**
     * Resolve the google_product_category to a VALID Google taxonomy path.
     *
     * The stored category value (e.g. "Gift Item") is accepted ONLY when it already
     * looks like a valid taxonomy: contains ' > ' (full path) or is purely numeric (ID).
     * Otherwise we fall back to keyword mapping on the internal category name.
     */
    private function resolveGoogleCategory(?Category $category): string
    {
        if ($category) {
            $stored = trim((string) ($category->google_product_category ?? ''));
            // Valid taxonomy: full path contains '>' or is a bare numeric ID
            if ($stored !== '' && (str_contains($stored, '>') || ctype_digit($stored))) {
                return $stored;
            }

            // Map by internal category name keywords
            $lower = strtolower($category->name ?? '');
            foreach (self::CATEGORY_GOOGLE_MAP as $keyword => $googleCat) {
                if (str_contains($lower, $keyword)) {
                    return $googleCat;
                }
            }
        }

        return $this->defaultGoogleProductCategory();
    }

    /** Returns true when the resolved Google taxonomy is under Apparel & Accessories. */
    private function isApparelCategory(?Category $category): bool
    {
        return str_starts_with($this->resolveGoogleCategory($category), 'Apparel &');
    }

    /**
     * Resolve the gender attribute required by Google for all free-listing apparel.
     * Defaults to 'female' for this women's-fashion store.
     */
    private function resolveGender(?Category $category, string $title = ''): string
    {
        if (!$this->isApparelCategory($category)) {
            return '';
        }
        $hay = strtolower($title . ' ' . ($category?->name ?? ''));
        // Check 'women'/'female'/'girl' before 'men'/'male'/'boy' to avoid false matches
        if (str_contains($hay, 'women') || str_contains($hay, 'female') || str_contains($hay, 'girl')) {
            return 'female';
        }
        if (str_contains($hay, ' men') || str_contains($hay, 'male') || str_contains($hay, 'boy')) {
            return 'male';
        }
        // Default for this women's fashion store
        return 'female';
    }

    /** Returns 'adult' for apparel (required for free listings); empty for non-apparel. */
    private function resolveAgeGroup(?Category $category): string
    {
        return $this->isApparelCategory($category) ? 'adult' : '';
    }

    /**
     * Returns 'yes' for products whose title or description contains explicitly adult language.
     * Google requires adult=yes for nudity / sexually explicit content.
     */
    private function resolveAdult(Product $product): string
    {
        $hay = strtolower(
            ($product->name ?? '') . ' ' .
            strip_tags($product->short_description ?? $product->description ?? '')
        );
        $triggers = ['erotic', 'open cup', 'provocative', 'role play', 'roleplay'];
        foreach ($triggers as $word) {
            if (str_contains($hay, $word)) {
                return 'yes';
            }
        }
        return 'no';
    }

    private function resolveProductType(?Category $category): string
    {
        return $category?->name ?? '';
    }

    /**
     * Build a clean description that:
     * 1. Is non-empty (Google requirement).
     * 2. Is NOT identical to the title (best practice — identical title+description
     *    causes quality issues in Google's crawler).
     */
    private function buildDescription(Product $product): string
    {
        $title = $this->cleanText($product->name ?? '', 5000);

        // Candidates in preference order: short_description, then long description
        $raws = [
            strip_tags($product->short_description ?? ''),
            strip_tags($product->description ?? ''),
        ];

        foreach ($raws as $raw) {
            $text = $this->cleanText($raw, 5000);
            // Accept the candidate only when it is non-empty AND distinct from the title
            if ($text !== '' && strcasecmp($text, $title) !== 0) {
                return $text;
            }
        }

        // If all candidates are empty or identical to the title, use whichever is non-empty
        // and append a differentiating brand suffix so the description is not a duplicate.
        $fallback = $this->cleanText(strip_tags($product->short_description ?? $product->description ?? ''), 4900);
        if ($fallback !== '') {
            return $fallback . ' | ' . $this->brand();
        }

        // Absolute last resort: use the title itself (Google disapproves empty descriptions)
        return $title;
    }

    /** Strip control characters and trim to max length. */
    private function cleanText(string $text, int $maxLength): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        $text = preg_replace('/\s+/', ' ', trim($text));
        return mb_substr($text, 0, $maxLength);
    }

    /** Escape a cell value for TSV: replace tabs/newlines with spaces. */
    private function tsvCell(string $value): string
    {
        return str_replace(["\t", "\r", "\n"], ' ', $value);
    }
}
