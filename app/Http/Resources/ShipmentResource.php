<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shipment_number' => $this->shipment_number,
            'sales_order_id' => $this->sales_order_id,
            'carrier' => $this->carrier,
            'tracking_no' => $this->tracking_no,
            'incoterm' => $this->incoterm,
            'hs_code' => $this->hs_code,
            'declared_value' => $this->declared_value,
            'currency' => $this->currency,
            'status' => $this->status,
            'estimated_arrival' => $this->estimated_arrival?->toISOString(),
            'actual_arrival' => $this->actual_arrival?->toISOString(),
            'certificate_of_origin' => $this->certificate_of_origin,
            'fair_trade_doc' => $this->fair_trade_doc,
            'notes' => $this->notes,
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
