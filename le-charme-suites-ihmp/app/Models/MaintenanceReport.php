<?php

namespace App\Models;

use Database\Factories\MaintenanceReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReport extends Model
{
    /** @use HasFactory<MaintenanceReportFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'hvac' => 'Aircon & Ventilation',
        'plumbing' => 'Plumbing & Water',
        'door_lock' => 'Door Lock & Keycard',
        'electrical' => 'Electrical & Lights',
        'furniture' => 'Furniture & Fixtures',
        'tv_media' => 'TV & Wi-Fi',
        'other' => 'Other',
    ];

    public const SEVERITIES = [
        'low' => 'Low - fix when convenient',
        'normal' => 'Normal - fix today',
        'high' => 'High - affects guest comfort',
        'critical' => 'Critical - room cannot be used',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
    ];

    protected $fillable = [
        'room_id',
        'location',
        'category',
        'severity',
        'description',
        'photo_path',
        'status',
        'room_taken_out_of_service',
        'reported_by',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'room_taken_out_of_service' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status !== 'resolved';
    }

    public function locationLabel(): string
    {
        return $this->room ? 'Room '.$this->room->room_number : ($this->location ?: 'Hotel facility');
    }
}
