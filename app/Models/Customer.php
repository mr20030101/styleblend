<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address', 'birthday', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'birthday' => 'date'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function raffleEntries()
    {
        return $this->hasMany(RaffleEntry::class);
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->transactions()->where('status', 'completed')->sum('total');
    }

    public function getTotalRaffleEntriesAttribute(): int
    {
        return (int) $this->raffleEntries()->sum('entries');
    }
}
