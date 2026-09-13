<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'supplier_id' => $this->supplier_id,
            'status' => $this->status,
            'expected_date' => $this->expected_date?->toISOString(),
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'notes' => $this->notes,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'goods_receipt_notes' => GoodsReceiptNoteResource::collection($this->whenLoaded('goodsReceiptNotes')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
