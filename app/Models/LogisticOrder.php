<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LogisticOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'pickup_location_id',
        'package_type_id',
        'weight',
        'total_distance',
        'total_price',
        'notes_for_rider',
        '_state',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'Package no(' . random_int(1000000, 9999999) . ')';
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packageType()
    {
        return $this->belongsTo(PackageType::class);
    }

    public function pickupLocation()
    {
        return $this->belongsTo(PickupLocation::class, 'pickup_location_id');
    }

    public function dropoffs()
    {
        return $this->hasMany(OrderDropoff::class);
    }
}
