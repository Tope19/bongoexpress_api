<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        \DB::table('price_settings')->insert([
            'base_fare' => 100.00, // Base fare in your currency
            'price_per_km' => 20.00, // Price per km
            'price_per_kg' => 10.00, // Price per kg
            'min_price' => 150.00, // Minimum price
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
