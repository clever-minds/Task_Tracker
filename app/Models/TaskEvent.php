<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvent extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'task_id',
        'actor_type',
        'actor_id',
        'event_type',
        'message',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
