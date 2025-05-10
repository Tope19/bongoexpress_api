<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticPayment extends Model
{
    protected $fillable = [
        'logistic_order_id',
        'reference',
        'amount',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(LogisticOrder::class, 'logistic_order_id');
    }
}
