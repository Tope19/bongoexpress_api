<?php

namespace App\Http\Resources\Logistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogisticOrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'pickup_location_id' => new PickupLocationResource($this->whenLoaded('pickupLocation')),
            'package_type_id' => new PackageTypeResource($this->whenLoaded('packageType')),
            'dropoff_locations' => DropoffLocationResource::collection($this->whenLoaded('dropoffs')),
            'weight' => $this->weight,
            'total_distance' => $this->total_distance,
            'total_price' => $this->total_price,
            'notes_for_rider' => $this->notes_for_rider,
            '_state' => $this->_state,
            'status' => $this->status,
        ];
    }
}
