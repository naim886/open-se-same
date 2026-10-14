<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('payment_method')->default(1)
                  ->comment('1=Cash on Delivery, 2=Online Payment')
                  ->after('delivery_location');
            $table->tinyInteger('payment_status')->default(1)
                  ->comment('1=Unpaid, 2=Paid')
                  ->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status']);
        });
    }
};
