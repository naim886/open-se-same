<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupLead extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'ip_address', 'user_agent'];
}
