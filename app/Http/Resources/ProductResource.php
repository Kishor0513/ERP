<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku_prefix' => $this->sku_prefix,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_customizable' => $this->is_customizable,
            'is_active' => $this->is_active,
            'moq' => $this->moq,
            'lead_time_days' => $this->lead_time_days,
            'weight_grams' => $this->weight_grams,
            'hs_code' => $this->hs_code,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'boms' => ProductBomResource::collection($this->whenLoaded('boms')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
