<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogFaq extends Model
{
    use HasFactory;

    protected $guarded = [];

    final public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }
}
