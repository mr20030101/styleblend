<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleEntry extends Model
{
    protected $fillable = [
        'raffle_period_id', 'customer_name', 'customer_phone', 'customer_id', 'transaction_id',
        'entries', 'ticket_numbers', 'is_winner',
    ];

    protected $casts = ['is_winner' => 'boolean'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function period()
    {
        return $this->belongsTo(RafflePeriod::class, 'raffle_period_id');
    }

    public function getTicketArrayAttribute(): array
    {
        return array_filter(explode(',', $this->ticket_numbers));
    }

    /** Generate the next sequential ticket numbers */
    public static function generateTickets(int $count, ?int $periodId = null): array
    {
        $query = static::orderBy('id', 'desc');
        if ($periodId) $query->where('raffle_period_id', $periodId);
        $last = $query->first();
        $lastNum = 0;

        if ($last) {
            $tickets = array_filter(explode(',', $last->ticket_numbers));
            if (!empty($tickets)) {
                $lastNum = (int) max(array_map(fn($t) => ltrim($t, '0') ?: '0', $tickets));
            }
        }

        $tickets = [];
        for ($i = 1; $i <= $count; $i++) {
            $tickets[] = str_pad($lastNum + $i, 6, '0', STR_PAD_LEFT);
        }
        return $tickets;
    }

    /** How many entries does a given total earn */
    public static function calcEntries(float $total, float $perEntry = 300): int
    {
        return (int) floor($total / $perEntry);
    }
}
