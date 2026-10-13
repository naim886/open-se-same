<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml covering all public frontend URLs';

    public function handle(): int
    {
        $this->info('');
        $this->info('  <fg=magenta>Open Se Same — Sitemap Generator</>');
        $this->info('  ─────────────────────────────────────');

        $sitemap = Sitemap::create();
        $count   = 0;

        // ── Static pages ──────────────────────────────────────────────
        $statics = [
            ['url' => route('shop.home'),        'priority' => 1.0, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('shop.products'),     'priority' => 1.0, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('shop.blogs'),        'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('shop.contact'),      'priority' => 0.5, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => route('shop.track-order'),  'priority' => 0.5, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
        ];

        foreach ($statics as $entry) {
            $sitemap->add(
                Url::create($entry['url'])
                    ->setPriority($entry['priority'])
                    ->setChangeFrequency($entry['freq'])
            );
            $count++;
        }

        $this->line("  <fg=green>  OK</>  Added <fg=cyan>{$count}</> static pages");

        // ── Categories ────────────────────────────────────────────────
        $categories = Category::query()
            ->where('status', Category::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']);

        foreach ($categories as $cat) {
            $sitemap->add(
                Url::create(route('shop.category', $cat->slug))
                    ->setLastModificationDate($cat->updated_at)
                    ->setPriority(1.0)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        $this->line("  <fg=green>  OK</>  Added <fg=cyan>{$categories->count()}</> category pages");
        $count += $categories->count();

        // ── Products ──────────────────────────────────────────────────
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get(['slug', 'updated_at', 'primary_photo']);

        foreach ($products as $product) {
            $url = Url::create(route('shop.product', $product->slug))
                ->setLastModificationDate($product->updated_at)
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

            // Add primary image for Google Image Sitemap
            if ($product->primary_photo) {
                $url->addImage(
                    asset('storage/' . $product->primary_photo),
                    $product->slug
                );
            }

            $sitemap->add($url);
        }

        $this->line("  <fg=green>  OK</>  Added <fg=cyan>{$products->count()}</> product pages");
        $count += $products->count();

        // ── Blogs ─────────────────────────────────────────────────────
        $blogs = Blog::query()
            ->where('status', Blog::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get(['slug', 'updated_at', 'cover_photo', 'title']);

        foreach ($blogs as $blog) {
            $url = Url::create(route('shop.blog', $blog->slug))
                ->setLastModificationDate($blog->updated_at)
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            if ($blog->cover_photo) {
                $url->addImage(
                    asset('storage/' . $blog->cover_photo),
                    $blog->title
                );
            }

            $sitemap->add($url);
        }

        $this->line("  <fg=green>  OK</>  Added <fg=cyan>{$blogs->count()}</> blog pages");
        $count += $blogs->count();

        // ── CMS Pages ─────────────────────────────────────────────────
        $pages = Page::query()
            ->where('status', Page::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get(['slug', 'updated_at']);

        foreach ($pages as $page) {
            $sitemap->add(
                Url::create(route('shop.page', $page->slug))
                    ->setLastModificationDate($page->updated_at)
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        }

        $this->line("  <fg=green>  OK</>  Added <fg=cyan>{$pages->count()}</> CMS pages");
        $count += $pages->count();

        // ── Write ─────────────────────────────────────────────────────
        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->line('');
        $this->info("  Total URLs written: <fg=cyan>{$count}</>");
        $this->info("  Saved to: <fg=yellow>public/sitemap.xml</>");
        $this->line('');

        return self::SUCCESS;
    }
}
