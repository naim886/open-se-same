<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', static function (Blueprint $table) {
            $table->string('mobile_image')->nullable()->after('image');
        });

        Schema::table('categories', static function (Blueprint $table) {
            $table->string('mobile_banner')->nullable()->after('banner');
        });
    }

    public function down(): void
    {
        Schema::table('banners', static function (Blueprint $table) {
            $table->dropColumn('mobile_image');
        });

        Schema::table('categories', static function (Blueprint $table) {
            $table->dropColumn('mobile_banner');
        });
    }
};
