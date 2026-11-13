<?php

namespace App\Jobs;

use App\Services\ProductCsvImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ImportProductsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $csvPath,
        public array $options = []
    ) {
    }

    public function handle(ProductCsvImporter $importer): void
    {
        Cache::put('product_csv_import_status', [
            'state'      => 'running',
            'started_at' => now()->toDateTimeString(),
        ], now()->addDay());

        try {
            $stats = $importer->import($this->csvPath, $this->options);

            Cache::put('product_csv_import_status', [
                'state'       => 'finished',
                'finished_at' => now()->toDateTimeString(),
                'stats'       => $stats,
            ], now()->addDay());
        } catch (Throwable $e) {
            Cache::put('product_csv_import_status', [
                'state'       => 'failed',
                'finished_at' => now()->toDateTimeString(),
                'error'       => $e->getMessage(),
            ], now()->addDay());

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Cache::put('product_csv_import_status', [
            'state'       => 'failed',
            'finished_at' => now()->toDateTimeString(),
            'error'       => $exception->getMessage(),
        ], now()->addDay());
    }
}
