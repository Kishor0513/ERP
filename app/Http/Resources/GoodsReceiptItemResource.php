<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReceiptItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grn_id' => $this->grn_id,
            'purchase_order_item_id' => $this->purchase_order_item_id,
            'raw_material_id' => $this->raw_material_id,
            'qty_received' => $this->qty_received,
            'unit_cost' => $this->unit_cost,
            'raw_material' => new RawMaterialResource($this->whenLoaded('rawMaterial')),
        ];
    }
}
