<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task_commit_hash extends Model
{
    protected $table = 'task_commit_hashes';

    protected $fillable = [
        'task_id',
        'project_id',
        'commit_hash',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getCommitHashAttribute($value)
    {
        return $value ? substr($value, 0, 7) : null; // returns first 7 characters of the commit hash
    }
    // a task can have many commit hashes
    public function taskCommitHashes()
    {
        return $this->hasMany(Task_commit_hash::class, 'task_id');
    }
}
