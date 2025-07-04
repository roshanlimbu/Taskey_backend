<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class activities extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'type',
        'title',
        'description',
        'meta',
        'comments',
        'reply'
    ];

    protected $casts = [
        'comments' => 'array',
        'reply' => 'array',
        'meta' => 'array',
    ];

    protected $attributes = [
        'comments' => '[]',
        'reply' => '[]',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class, 'activity_id');
    }
}
