<?php

namespace App\Http\Resources\Logistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_active' => boolval($this->is_active),
            'pickup_bounds' => [
                'latitude_min' => $this->pickup_latitude_min,
                'latitude_max' => $this->pickup_latitude_max,
                'longitude_min' => $this->pickup_longitude_min,
                'longitude_max' => $this->pickup_longitude_max,
            ],
            'dropoff_bounds' => [
                'latitude_min' => $this->dropoff_latitude_min,
                'latitude_max' => $this->dropoff_latitude_max,
                'longitude_min' => $this->dropoff_longitude_min,
                'longitude_max' => $this->dropoff_longitude_max,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
