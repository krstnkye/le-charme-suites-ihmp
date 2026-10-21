<?php

namespace App\Models;

use Database\Factories\LostFoundItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFoundItem extends Model
{
    /** @use HasFactory<LostFoundItemFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'electronics' => 'Electronics',
        'valuables' => 'Jewelry & Valuables',
        'clothing' => 'Clothing & Luggage',
        'documents' => 'Documents & IDs',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'in_custody' => 'In Custody',
        'claimed' => 'Claimed & Released',
        'disposed' => 'Donated / Disposed',
    ];

    protected $fillable = [
        'item_name',
        'category',
        'description',
        'room_id',
        'found_location',
        'found_at',
        'found_by',
        'storage_location',
        'status',
        'claimed_by_name',
        'claimant_id_details',
        'released_at',
        'released_by',
        'release_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'found_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function finder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'found_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function reference(): string
    {
        return 'LF-'.$this->found_at?->format('Y').'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function foundWhere(): string
    {
        return $this->room ? 'Room '.$this->room->room_number : ($this->found_location ?: 'Hotel premises');
    }
}
