<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // ProductsSeeder::class,
            // PackageTypeSeeder::class,
            PriceSettingsSeeder::class,
        ]);


    }
}
