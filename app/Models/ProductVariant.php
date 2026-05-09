<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'color', 'price', 'cost_price', 'stock_quantity', 'sku', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getVariantInfoAttribute()
    {
        return $this->size . ' / ' . $this->color;
    }

    public function isLowStock($threshold = 5)
    {
        return $this->stock_quantity <= $threshold;
    }
}
