<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class ConvertProductImages extends Command
{
    protected $signature   = 'products:convert-images {--dry-run : Preview what would be done without making changes}';
    protected $description = 'Convert all product images to WebP and generate 265×265 thumbnails';

    private int $converted  = 0;
    private int $thumbsOnly = 0;
    private int $skipped    = 0;
    private bool $dryRun    = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY-RUN mode — no files will be written or DB rows updated.');
        }

        $this->info('=== Converting Product Primary Photos ===');
        $this->processPrimaryPhotos();

        $this->info('');
        $this->info('=== Converting Gallery Photos ===');
        $this->processGalleryPhotos();

        $this->info('');
        $this->info("Done.  Converted: {$this->converted} | Thumbs only: {$this->thumbsOnly} | Skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------

    private function processPrimaryPhotos(): void
    {
        $products = Product::query()->whereNotNull('primary_photo')->get();
        $this->line("Found {$products->count()} products with a primary photo.");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $this->convertPrimaryPhoto($product);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function processGalleryPhotos(): void
    {
        $photos = ProductPhoto::query()->get();
        $this->line("Found {$photos->count()} gallery photos.");

        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        foreach ($photos as $photo) {
            $this->convertGalleryPhoto($photo);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    // -----------------------------------------------------------------------

    private function convertPrimaryPhoto(Product $product): void
    {
        $storedPath = $product->primary_photo;                        // e.g. uploads/products/primary/abc.jpg
        $absPath    = Storage::path($storedPath);

        if (!file_exists($absPath)) {
            $this->warn("\n  [SKIP] File not found: {$storedPath}");
            $this->skipped++;
            return;
        }

        $basename = pathinfo($absPath, PATHINFO_FILENAME);            // abc  (no extension)
        $dir      = ltrim(Product::PRIMARY_PHOTO_PATH, '/');           // uploads/products/primary/
        $thumbDir = ltrim(Product::PRIMARY_PHOTO_THUMB_PATH, '/');     // uploads/products/primary/thumbs/

        $newFilename = $basename . '.webp';
        $newPath     = $dir . $newFilename;                            // uploads/products/primary/abc.webp
        $thumbPath   = Storage::path($thumbDir) . $newFilename;

        $alreadyWebp     = strtolower(pathinfo($absPath, PATHINFO_EXTENSION)) === 'webp';
        $thumbExists     = file_exists($thumbPath);
        $pathUnchanged   = ($storedPath === $newPath);

        // Nothing to do?
        if ($alreadyWebp && $pathUnchanged && $thumbExists) {
            $this->skipped++;
            return;
        }

        if ($this->dryRun) {
            $action = $alreadyWebp ? 'thumb-only' : 'convert+thumb';
            $this->line("\n  [DRY] {$action}: {$storedPath} → {$newPath}");
            return;
        }

        // Ensure directories exist
        $this->ensureDir(Storage::path($dir));
        $this->ensureDir(Storage::path($thumbDir));

        $manager = new ImageManager(new Driver());

        // Full-size WebP (800×800)
        if (!$alreadyWebp || !$pathUnchanged) {
            $manager->read($absPath)
                ->cover(Product::PRIMARY_PHOTO_WIDTH, Product::PRIMARY_PHOTO_HEIGHT)
                ->save(Storage::path($dir) . $newFilename, 80, 'webp');
        }

        // Thumbnail WebP (265×265)
        if (!$thumbExists || !$alreadyWebp) {
            $manager->read($absPath)
                ->cover(Product::THUMB_WIDTH, Product::THUMB_HEIGHT)
                ->save($thumbPath, 80, 'webp');
        }

        // Delete old non-webp file
        if (!$alreadyWebp && file_exists($absPath)) {
            unlink($absPath);
        }

        // Update DB
        if (!$pathUnchanged) {
            $product->updateQuietly(['primary_photo' => $newPath]);
        }

        $alreadyWebp ? $this->thumbsOnly++ : $this->converted++;
    }

    private function convertGalleryPhoto(ProductPhoto $photo): void
    {
        $storedPath = $photo->photo_path;
        $absPath    = Storage::path($storedPath);

        if (!file_exists($absPath)) {
            $this->warn("\n  [SKIP] File not found: {$storedPath}");
            $this->skipped++;
            return;
        }

        $basename = pathinfo($absPath, PATHINFO_FILENAME);
        $dir      = ltrim(Product::GALLERY_PATH, '/');
        $thumbDir = ltrim(Product::GALLERY_THUMB_PATH, '/');

        $newFilename = $basename . '.webp';
        $newPath     = $dir . $newFilename;
        $thumbPath   = Storage::path($thumbDir) . $newFilename;

        $alreadyWebp   = strtolower(pathinfo($absPath, PATHINFO_EXTENSION)) === 'webp';
        $thumbExists   = file_exists($thumbPath);
        $pathUnchanged = ($storedPath === $newPath);

        if ($alreadyWebp && $pathUnchanged && $thumbExists) {
            $this->skipped++;
            return;
        }

        if ($this->dryRun) {
            $action = $alreadyWebp ? 'thumb-only' : 'convert+thumb';
            $this->line("\n  [DRY] {$action}: {$storedPath} → {$newPath}");
            return;
        }

        $this->ensureDir(Storage::path($dir));
        $this->ensureDir(Storage::path($thumbDir));

        $manager = new ImageManager(new Driver());

        if (!$alreadyWebp || !$pathUnchanged) {
            $manager->read($absPath)
                ->cover(Product::GALLERY_WIDTH, Product::GALLERY_HEIGHT)
                ->save(Storage::path($dir) . $newFilename, 80, 'webp');
        }

        if (!$thumbExists || !$alreadyWebp) {
            $manager->read($absPath)
                ->cover(Product::THUMB_WIDTH, Product::THUMB_HEIGHT)
                ->save($thumbPath, 80, 'webp');
        }

        if (!$alreadyWebp && file_exists($absPath)) {
            unlink($absPath);
        }

        if (!$pathUnchanged) {
            $photo->updateQuietly(['photo_path' => $newPath]);
        }

        $alreadyWebp ? $this->thumbsOnly++ : $this->converted++;
    }

    // -----------------------------------------------------------------------

    private function ensureDir(string $absDir): void
    {
        if (!is_dir($absDir)) {
            mkdir($absDir, 0777, true);
        }
    }
}
