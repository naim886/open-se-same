<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Roll the store's Meta Pixel over to the new pixel ID. The value stays
     * editable from Admin → Settings → Analytics.
     */
    public function up(): void
    {
        $now = now();

        $exists = DB::table('settings')
            ->where('key', 'analytics.meta.pixel_id')
            ->exists();

        if ($exists) {
            DB::table('settings')
                ->where('key', 'analytics.meta.pixel_id')
                ->update([
                    'value' => '2238997253621126',
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('settings')->insert([
            'key' => 'analytics.meta.pixel_id',
            'value' => '2238997253621126',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Tracking configuration is intentionally not rolled back.
    }
};
