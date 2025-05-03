<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function getImagePathAttribute()
    {
        return storage_path('app/public/' . $this->image);
    }
}
