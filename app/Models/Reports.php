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

    // Relationship to project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
