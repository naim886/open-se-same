<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_histories';

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'previous',
        'changes',
        'items_previous',
        'items_new',
    ];

    protected $casts = [
        'previous' => 'array',
        'changes' => 'array',
        'items_previous' => 'array',
        'items_new' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}

