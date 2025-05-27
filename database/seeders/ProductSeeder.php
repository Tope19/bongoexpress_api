<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Ogbolo', 'category' => 'Spices'],
            ['name' => 'Egusi', 'category' => 'Spices'],
            ['name' => 'Cray fish', 'category' => 'Seafood'],
            ['name' => 'Prawn', 'category' => 'Seafood'],
            ['name' => 'Stock fish', 'category' => 'Seafood'],
            ['name' => 'Dry fish', 'category' => 'Seafood'],
            ['name' => 'Dry ponmo', 'category' => 'Livestock'],
            ['name' => 'Dry meat - goat', 'category' => 'Livestock'],
            ['name' => 'Dry meat - cow', 'category' => 'Livestock'],
            ['name' => 'Dry meat - tiko', 'category' => 'Livestock'],
            ['name' => 'Red oil', 'category' => 'Oils & Condiments'],
            ['name' => 'Groundnut oil', 'category' => 'Oils & Condiments'],
            ['name' => 'Dry pepper', 'category' => 'Spices'],
            ['name' => 'Grounded pepper', 'category' => 'Spices'],
            ['name' => 'Ugu', 'category' => 'Vegetables'],
            ['name' => 'Bitter leaves', 'category' => 'Vegetables'],
            ['name' => 'Scent pepper (dry)', 'category' => 'Spices'],
            ['name' => 'Tin tomatoes', 'category' => 'Grains & Others'],
            ['name' => 'Lucy\'s bean', 'category' => 'Grains & Others'],
            ['name' => 'Palm fruit (banga)', 'category' => 'Cooking Additives'],
            ['name' => 'Utazi leaves', 'category' => 'Vegetables'],
            ['name' => 'Uziza seeds', 'category' => 'Spices'],
            ['name' => 'Banga spice', 'category' => 'Cooking Additives'],
            ['name' => 'Periwinkle', 'category' => 'Seafood'],
            ['name' => 'Garlic', 'category' => 'Spices'],
            ['name' => 'Ginger', 'category' => 'Spices'],
            ['name' => 'Scent leaf', 'category' => 'Vegetables'],
        ];

        foreach ($products as $item) {
            $category = ProductCategory::where('name', $item['category'])->first();
            Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'sku' => strtoupper(\Str::slug($item['name'])) . '-' . rand(1000, 9999),
                'barcode' => rand(100000000000, 999999999999),
                'description' => $item['name'] . ' - Premium quality for African meals.',
            ]);
        }
    }
}
