<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'website',
    ];
}
