<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_id' => $this->quote_id,
            'product_variant_id' => $this->product_variant_id,
            'custom_description' => $this->custom_description,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
        ];
    }
}
