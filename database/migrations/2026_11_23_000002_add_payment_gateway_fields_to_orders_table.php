<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_status');
            $table->string('payment_transaction_id')->nullable()->after('payment_gateway');
            $table->string('payment_reference')->nullable()->after('payment_transaction_id');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->string('purchase_event_id')->nullable()->after('paid_at');
            $table->json('analytics_context')->nullable()->after('purchase_event_id');
        });

        DB::table('orders')
            ->where('payment_status', 2)
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_transaction_id',
                'payment_reference',
                'paid_at',
                'purchase_event_id',
                'analytics_context',
            ]);
        });
    }
};
