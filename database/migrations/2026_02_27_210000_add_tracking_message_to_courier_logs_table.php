<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_logs', function (Blueprint $table) {
            $table->text('tracking_message')->nullable()->after('status')->comment('Latest tracking message from webhook');
            $table->string('source')->nullable()->after('tracking_message')->comment('api or webhook');
        });
    }

    public function down(): void
    {
        Schema::table('courier_logs', function (Blueprint $table) {
            $table->dropColumn(['tracking_message', 'source']);
        });
    }
};
