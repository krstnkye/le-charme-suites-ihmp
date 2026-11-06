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

    /**
     * Status changes a task may make. Completed and cancelled tasks are final.
     *
     * @var array<string, list<string>>
     */
    public const ALLOWED_TRANSITIONS = [
        'queued' => ['in_progress', 'cancelled'],
        'in_progress' => ['queued', 'inspection', 'completed', 'cancelled'],
        'inspection' => ['in_progress', 'completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Task types that must be inspected before they can be completed.
     *
     * @var list<string>
     */
    public const TYPES_REQUIRING_INSPECTION = ['turnover', 'deep_cleaning'];

    /**
     * Task types whose completion can put a cleaned room back on sale.
     *
     * @var list<string>
     */
    public const ROOM_RELEASING_TYPES = ['turnover', 'inspection', 'deep_cleaning'];

    /**
     * Open task types that keep a room off sale until they are finished.
     *
     * @var list<string>
     */
    public const ROOM_BLOCKING_TYPES = ['turnover', 'inspection', 'deep_cleaning', 'maintenance_follow_up'];

    /**
     * Statuses that only housekeeping staff or an administrator may set.
     *
     * @var list<string>
     */
    public const HOUSEKEEPING_ONLY_STATUSES = ['in_progress', 'inspection', 'completed'];

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

    /**
     * Quick tasks (stayover, guest request, maintenance follow-up) may be
     * completed straight from the queue; cleaning that needs inspection may not.
     */
    public function canMoveTo(string $status): bool
    {
        if ($status === $this->status) {
            return true;
        }

        if ($this->status === 'queued' && $status === 'completed') {
            return ! $this->requiresInspection();
        }

        return in_array($status, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }

    public function requiresInspection(): bool
    {
        return in_array($this->task_type, self::TYPES_REQUIRING_INSPECTION, true);
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
