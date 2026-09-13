<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sales_order_id' => $this->sales_order_id,
            'product_variant_id' => $this->product_variant_id,
            'qty' => $this->qty,
            'unit_price' => $this->unit_price,
            'total' => $this->total,
            'production_order_id' => $this->production_order_id,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'production_order' => new ProductionOrderResource($this->whenLoaded('productionOrder')),
        ];
    }
}
