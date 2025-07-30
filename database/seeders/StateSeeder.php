<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            [
                'name' => 'Lagos',
                'code' => 'LA',
                'is_active' => true,
                'delivery_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Edo',
                'code' => 'ED',
                'is_active' => true,
                'delivery_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Federal Capital Territory',
                'code' => 'FC',
                'is_active' => true,
                'delivery_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more states as needed
            [
                'name' => 'Ogun',
                'code' => 'OG',
                'is_active' => true,
                'delivery_available' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Oyo',
                'code' => 'OY',
                'is_active' => true,
                'delivery_available' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('states')->insert($states);
    }
}
