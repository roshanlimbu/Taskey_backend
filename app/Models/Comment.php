<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\activities;

class Comment extends Model
{
    protected $fillable = ['activity_id', 'user_id', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(activities::class, 'activity_id');
    }
}
