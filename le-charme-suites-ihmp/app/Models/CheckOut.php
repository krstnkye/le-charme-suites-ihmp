<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'room_charges',
        'extra_guest_charges',
        'additional_charges',
        'total_charges',
        'balance_due',
        'folio_snapshot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'room_charges' => 'decimal:2',
            'extra_guest_charges' => 'decimal:2',
            'additional_charges' => 'decimal:2',
            'total_charges' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'folio_snapshot' => 'array',
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

    public function payments(): HasMany
    {
        return $this->hasMany(CheckOutPayment::class);
    }
}
