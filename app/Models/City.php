<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state_id',
        'is_active',
        'delivery_available',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delivery_available' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public static function getCitiesByState($stateId)
    {
        return self::where('state_id', $stateId)
                   ->where('delivery_available', true)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get();
    }
}
