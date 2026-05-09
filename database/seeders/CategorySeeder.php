<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Women', 'slug' => 'women', 'description' => 'Women clothing'],
            ['name' => 'Men', 'slug' => 'men', 'description' => 'Men clothing'],
            ['name' => 'Kids', 'slug' => 'kids', 'description' => 'Kids clothing'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
