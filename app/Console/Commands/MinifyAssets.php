<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MatthiasMullie\Minify;

class MinifyAssets extends Command
{
    protected $signature   = 'assets:minify
                                {--watch : Re-run whenever a source file changes}';

    protected $description = 'Minify local CSS and JS assets into *.min.css / *.min.js files (originals untouched)';

    /**
     * Source files to minify.
     * Key   = public-relative path to the SOURCE file (the one you edit).
     * Value = auto-generated: same path with .min.{ext} suffix.
     */
    private array $cssFiles = [
        'frontend/css/shop.css',
        'frontend/css/product-detail.css',
        'frontend/css/style.css',
        'frontend/css/valentine.css',
        'backend/css/custom_style.css',
        'backend/css/styles.css',
    ];

    private array $jsFiles = [
        'frontend/js/product-detail.js',
        'backend/js/scripts.js',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('  <fg=magenta>Open Se Same — Asset Minifier</>');
        $this->info('  ─────────────────────────────────────');

        $totalSaved = 0;

        // ── CSS ───────────────────────────────────────────────────────
        $this->line('');
        $this->line('  <fg=cyan>CSS files:</>');

        foreach ($this->cssFiles as $rel) {
            $source = public_path($rel);

            if (! file_exists($source)) {
                $this->line("  <fg=yellow>  SKIP</> {$rel}  <fg=gray>(file not found)</>  ");
                continue;
            }

            $target  = $this->minPath($source);
            $relMin  = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $target);

            $before = filesize($source);

            $minifier = new Minify\CSS($source);
            $minifier->setMaxImportSize(0); // don't inline external assets
            $minifier->minify($target);

            $after = filesize($target);
            $saved = $before - $after;
            $totalSaved += $saved;

            $this->line(sprintf(
                '  <fg=green>  OK</>  %-45s  %s → %s  <fg=green>(%s saved)</>',
                $relMin,
                $this->humanBytes($before),
                $this->humanBytes($after),
                $this->humanBytes($saved)
            ));
        }

        // ── JS ────────────────────────────────────────────────────────
        $this->line('');
        $this->line('  <fg=cyan>JS files:</>');

        foreach ($this->jsFiles as $rel) {
            $source = public_path($rel);

            if (! file_exists($source)) {
                $this->line("  <fg=yellow>  SKIP</> {$rel}  <fg=gray>(file not found)</>  ");
                continue;
            }

            $target  = $this->minPath($source);
            $relMin  = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $target);

            $before = filesize($source);

            $minifier = new Minify\JS($source);
            $minifier->minify($target);

            $after = filesize($target);
            $saved = $before - $after;
            $totalSaved += $saved;

            $this->line(sprintf(
                '  <fg=green>  OK</>  %-45s  %s → %s  <fg=green>(%s saved)</>',
                $relMin,
                $this->humanBytes($before),
                $this->humanBytes($after),
                $this->humanBytes($saved)
            ));
        }

        $this->line('');
        $this->info("  Total saved: {$this->humanBytes($totalSaved)}");
        $this->line('');

        return self::SUCCESS;
    }

    /** Convert foo/bar.css → foo/bar.min.css */
    private function minPath(string $absolutePath): string
    {
        $info = pathinfo($absolutePath);
        return $info['dirname'] . DIRECTORY_SEPARATOR
             . $info['filename'] . '.min.' . $info['extension'];
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
