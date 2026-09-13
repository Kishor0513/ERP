<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'sku' => $this->sku,
            'full_sku' => $this->full_sku,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock_quantity' => $this->stock_quantity,
            'weight_grams' => $this->weight_grams,
            'is_active' => $this->is_active,
            'attribute_values' => $this->attribute_values,
            'color_chart_entry_id' => $this->color_chart_entry_id,
            'color_chart_entry' => new ColorChartEntryResource($this->whenLoaded('colorChartEntry')),
            'price_tiers' => PriceTierResource::collection($this->whenLoaded('priceTiers')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
