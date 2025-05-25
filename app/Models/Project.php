<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due_date',
    ];

    // Members: many-to-many with User
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    // Tasks: one-to-many
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Project lead: belongsTo User
    public function project_lead()
    {
        return $this->belongsTo(User::class, 'project_lead_id');
    }
}
