<?php

namespace Database\Seeders;

use App\Models\WheelPrize;
use Illuminate\Database\Seeder;

class WheelPrizeSeeder extends Seeder
{
    public function run(): void
    {
        WheelPrize::truncate();

        $prizes = [
            ['name' => '₱10',  'description' => '₱10 off your purchase',  'color' => '#4ade80', 'percentage' => 16.00, 'discount_type' => 'fixed',      'discount_value' => 10],
            ['name' => '₱15',  'description' => '₱15 off your purchase',  'color' => '#34d399', 'percentage' => 14.00, 'discount_type' => 'fixed',      'discount_value' => 15],
            ['name' => '₱30',  'description' => '₱30 off your purchase',  'color' => '#60a5fa', 'percentage' => 12.00, 'discount_type' => 'fixed',      'discount_value' => 30],
            ['name' => '₱50',  'description' => '₱50 off your purchase',  'color' => '#818cf8', 'percentage' => 10.00, 'discount_type' => 'fixed',      'discount_value' => 50],
            ['name' => '₱60',  'description' => '₱60 off your purchase',  'color' => '#a78bfa', 'percentage' =>  8.00, 'discount_type' => 'fixed',      'discount_value' => 60],
            ['name' => '₱70',  'description' => '₱70 off your purchase',  'color' => '#f472b6', 'percentage' =>  6.00, 'discount_type' => 'fixed',      'discount_value' => 70],
            ['name' => '₱80',  'description' => '₱80 off your purchase',  'color' => '#fb923c', 'percentage' =>  6.00, 'discount_type' => 'fixed',      'discount_value' => 80],
            ['name' => '₱100', 'description' => '₱100 off your purchase', 'color' => '#f87171', 'percentage' =>  5.00, 'discount_type' => 'fixed',      'discount_value' => 100],
            ['name' => '5%',   'description' => '5% discount',            'color' => '#38bdf8', 'percentage' => 10.00, 'discount_type' => 'percentage', 'discount_value' => 5],
            ['name' => '10%',  'description' => '10% discount',           'color' => '#c084fc', 'percentage' =>  5.00, 'discount_type' => 'percentage', 'discount_value' => 10],
            ['name' => '15%',  'description' => '15% discount',           'color' => '#fbbf24', 'percentage' =>  3.00, 'discount_type' => 'percentage', 'discount_value' => 15],
            ['name' => '20%',  'description' => '20% discount',           'color' => '#e879f9', 'percentage' =>  5.00, 'discount_type' => 'percentage', 'discount_value' => 20],
        ];
        // 16+14+12+10+8+6+6+5+10+5+3+5 = 100 ✓

        foreach ($prizes as $prize) {
            WheelPrize::create(array_merge($prize, ['is_active' => true]));
        }
    }
}
