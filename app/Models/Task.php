<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'assigned_to',
        'status_id',
        'due_date',
        'need_help',
    ];

    protected $casts = [
        'need_help' => 'boolean',
    ];

    // Project relationship
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Assignee relationship
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }

    // Status relationship
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // get commit hashes
    public function commitHashes()
    {
        return $this->hasMany(Task_commit_hash::class, 'task_id');
    }
}
