<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckOutPayment extends Model
{
    protected $fillable = [
        'check_out_id',
        'received_by',
        'amount',
        'payment_method',
        'reference',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    public function checkOut(): BelongsTo
    {
        return $this->belongsTo(CheckOut::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
