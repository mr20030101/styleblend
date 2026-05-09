<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Nike',  'slug' => 'nike',  'description' => 'Premium athletic wear', 'is_active' => true],
            ['name' => 'H&M',   'slug' => 'hm',    'description' => 'Fashion at the best price', 'is_active' => true],
            ['name' => 'Zara',  'slug' => 'zara',  'description' => 'Fast fashion retailer', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }
    }
}
