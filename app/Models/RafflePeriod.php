<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RafflePeriod extends Model
{
    protected $fillable = [
        'name', 'description', 'starts_at', 'ends_at',
        'status', 'winner_ticket', 'winner_entry_id', 'ended_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'ended_at'  => 'datetime',
    ];

    public function entries()
    {
        return $this->hasMany(RaffleEntry::class);
    }

    public function winnerEntry()
    {
        return $this->belongsTo(RaffleEntry::class, 'winner_entry_id');
    }

    public static function getActive(): ?self
    {
        return static::where('status', 'active')->latest()->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getTotalEntriesAttribute(): int
    {
        return (int) $this->entries()->sum('entries');
    }
}
