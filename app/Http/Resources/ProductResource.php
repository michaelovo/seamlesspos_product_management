<?php

namespace App\Http\Resources;

use App\Http\Resources\ProductCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'price' => $this->price,
            'quantity' => $this->quantity,
            'status' => $this->status->name,
            'created_by' => $this->createdBy->last_name . ' ' . $this->createdBy->first_name,
            'categories' => (ProductCategoryResource::collection($this->whenLoaded('productCategory'))),
        ];
    }
}
