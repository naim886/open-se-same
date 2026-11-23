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
            $table->string('courier_name')->nullable()->after('tracking_code');
            $table->string('courier_status')->nullable()->after('courier_name');
        });

        Schema::table('courier_logs', function (Blueprint $table) {
            $table->string('courier_name')->nullable()->after('tracking_code');
        });

        // Existing consignments were created through Steadfast.
        DB::table('orders')
            ->whereNotNull('consignment_id')
            ->whereNull('courier_name')
            ->update(['courier_name' => 'steadfast']);

        DB::table('courier_logs')
            ->whereNull('courier_name')
            ->update(['courier_name' => 'steadfast']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier_name', 'courier_status']);
        });

        Schema::table('courier_logs', function (Blueprint $table) {
            $table->dropColumn('courier_name');
        });
    }
};
