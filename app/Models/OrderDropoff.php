<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDropoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistic_order_id',
        'recipient_name',
        'address',
        'phone_number',
        'latitude',
        'longitude',
        'distance_from_pickup',
        'price',
        'notes',
        'status',
        'sequence',
    ];

    public function order()
    {
        return $this->belongsTo(LogisticOrder::class);
    }
}
