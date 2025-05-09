<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'latitude',
        'longitude',
        'user_id',
        'is_favorite',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pickupOrders()
    {
        return $this->hasMany(LogisticOrder::class, 'pickup_location_id');
    }
}
