<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_multiplier',
        'is_active',
    ];

    public function orders()
    {
        return $this->hasMany(LogisticOrder::class, 'package_type_id');
    }
}
