<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', static function (Blueprint $table) {
            $table->string('mobile_banner')->nullable()->after('banner');
        });
    }

    public function down(): void
    {
        Schema::table('brands', static function (Blueprint $table) {
            $table->dropColumn('mobile_banner');
        });
    }
};
