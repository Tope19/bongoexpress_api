<?php

namespace App\Http\Resources\Logistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DropoffLocationResource extends JsonResource
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
            'logistic_order_id' => new LogisticOrderResource($this->whenLoaded('order')),
            'recipient_name' => $this->recipient_name,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_from_pickup' => $this->distance_from_pickup,
            'price' => $this->price,
            'notes' => $this->notes,
            // 'status' => $this->status,
            'sequence' => $this->sequence,
        ];
    }
}
