<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'raw_material_id' => $this->raw_material_id,
            'supplier_id' => $this->supplier_id,
            'batch_no' => $this->batch_no,
            'qty_received' => $this->qty_received,
            'qty_remaining' => $this->qty_remaining,
            'unit_cost' => $this->unit_cost,
            'received_at' => $this->received_at?->toISOString(),
            'expiry_date' => $this->expiry_date?->toISOString(),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
        ];
    }
}
