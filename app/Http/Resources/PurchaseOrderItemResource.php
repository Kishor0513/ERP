<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'raw_material_id' => $this->raw_material_id,
            'qty_ordered' => $this->qty_ordered,
            'qty_received' => $this->qty_received,
            'unit_cost' => $this->unit_cost,
            'total' => $this->total,
            'raw_material' => new RawMaterialResource($this->whenLoaded('rawMaterial')),
        ];
    }
}
