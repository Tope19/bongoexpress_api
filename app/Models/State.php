<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'delivery_available',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_available' => 'boolean',
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public static function getAvailableStates()
    {
        return self::where('delivery_available', true)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get();
    }
}
