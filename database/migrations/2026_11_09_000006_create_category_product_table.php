<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'category_id']);
        });

        // Preserve every existing product's current category in the pivot.
        DB::table('products')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunkById(200, static function ($products): void {
                $rows = [];

                foreach ($products as $product) {
                    $rows[] = [
                        'product_id'  => $product->id,
                        'category_id' => $product->category_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('category_product')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
