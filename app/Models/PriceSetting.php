<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_fare',
        'price_per_km',
        'price_per_kg',
        'min_price',
    ];

    public static function getSettings()
    {
        return self::first();
    }
}
