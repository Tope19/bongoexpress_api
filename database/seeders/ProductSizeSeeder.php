<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = ['100g', '250g', '500g', '1kg', '5kg', 'Bottle', 'Bag'];

        foreach (Product::all() as $product) {
            $randomSizes = collect($sizes)->random(rand(1, 3));
            foreach ($randomSizes as $size) {
                ProductSize::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'price' => rand(500, 5000),
                    'stock_quantity' => rand(10, 100),
                ]);
            }
        }
    }
}
