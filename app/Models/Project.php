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
        'members',
        'project_lead_id',
        'company_id',
    ];


    // Tasks: one-to-many
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    // Project lead: belongsTo User
    public function project_lead()
    {
        return $this->belongsTo(User::class, 'project_lead_id');
    }
    // Company: belongsTo Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
