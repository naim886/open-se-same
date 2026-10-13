<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variation_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variation_id')->index();
            $table->unsignedBigInteger('attribute_name_id')->index();
            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variation_attributes');
    }
};
