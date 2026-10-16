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
        Schema::create('homepage_sections', static function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('banner, flash_sale, top_selling, new_products, category');
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title')->nullable()->comment('Optional heading overriding the block default');
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
