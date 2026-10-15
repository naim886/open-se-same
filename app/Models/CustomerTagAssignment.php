<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTagAssignment extends Model
{
    protected $guarded = [];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(CustomerTag::class, 'customer_tag_id');
    }
}
