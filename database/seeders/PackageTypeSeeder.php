<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packageTypes = [
            [
                'name' => 'Shoes',
                'description' => 'Footwear of all kinds',
                'price_multiplier' => 1.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'price_multiplier' => 1.5, // Higher price due to fragility
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Documents',
                'description' => 'Papers and documents',
                'price_multiplier' => 0.8, // Lower price due to lightweight
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Food',
                'description' => 'Perishable food items',
                'price_multiplier' => 1.2, // Higher due to urgent delivery needs
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('package_types')->insert($packageTypes);
    }
}
