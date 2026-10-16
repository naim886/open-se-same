<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('category_id');
            $table->unsignedBigInteger('brand_id')->nullable()->index()->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', static function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'brand_id']);
        });
    }
};
