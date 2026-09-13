<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_number' => $this->quote_number,
            'wholesale_account_id' => $this->wholesale_account_id,
            'lead_id' => $this->lead_id,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'currency' => $this->currency,
            'valid_until' => $this->valid_until?->toISOString(),
            'is_expired' => $this->valid_until ? $this->valid_until->isPast() : false,
            'notes' => $this->notes,
            'wholesale_account' => new WholesaleAccountResource($this->whenLoaded('wholesaleAccount')),
            'lead' => new LeadResource($this->whenLoaded('lead')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
