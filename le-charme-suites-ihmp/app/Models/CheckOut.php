<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckOut extends Model
{
    protected $fillable = [
        'check_in_id',
        'reservation_id',
        'guest_id',
        'room_id',
        'checked_out_at',
        'amount_paid',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
        ];
    }

    public function checkIn(): BelongsTo
    {
        return $this->belongsTo(CheckIn::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
