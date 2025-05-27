<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fcm_tokens extends Model
{
    protected $fillable = ['token', 'device_id', 'platform', 'user_id'];
}
