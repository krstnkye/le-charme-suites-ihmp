<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DepartmentMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_department',
        'subject',
        'body',
        'priority',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_message_reads')
            ->withPivot('read_at');
    }
}
