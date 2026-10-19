<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type',
        'rate',
        'capacity',
        'base_occupancy',
        'room_size_sqm',
        'bed_configuration',
        'bathrooms',
        'extra_guest_rate',
        'amenities',
        'non_smoking',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'room_size_sqm' => 'decimal:2',
            'extra_guest_rate' => 'decimal:2',
            'amenities' => 'array',
            'non_smoking' => 'boolean',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RoomStatusLog::class);
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class);
    }
}
