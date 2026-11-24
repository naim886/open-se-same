<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('permission.table_names.roles', 'roles');

        Schema::table($table, static function (Blueprint $blueprint) {
            $blueprint->string('description')->nullable()->after('guard_name');
            $blueprint->boolean('is_locked')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        $table = config('permission.table_names.roles', 'roles');

        Schema::table($table, static function (Blueprint $blueprint) {
            $blueprint->dropColumn(['description', 'is_locked']);
        });
    }
};
