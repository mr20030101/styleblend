<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelPrize extends Model
{
    protected $fillable = ['name', 'description', 'color', 'percentage', 'is_active', 'discount_type', 'discount_value'];

    protected $casts = ['is_active' => 'boolean', 'percentage' => 'float', 'discount_value' => 'float'];
}
