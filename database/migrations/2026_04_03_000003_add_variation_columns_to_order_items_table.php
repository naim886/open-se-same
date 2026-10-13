<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->index()->after('product_id');
            $table->string('variation_label')->nullable()->after('variation_id')
                ->comment('Human-readable e.g. Color: Red / Size: Large / Material: Siffon');
            // populate product_name at order time so it survives product deletion
            $table->string('product_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['variation_id', 'variation_label']);
        });
    }
};
