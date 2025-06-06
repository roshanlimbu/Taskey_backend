<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Reports extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'report',
    ];
}
