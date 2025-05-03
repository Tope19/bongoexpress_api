<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // DB::table('product_categories')->truncate();
        // DB::table('products')->truncate();
        // DB::table('product_sizes')->truncate();
        // DB::table('product_images')->truncate();

        $categories = [
            [
                'name' => 'Vegetables',
                'description' => 'Fresh and organic vegetables.',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/766/766215.png', // farm-related icon
            ],
            [
                'name' => 'Fruits',
                'description' => 'Seasonal fruits full of nutrients.',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/415/415682.png',
            ],
            [
                'name' => 'Dairy Products',
                'description' => 'Pure dairy from our farm.',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/1046/1046793.png',
            ],
        ];

        foreach ($categories as $cat) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'name' => $cat['name'],
                'description' => $cat['description'],
                'icon' => $cat['icon'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create 2 products per category
            for ($i = 1; $i <= 2; $i++) {
                $productName = "{$cat['name']} Product $i";
                $productId = DB::table('products')->insertGetId([
                    'category_id' => $categoryId,
                    'name' => $productName,
                    'description' => "This is a sample $productName description.",
                    'sku' => strtoupper(Str::random(8)),
                    'barcode' => strtoupper(Str::random(12)),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Sizes for each product
                $sizes = [
                    ['size' => 'Small', 'price' => rand(10, 30)],
                    ['size' => 'Medium', 'price' => rand(31, 60)],
                    ['size' => 'Large', 'price' => rand(61, 100)],
                ];

                foreach ($sizes as $s) {
                    DB::table('product_sizes')->insert([
                        'product_id' => $productId,
                        'size' => $s['size'],
                        'price' => $s['price'],
                        'stock_quantity' => rand(10, 100),
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Sample image URLs (replace with your own or host)
                $sampleImages = [
                    'https://images.pexels.com/photos/533360/pexels-photo-533360.jpeg', // Vegetables
                    'https://images.pexels.com/photos/102104/pexels-photo-102104.jpeg', // Fruits
                    'https://images.pexels.com/photos/533360/pexels-photo-533360.jpeg', // Dairy
                ];

                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image_path' => $sampleImages[array_rand($sampleImages)],
                    'is_primary' => true,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
