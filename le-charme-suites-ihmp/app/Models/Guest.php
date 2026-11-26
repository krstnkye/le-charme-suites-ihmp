<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'identification_type',
        'identification_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'identification_number' => 'encrypted',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getMaskedIdentificationNumberAttribute(): ?string
    {
        if (blank($this->identification_number)) {
            return null;
        }

        $number = (string) $this->identification_number;

        return str_repeat('•', max(0, mb_strlen($number) - 4)).mb_substr($number, -4);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(HotelTransaction::class);
    }
}
