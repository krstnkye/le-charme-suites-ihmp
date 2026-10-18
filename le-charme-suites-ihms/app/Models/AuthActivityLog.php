<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'event_type',
        'successful',
        'ip_address',
        'user_agent',
        'details',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
