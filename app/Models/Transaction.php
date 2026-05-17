<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number', 'user_id', 'customer_id', 'subtotal', 'discount',
        'tax', 'total', 'amount_paid', 'change_amount',
        'payment_method', 'reference_number', 'status', 'notes', 'void_reason', 'voided_by', 'voided_at',
        'wheel_spin_token', 'wheel_spun_at',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'total'        => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'change_amount' => 'decimal:2',
        'voided_at'    => 'datetime',
        'wheel_spun_at' => 'datetime',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function voidedBy() { return $this->belongsTo(User::class, 'voided_by'); }
    public function items()    { return $this->hasMany(TransactionItem::class); }

    public static function generateNumber(): string
    {
        $prefix = 'TXN-' . date('Ymd') . '-';
        $last = self::where('transaction_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->first();
        $seq = $last ? (intval(substr($last->transaction_number, -4)) + 1) : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
