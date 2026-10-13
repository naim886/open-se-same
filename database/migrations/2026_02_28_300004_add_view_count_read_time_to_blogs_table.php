<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('author');
            $table->unsignedSmallInteger('read_time')->default(0)->after('view_count')
                  ->comment('Estimated read time in minutes');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['view_count', 'read_time']);
        });
    }
};
