<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    /**
     * Get the tasks that have this status
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
