<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // ProductsSeeder::class,
            UserSeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            PackageTypeSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ProductSizeSeeder::class,
            ProductImageSeeder::class,
            PriceSettingsSeeder::class,
            DeliveryZoneSeeder::class,
        ]);


    }
}
