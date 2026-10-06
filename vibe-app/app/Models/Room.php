<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'room_type',
        'rate',
        'capacity',
        'status',
        'description',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RoomStatusLog::class);
    }
}
