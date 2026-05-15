<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Dress',
            'T-Shirt',
            'Polo',
            'Blouse',
            'Tops',
            'Pants',
            'Shorts',
            'Skirt',
            'Jacket',
            'Hoodie',
            'Swimwear',
            'Lingerie',
            'Shoes',
            'Bag',
            'Accessories',
            'Uncategorized',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }
    }
}
