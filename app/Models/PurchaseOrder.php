<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'user_id', 'status',
        'total_cost', 'order_date', 'received_date', 'notes',
    ];

    protected $casts = ['order_date' => 'date', 'received_date' => 'date'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function items()    { return $this->hasMany(PurchaseOrderItem::class); }

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $last = static::where('po_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq  = $last ? (intval(substr($last->po_number, -4)) + 1) : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
