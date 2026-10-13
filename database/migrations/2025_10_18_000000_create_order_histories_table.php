<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates order_histories table to track order changes.
     */
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action')->nullable(); // e.g. 'updated', 'status_changed', 'created'
            $table->json('previous')->nullable(); // previous data
            $table->json('changes')->nullable(); // what changed (new values or diffs)
            $table->json('items_previous')->nullable(); // optional: previous order_items
            $table->json('items_new')->nullable(); // optional: new order_items
            $table->timestamps();

            // foreign keys not enforced here to keep migration simple across environments
            $table->index(['order_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};

