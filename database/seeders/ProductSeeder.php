<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $women = Category::where('slug', 'women')->first()->id;
        $men   = Category::where('slug', 'men')->first()->id;
        $kids  = Category::where('slug', 'kids')->first()->id;

        $nike = Brand::where('slug', 'nike')->first()->id;
        $hm   = Brand::where('slug', 'hm')->first()->id;
        $zara = Brand::where('slug', 'zara')->first()->id;

        $products = [
            [
                'name' => 'Floral Midi Dress',
                'category_id' => $women, 'brand_id' => $zara,
                'sku' => 'ZAR-FMD-001', 'description' => 'Elegant floral midi dress.',
                'variant' => ['size' => 'M', 'color' => 'White', 'price' => 1299, 'cost_price' => 650, 'stock_quantity' => 18],
            ],
            [
                'name' => 'Slim Fit Jeans',
                'category_id' => $women, 'brand_id' => $hm,
                'sku' => 'HM-SFJ-W01', 'description' => 'Classic slim fit jeans.',
                'variant' => ['size' => '28', 'color' => 'Blue', 'price' => 1899, 'cost_price' => 950, 'stock_quantity' => 25],
            ],
            [
                'name' => 'Classic Polo Shirt',
                'category_id' => $men, 'brand_id' => $nike,
                'sku' => 'NK-CPS-M01', 'description' => 'Breathable dry-fit polo shirt.',
                'variant' => ['size' => 'L', 'color' => 'White', 'price' => 999, 'cost_price' => 450, 'stock_quantity' => 22],
            ],
            [
                'name' => 'Graphic Tee',
                'category_id' => $men, 'brand_id' => $hm,
                'sku' => 'HM-GT-M01', 'description' => 'Streetwear graphic tee.',
                'variant' => ['size' => 'M', 'color' => 'Black', 'price' => 699, 'cost_price' => 280, 'stock_quantity' => 30],
            ],
            [
                'name' => 'Kids Hoodie',
                'category_id' => $kids, 'brand_id' => $nike,
                'sku' => 'NK-KH-K01', 'description' => 'Warm and soft hoodie for kids.',
                'variant' => ['size' => '6Y', 'color' => 'Gray', 'price' => 799, 'cost_price' => 360, 'stock_quantity' => 14],
            ],
        ];

        foreach ($products as $data) {
            $variant = $data['variant'];
            unset($data['variant']);

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, ['is_active' => true])
            );

            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'size' => $variant['size'], 'color' => $variant['color']],
                array_merge($variant, [
                    'sku'       => $data['sku'] . '-' . $variant['size'] . '-' . strtoupper(substr($variant['color'], 0, 3)),
                    'is_active' => true,
                ])
            );
        }
    }
}
