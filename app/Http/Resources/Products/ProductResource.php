<?php

namespace App\Http\Resources\Products;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => (int) $this->id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            "user" => new UserResource($this->whenLoaded('user')),
            "name" => $this->name,
            "description" => $this->description,
            "sku" => $this->sku,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'sizes' => SizeResource::collection($this->whenLoaded('sizes')),
        ];
    }


}
