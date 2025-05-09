<?php

namespace App\Http\Resources\Logistics;

use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupLocationResource extends JsonResource
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
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'user' => new UserResource($this->whenLoaded('user')),
            'is_favorite' => boolval($this->is_favorite),
        ];
    }
}
