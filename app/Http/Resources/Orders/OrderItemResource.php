<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use App\Http\Resources\Products\SizeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product' => new SizeResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'created_at' => formatDate($this->created_at),
        ];
    }
}
