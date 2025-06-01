<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['task_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}

