<?php

namespace App\Models;

use Database\Factories\HousekeepingTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTask extends Model
{
    /** @use HasFactory<HousekeepingTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'assigned_to',
        'created_by',
        'task_type',
        'title',
        'instructions',
        'priority',
        'status',
        'due_at',
        'started_at',
        'completed_at',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
