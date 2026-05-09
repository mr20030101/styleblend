<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'product_variant_id', 'product_name',
        'variant_info', 'quantity', 'unit_price', 'subtotal'
    ];

    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
