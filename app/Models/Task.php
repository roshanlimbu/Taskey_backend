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
        'status',
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
}
