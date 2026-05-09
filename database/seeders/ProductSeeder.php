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

        $nike   = Brand::where('name', 'Nike')->first()->id;
        $adidas = Brand::where('name', 'Adidas')->first()->id;
        $hm     = Brand::where('name', 'H&M')->first()->id;
        $zara   = Brand::where('name', 'Zara')->first()->id;
        $levis  = Brand::where('name', "Levi's")->first()->id;
        $shein  = Brand::where('name', 'Shein')->first()->id;

        $products = [

            // ── WOMEN ──────────────────────────────────────────────────
            [
                'name' => 'Floral Midi Dress',
                'category_id' => $women, 'brand_id' => $zara,
                'sku' => 'ZAR-FMD-001', 'description' => 'Elegant floral midi dress perfect for any occasion.',
                'variants' => [
                    ['size' => 'S',  'color' => 'White', 'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 12],
                    ['size' => 'M',  'color' => 'White', 'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 18],
                    ['size' => 'L',  'color' => 'White', 'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 8],
                    ['size' => 'S',  'color' => 'Pink',  'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 10],
                    ['size' => 'M',  'color' => 'Pink',  'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 15],
                    ['size' => 'L',  'color' => 'Pink',  'price' => 1299, 'cost_price' => 650,  'stock_quantity' => 6],
                ],
            ],
            [
                'name' => 'Slim Fit Jeans',
                'category_id' => $women, 'brand_id' => $levis,
                'sku' => 'LEV-SFJ-W01', 'description' => 'Classic slim fit jeans with stretch comfort.',
                'variants' => [
                    ['size' => '27', 'color' => 'Blue',  'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 20],
                    ['size' => '28', 'color' => 'Blue',  'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 25],
                    ['size' => '29', 'color' => 'Blue',  'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 15],
                    ['size' => '27', 'color' => 'Black', 'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 18],
                    ['size' => '28', 'color' => 'Black', 'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 22],
                    ['size' => '29', 'color' => 'Black', 'price' => 1899, 'cost_price' => 950,  'stock_quantity' => 10],
                ],
            ],
            [
                'name' => 'Cotton Crop Top',
                'category_id' => $women, 'brand_id' => $hm,
                'sku' => 'HM-CCT-W01', 'description' => 'Soft 100% cotton crop top, everyday essential.',
                'variants' => [
                    ['size' => 'XS', 'color' => 'White', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 30],
                    ['size' => 'S',  'color' => 'White', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 35],
                    ['size' => 'M',  'color' => 'White', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 30],
                    ['size' => 'L',  'color' => 'White', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 20],
                    ['size' => 'XS', 'color' => 'Black', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 28],
                    ['size' => 'S',  'color' => 'Black', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 32],
                    ['size' => 'M',  'color' => 'Black', 'price' => 399,  'cost_price' => 150,  'stock_quantity' => 28],
                    ['size' => 'S',  'color' => 'Pink',  'price' => 399,  'cost_price' => 150,  'stock_quantity' => 25],
                    ['size' => 'M',  'color' => 'Pink',  'price' => 399,  'cost_price' => 150,  'stock_quantity' => 20],
                ],
            ],
            [
                'name' => 'Oversized Blazer',
                'category_id' => $women, 'brand_id' => $shein,
                'sku' => 'SHN-OBL-W01', 'description' => 'Trendy oversized blazer for a chic, polished look.',
                'variants' => [
                    ['size' => 'S',  'color' => 'Beige', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 14],
                    ['size' => 'M',  'color' => 'Beige', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 18],
                    ['size' => 'L',  'color' => 'Beige', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 10],
                    ['size' => 'S',  'color' => 'Black', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 16],
                    ['size' => 'M',  'color' => 'Black', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 20],
                    ['size' => 'L',  'color' => 'Black', 'price' => 899,  'cost_price' => 400,  'stock_quantity' => 8],
                ],
            ],
            [
                'name' => 'Knit Cardigan',
                'category_id' => $women, 'brand_id' => $hm,
                'sku' => 'HM-KNC-W01', 'description' => 'Cozy knit cardigan, perfect for layering.',
                'variants' => [
                    ['size' => 'S',  'color' => 'Brown', 'price' => 799,  'cost_price' => 380,  'stock_quantity' => 3],
                    ['size' => 'M',  'color' => 'Brown', 'price' => 799,  'cost_price' => 380,  'stock_quantity' => 5],
                    ['size' => 'L',  'color' => 'Brown', 'price' => 799,  'cost_price' => 380,  'stock_quantity' => 2],
                    ['size' => 'S',  'color' => 'Cream', 'price' => 799,  'cost_price' => 380,  'stock_quantity' => 4],
                    ['size' => 'M',  'color' => 'Cream', 'price' => 799,  'cost_price' => 380,  'stock_quantity' => 6],
                ],
            ],

            // ── MEN ────────────────────────────────────────────────────
            [
                'name' => 'Classic Polo Shirt',
                'category_id' => $men, 'brand_id' => $nike,
                'sku' => 'NK-CPS-M01', 'description' => 'Breathable dry-fit polo shirt for everyday wear.',
                'variants' => [
                    ['size' => 'S',  'color' => 'White', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 20],
                    ['size' => 'M',  'color' => 'White', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 25],
                    ['size' => 'L',  'color' => 'White', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 22],
                    ['size' => 'XL', 'color' => 'White', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 15],
                    ['size' => 'S',  'color' => 'Navy',  'price' => 999,  'cost_price' => 450,  'stock_quantity' => 18],
                    ['size' => 'M',  'color' => 'Navy',  'price' => 999,  'cost_price' => 450,  'stock_quantity' => 24],
                    ['size' => 'L',  'color' => 'Navy',  'price' => 999,  'cost_price' => 450,  'stock_quantity' => 20],
                    ['size' => 'M',  'color' => 'Black', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 20],
                    ['size' => 'L',  'color' => 'Black', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 18],
                    ['size' => 'XL', 'color' => 'Black', 'price' => 999,  'cost_price' => 450,  'stock_quantity' => 12],
                ],
            ],
            [
                'name' => 'Slim Chino Pants',
                'category_id' => $men, 'brand_id' => $zara,
                'sku' => 'ZAR-SCP-M01', 'description' => 'Smart slim-cut chino pants, office to weekend.',
                'variants' => [
                    ['size' => '30', 'color' => 'Khaki', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 15],
                    ['size' => '32', 'color' => 'Khaki', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 20],
                    ['size' => '34', 'color' => 'Khaki', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 12],
                    ['size' => '30', 'color' => 'Navy',  'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 14],
                    ['size' => '32', 'color' => 'Navy',  'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 18],
                    ['size' => '30', 'color' => 'Black', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 16],
                    ['size' => '32', 'color' => 'Black', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 22],
                    ['size' => '34', 'color' => 'Black', 'price' => 1599, 'cost_price' => 750,  'stock_quantity' => 10],
                ],
            ],
            [
                'name' => 'Graphic Tee',
                'category_id' => $men, 'brand_id' => $adidas,
                'sku' => 'ADI-GT-M01', 'description' => 'Streetwear graphic tee with Adidas branding.',
                'variants' => [
                    ['size' => 'S',  'color' => 'White', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 25],
                    ['size' => 'M',  'color' => 'White', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 30],
                    ['size' => 'L',  'color' => 'White', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 28],
                    ['size' => 'XL', 'color' => 'White', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 20],
                    ['size' => 'S',  'color' => 'Black', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 22],
                    ['size' => 'M',  'color' => 'Black', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 28],
                    ['size' => 'L',  'color' => 'Black', 'price' => 699,  'cost_price' => 280,  'stock_quantity' => 24],
                    ['size' => 'M',  'color' => 'Gray',  'price' => 699,  'cost_price' => 280,  'stock_quantity' => 18],
                    ['size' => 'L',  'color' => 'Gray',  'price' => 699,  'cost_price' => 280,  'stock_quantity' => 15],
                ],
            ],
            [
                'name' => 'Denim Jacket',
                'category_id' => $men, 'brand_id' => $levis,
                'sku' => 'LEV-DJ-M01', 'description' => 'Timeless denim jacket, a wardrobe staple.',
                'variants' => [
                    ['size' => 'S',  'color' => 'Blue',  'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 8],
                    ['size' => 'M',  'color' => 'Blue',  'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 12],
                    ['size' => 'L',  'color' => 'Blue',  'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 10],
                    ['size' => 'XL', 'color' => 'Blue',  'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 6],
                    ['size' => 'S',  'color' => 'Black', 'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 7],
                    ['size' => 'M',  'color' => 'Black', 'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 10],
                    ['size' => 'L',  'color' => 'Black', 'price' => 2299, 'cost_price' => 1100, 'stock_quantity' => 8],
                ],
            ],
            [
                'name' => 'Jogger Pants',
                'category_id' => $men, 'brand_id' => $nike,
                'sku' => 'NK-JP-M01', 'description' => 'Comfortable Nike jogger pants for training or casual wear.',
                'variants' => [
                    ['size' => 'S',  'color' => 'Black', 'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 20],
                    ['size' => 'M',  'color' => 'Black', 'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 25],
                    ['size' => 'L',  'color' => 'Black', 'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 22],
                    ['size' => 'XL', 'color' => 'Black', 'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 15],
                    ['size' => 'S',  'color' => 'Gray',  'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 18],
                    ['size' => 'M',  'color' => 'Gray',  'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 22],
                    ['size' => 'L',  'color' => 'Gray',  'price' => 1299, 'cost_price' => 580,  'stock_quantity' => 18],
                ],
            ],

            // ── KIDS ───────────────────────────────────────────────────
            [
                'name' => 'Kids Graphic T-Shirt',
                'category_id' => $kids, 'brand_id' => $hm,
                'sku' => 'HM-KGT-K01', 'description' => 'Fun and colorful graphic tee for kids.',
                'variants' => [
                    ['size' => '2Y', 'color' => 'White', 'price' => 299,  'cost_price' => 120,  'stock_quantity' => 15],
                    ['size' => '4Y', 'color' => 'White', 'price' => 299,  'cost_price' => 120,  'stock_quantity' => 20],
                    ['size' => '6Y', 'color' => 'White', 'price' => 299,  'cost_price' => 120,  'stock_quantity' => 18],
                    ['size' => '2Y', 'color' => 'Blue',  'price' => 299,  'cost_price' => 120,  'stock_quantity' => 14],
                    ['size' => '4Y', 'color' => 'Blue',  'price' => 299,  'cost_price' => 120,  'stock_quantity' => 18],
                    ['size' => '6Y', 'color' => 'Blue',  'price' => 299,  'cost_price' => 120,  'stock_quantity' => 16],
                    ['size' => '4Y', 'color' => 'Pink',  'price' => 299,  'cost_price' => 120,  'stock_quantity' => 20],
                    ['size' => '6Y', 'color' => 'Pink',  'price' => 299,  'cost_price' => 120,  'stock_quantity' => 15],
                ],
            ],
            [
                'name' => 'Kids Denim Shorts',
                'category_id' => $kids, 'brand_id' => $levis,
                'sku' => 'LEV-KDS-K01', 'description' => 'Durable denim shorts built for active kids.',
                'variants' => [
                    ['size' => '4Y', 'color' => 'Blue',  'price' => 599,  'cost_price' => 280,  'stock_quantity' => 12],
                    ['size' => '6Y', 'color' => 'Blue',  'price' => 599,  'cost_price' => 280,  'stock_quantity' => 16],
                    ['size' => '8Y', 'color' => 'Blue',  'price' => 599,  'cost_price' => 280,  'stock_quantity' => 14],
                    ['size' => '4Y', 'color' => 'Black', 'price' => 599,  'cost_price' => 280,  'stock_quantity' => 10],
                    ['size' => '6Y', 'color' => 'Black', 'price' => 599,  'cost_price' => 280,  'stock_quantity' => 12],
                    ['size' => '8Y', 'color' => 'Black', 'price' => 599,  'cost_price' => 280,  'stock_quantity' => 10],
                ],
            ],
            [
                'name' => 'Kids Hoodie',
                'category_id' => $kids, 'brand_id' => $nike,
                'sku' => 'NK-KH-K01', 'description' => 'Warm and soft Nike hoodie for kids.',
                'variants' => [
                    ['size' => '4Y', 'color' => 'Gray',  'price' => 799,  'cost_price' => 360,  'stock_quantity' => 10],
                    ['size' => '6Y', 'color' => 'Gray',  'price' => 799,  'cost_price' => 360,  'stock_quantity' => 14],
                    ['size' => '8Y', 'color' => 'Gray',  'price' => 799,  'cost_price' => 360,  'stock_quantity' => 12],
                    ['size' => '4Y', 'color' => 'Black', 'price' => 799,  'cost_price' => 360,  'stock_quantity' => 8],
                    ['size' => '6Y', 'color' => 'Black', 'price' => 799,  'cost_price' => 360,  'stock_quantity' => 12],
                    ['size' => '8Y', 'color' => 'Black', 'price' => 799,  'cost_price' => 360,  'stock_quantity' => 10],
                ],
            ],
        ];

        foreach ($products as $data) {
            $variants = $data['variants'];
            unset($data['variants']);

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, ['is_active' => true])
            );

            foreach ($variants as $v) {
                ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'size'       => $v['size'],
                        'color'      => $v['color'],
                    ],
                    array_merge($v, [
                        'sku'       => $data['sku'] . '-' . $v['size'] . '-' . strtoupper(substr($v['color'], 0, 3)),
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
