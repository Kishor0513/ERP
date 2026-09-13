<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReceiptNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grn_number' => $this->grn_number,
            'purchase_order_id' => $this->purchase_order_id,
            'received_by' => $this->received_by,
            'received_at' => $this->received_at?->toISOString(),
            'notes' => $this->notes,
            'receiver' => new UserResource($this->whenLoaded('receiver')),
            'items' => GoodsReceiptItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
