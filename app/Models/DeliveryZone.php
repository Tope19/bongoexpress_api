<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'state_id',
        'city_id',
        'pickup_latitude_min',
        'pickup_latitude_max',
        'pickup_longitude_min',
        'pickup_longitude_max',
        'dropoff_latitude_min',
        'dropoff_latitude_max',
        'dropoff_longitude_min',
        'dropoff_longitude_max',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'pickup_latitude_min' => 'decimal:7',
        'pickup_latitude_max' => 'decimal:7',
        'pickup_longitude_min' => 'decimal:7',
        'pickup_longitude_max' => 'decimal:7',
        'dropoff_latitude_min' => 'decimal:7',
        'dropoff_latitude_max' => 'decimal:7',
        'dropoff_longitude_min' => 'decimal:7',
        'dropoff_longitude_max' => 'decimal:7',
    ];

    public function isLocationInZone(float $pickupLat, float $pickupLon, float $dropoffLat, float $dropoffLon): bool
    {
        // Check if pickup location is within zone bounds
        $pickupInZone = $pickupLat >= $this->pickup_latitude_min &&
                       $pickupLat <= $this->pickup_latitude_max &&
                       $pickupLon >= $this->pickup_longitude_min &&
                       $pickupLon <= $this->pickup_longitude_max;

        // Check if dropoff location is within zone bounds
        $dropoffInZone = $dropoffLat >= $this->dropoff_latitude_min &&
                        $dropoffLat <= $this->dropoff_latitude_max &&
                        $dropoffLon >= $this->dropoff_longitude_min &&
                        $dropoffLon <= $this->dropoff_longitude_max;

        return $pickupInZone && $dropoffInZone;
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public static function findZoneForDelivery(float $pickupLat, float $pickupLon, float $dropoffLat, float $dropoffLon)
    {
        return self::where('is_active', true)
            ->get()
            ->first(function ($zone) use ($pickupLat, $pickupLon, $dropoffLat, $dropoffLon) {
                return $zone->isLocationInZone($pickupLat, $pickupLon, $dropoffLat, $dropoffLon);
            });
    }

    public static function getZonesByState($stateId)
    {
        return self::where('state_id', $stateId)
                   ->where('is_active', true)
                   ->with(['state', 'city'])
                   ->get();
    }

    public static function getZonesByCity($cityId)
    {
        return self::where('city_id', $cityId)
                   ->where('is_active', true)
                   ->with(['state', 'city'])
                   ->get();
    }
}
