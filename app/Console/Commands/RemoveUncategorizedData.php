<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class RemoveUncategorizedData extends Command
{
    protected $signature = 'cleanup:uncategorized';

    protected $description = 'Remove the fallback Uncategorized category and brand (products are kept)';

    public function handle(): int
    {
        $names = ['uncategorized', 'uncategorised'];

        $categoryCount = 0;

        Category::withTrashed()
            ->whereRaw('LOWER(name) IN (?, ?)', $names)
            ->get()
            ->each(function (Category $category) use (&$categoryCount) {
                $parentId = $category->category_id ? (int) $category->category_id : null;

                Product::query()
                    ->where('category_id', $category->id)
                    ->update(['category_id' => $parentId]);

                $category->products()->detach();

                Category::query()
                    ->where('category_id', $category->id)
                    ->update(['category_id' => $parentId]);

                if (! $category->trashed()) {
                    $category->delete();
                }

                $categoryCount++;
            });

        $brandCount = 0;

        Brand::withTrashed()
            ->whereRaw('LOWER(name) IN (?, ?)', $names)
            ->get()
            ->each(function (Brand $brand) use (&$brandCount) {
                Product::query()
                    ->where('brand_id', $brand->id)
                    ->update(['brand_id' => null]);

                if (! $brand->trashed()) {
                    $brand->delete();
                }

                $brandCount++;
            });

        $this->info("Removed {$categoryCount} Uncategorized category row(s) and {$brandCount} brand row(s).");

        return self::SUCCESS;
    }
}
