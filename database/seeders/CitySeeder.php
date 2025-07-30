<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Lagos State Cities
            [
                'name' => 'Victoria Island',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.4281,
                'longitude' => 3.4219,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ikoyi',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.6018,
                'longitude' => 3.3515,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lekki',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.4654,
                'longitude' => 3.5658,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ikeja',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.6018,
                'longitude' => 3.3515,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Surulere',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.5018,
                'longitude' => 3.3584,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Yaba',
                'state_id' => 1, // Lagos
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.6018,
                'longitude' => 3.3515,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Edo State Cities (Benin)
            [
                'name' => 'Benin City',
                'state_id' => 2, // Edo
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 6.3176,
                'longitude' => 5.6145,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // FCT Cities (Abuja)
            [
                'name' => 'Abuja',
                'state_id' => 3, // FCT
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 9.0820,
                'longitude' => 8.6753,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gwagwalada',
                'state_id' => 3, // FCT
                'is_active' => true,
                'delivery_available' => true,
                'latitude' => 8.9434,
                'longitude' => 7.0826,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('cities')->insert($cities);
    }
}
