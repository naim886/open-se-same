<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchKeyword extends Model
{
    protected $fillable = ['keyword', 'count'];

    /**
     * Insert the keyword or increment its count if it already exists.
     */
    public static function record(string $keyword): void
    {
        $keyword = mb_strtolower(trim($keyword));

        if ($keyword === '') {
            return;
        }

        $record = static::firstOrCreate(['keyword' => $keyword]);

        if (! $record->wasRecentlyCreated) {
            $record->increment('count');
        }
    }
}
