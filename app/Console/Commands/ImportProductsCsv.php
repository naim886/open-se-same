<?php

namespace App\Console\Commands;

use App\Services\ProductCsvImporter;
use Illuminate\Console\Command;

class ImportProductsCsv extends Command
{
    protected $signature = 'products:import-csv
                            {file=public/products.csv : CSV path (absolute or relative to the project root)}
                            {--no-images : Skip downloading and processing images}
                            {--skip-variations : Do not create variations for variable products}
                            {--replace-images : Replace existing images when a product already has them}
                            {--limit=0 : Only process the first N importable rows}';

    protected $description = 'Import products, categories, brands and images from the WooCommerce CSV export';

    public function handle(ProductCsvImporter $importer): int
    {
        $path = (string) $this->argument('file');

        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        if (!is_file($path)) {
            $this->error('CSV file not found: ' . $path);

            return self::FAILURE;
        }

        $this->info('Importing: ' . $path);
        $this->line('This can take a while when images are being downloaded.');
        $this->newLine();

        try {
            $stats = $importer->import($path, [
                'download_images'   => !$this->option('no-images'),
                'import_variations' => !$this->option('skip-variations'),
                'update_existing'   => true,
                'replace_images'    => (bool) $this->option('replace-images'),
                'limit'             => (int) $this->option('limit'),
            ]);
        } catch (\Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['CSV rows', $stats['rows']],
                ['Products created', $stats['products_created']],
                ['Products updated', $stats['products_updated']],
                ['Products skipped', $stats['products_skipped']],
                ['Variations created', $stats['variations_created']],
                ['Variations updated', $stats['variations_updated']],
                ['Legacy variation products migrated', $stats['source_products_migrated']],
                ['Categories created', $stats['categories_created']],
                ['Brands created', $stats['brands_created']],
                ['Images downloaded', $stats['images_downloaded']],
                ['Images failed', $stats['images_failed']],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->warn('Row errors (showing first 20):');

            foreach (array_slice($stats['errors'], 0, 20) as $error) {
                $this->line(' - ' . $error);
            }
        }

        $this->newLine();
        $this->info('Import finished.');

        return self::SUCCESS;
    }
}
