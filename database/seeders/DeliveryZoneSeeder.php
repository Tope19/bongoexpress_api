<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Lagos Island to Island',
                'description' => 'Delivery within Lagos Island areas',
                'base_price' => 5000.00,
                'state_id' => 1, // Lagos
                'city_id' => null, // Applies to all Lagos cities
                // Lagos Island coordinates (approximate bounds)
                'pickup_latitude_min' => 6.4000,
                'pickup_latitude_max' => 6.6000,
                'pickup_longitude_min' => 3.3500,
                'pickup_longitude_max' => 3.4500,
                'dropoff_latitude_min' => 6.4000,
                'dropoff_latitude_max' => 6.6000,
                'dropoff_longitude_min' => 3.3500,
                'dropoff_longitude_max' => 3.4500,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lagos Mainland to Mainland',
                'description' => 'Delivery within Lagos Mainland areas',
                'base_price' => 4000.00,
                'state_id' => 1, // Lagos
                'city_id' => null, // Applies to all Lagos cities
                // Lagos Mainland coordinates (approximate bounds)
                'pickup_latitude_min' => 6.5000,
                'pickup_latitude_max' => 6.7000,
                'pickup_longitude_min' => 3.2000,
                'pickup_longitude_max' => 3.4000,
                'dropoff_latitude_min' => 6.5000,
                'dropoff_latitude_max' => 6.7000,
                'dropoff_longitude_min' => 3.2000,
                'dropoff_longitude_max' => 3.4000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lagos Island to Mainland',
                'description' => 'Delivery from Lagos Island to Mainland or vice versa',
                'base_price' => 10000.00,
                'state_id' => 1, // Lagos
                'city_id' => null, // Applies to all Lagos cities
                // Island to Mainland or Mainland to Island
                'pickup_latitude_min' => 6.4000,
                'pickup_latitude_max' => 6.7000,
                'pickup_longitude_min' => 3.2000,
                'pickup_longitude_max' => 3.4500,
                'dropoff_latitude_min' => 6.4000,
                'dropoff_latitude_max' => 6.7000,
                'dropoff_longitude_min' => 3.2000,
                'dropoff_longitude_max' => 3.4500,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Benin Door to Door',
                'description' => 'Door to door delivery in Benin',
                'base_price' => 15000.00,
                'state_id' => 2, // Edo
                'city_id' => 7, // Benin City
                // Benin coordinates (approximate bounds)
                'pickup_latitude_min' => 6.3000,
                'pickup_latitude_max' => 6.4000,
                'pickup_longitude_min' => 5.6000,
                'pickup_longitude_max' => 5.7000,
                'dropoff_latitude_min' => 6.3000,
                'dropoff_latitude_max' => 6.4000,
                'dropoff_longitude_min' => 5.6000,
                'dropoff_longitude_max' => 5.7000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Abuja Delivery',
                'description' => 'Delivery within Abuja',
                'base_price' => 15000.00,
                'state_id' => 3, // FCT
                'city_id' => 8, // Abuja
                // Abuja coordinates (approximate bounds)
                'pickup_latitude_min' => 8.8000,
                'pickup_latitude_max' => 9.2000,
                'pickup_longitude_min' => 7.3000,
                'pickup_longitude_max' => 7.6000,
                'dropoff_latitude_min' => 8.8000,
                'dropoff_latitude_max' => 9.2000,
                'dropoff_longitude_min' => 7.3000,
                'dropoff_longitude_max' => 7.6000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Outside Lagos',
                'description' => 'Delivery outside Lagos state',
                'base_price' => 10000.00,
                'state_id' => null, // Catch-all for other states
                'city_id' => null, // Catch-all for other cities
                // Outside Lagos (catch-all for other locations)
                'pickup_latitude_min' => 4.0000,
                'pickup_latitude_max' => 14.0000,
                'pickup_longitude_min' => 2.0000,
                'pickup_longitude_max' => 15.0000,
                'dropoff_latitude_min' => 4.0000,
                'dropoff_latitude_max' => 14.0000,
                'dropoff_longitude_min' => 2.0000,
                'dropoff_longitude_max' => 15.0000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('delivery_zones')->insert($zones);
    }
}
