<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('type', 10)->default('fixed')->comment('fixed|percent');
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Cap applied to percent coupons');
            $table->unsignedInteger('usage_limit')->nullable()->comment('null = unlimited');
            $table->unsignedInteger('per_customer_limit')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_code')->nullable()->after('product_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'discount_amount']);
        });

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
