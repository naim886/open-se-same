<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 20)->default('#6c757d');
            $table->timestamps();
        });

        // Customers are derived from orders by phone number, so tags and notes
        // attach to the phone rather than requiring a customers row to exist.
        Schema::create('customer_tag_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->foreignId('customer_tag_id')->constrained('customer_tags')->cascadeOnDelete();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            $table->unique(['phone', 'customer_tag_id']);
        });

        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->timestamps();
        });

        $now = now();

        DB::table('customer_tags')->insert(array_map(
            fn (array $tag) => $tag + ['created_at' => $now, 'updated_at' => $now],
            [
                ['name' => 'VIP', 'slug' => 'vip', 'color' => '#d4af37'],
                ['name' => 'Frequent Buyer', 'slug' => 'frequent-buyer', 'color' => '#0d6efd'],
                ['name' => 'Wholesale', 'slug' => 'wholesale', 'color' => '#6f42c1'],
                ['name' => 'Blacklist', 'slug' => 'blacklist', 'color' => '#dc3545'],
            ]
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_tag_assignments');
        Schema::dropIfExists('customer_tags');
    }
};
