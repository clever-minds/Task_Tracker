<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'employee_id',
        'log_date',
        'reply_text',
        'task_id',
        'status_reported',
        'next_plan',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'replied_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
