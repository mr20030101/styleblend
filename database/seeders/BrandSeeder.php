<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Just Do It - Premium athletic wear and footwear',
                'is_active' => true
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Impossible is Nothing - Sports and lifestyle brand',
                'is_active' => true
            ],
            [
                'name' => 'Uniqlo',
                'slug' => 'uniqlo',
                'description' => 'LifeWear - Simple, high-quality everyday clothing',
                'is_active' => true
            ],
            [
                'name' => 'H&M',
                'slug' => 'hm',
                'description' => 'Fashion and quality at the best price',
                'is_active' => true
            ],
            [
                'name' => 'Zara',
                'slug' => 'zara',
                'description' => 'Fast fashion retailer with trendy designs',
                'is_active' => true
            ],
            [
                'name' => 'Levi\'s',
                'slug' => 'levis',
                'description' => 'Original jeans company since 1853',
                'is_active' => true
            ]
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }
    }
}