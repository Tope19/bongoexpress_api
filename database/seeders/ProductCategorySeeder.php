<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vegetables',
                'description' => 'Fresh and dried vegetables',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/766/766215.png', // leafy vegetables
            ],
            [
                'name' => 'Livestock',
                'description' => 'Meat and animal products',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/819/819803.png', // meat icon
            ],
            [
                'name' => 'Seafood',
                'description' => 'Dry and fresh seafood',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/3081/3081559.png', // fish icon
            ],
            [
                'name' => 'Spices',
                'description' => 'Local and traditional spices',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/2909/2909763.png', // spices icon
            ],
            [
                'name' => 'Oils & Condiments',
                'description' => 'Palm oil, groundnut oil, and other condiments',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/1147/1147460.png', // cooking oil icon
            ],
            [
                'name' => 'Grains & Others',
                'description' => 'Beans, tomatoes, etc.',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/2909/2909806.png', // grains icon
            ],
            [
                'name' => 'Cooking Additives',
                'description' => 'Palm fruit and special soup spices',
                'icon' => 'https://cdn-icons-png.flaticon.com/512/2917/2917999.png', // soup/spice bowl
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
