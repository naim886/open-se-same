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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // user who created the order
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // customer details
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('customer_email')->nullable();

            // products totals (products are stored in order_items table)
            $table->decimal('product_price', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // delivery meta
            $table->tinyInteger('delivery_location')->nullable()->comment('1=Inside Dhaka, 2=Outside Dhaka, 3=Express, 4=Super Express, 5=Free Delivery');

            $table->tinyInteger('status')->default(1)->comment('1=Pending, 2=Confirmed, 3=Processing, 4=Shipped, 5=Delivered, 6=Cancelled');
            $table->text('notes')->nullable();

            $table->timestamp('order_date');
            $table->timestamp('delivery_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
