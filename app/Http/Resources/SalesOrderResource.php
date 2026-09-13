<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'channel' => $this->channel,
            'wholesale_account_id' => $this->wholesale_account_id,
            'lead_id' => $this->lead_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'shipping_cost' => $this->shipping_cost,
            'total' => $this->total,
            'po_number' => $this->po_number,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'wholesale_account' => new WholesaleAccountResource($this->whenLoaded('wholesaleAccount')),
            'lead' => new LeadResource($this->whenLoaded('lead')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'production_orders' => ProductionOrderResource::collection($this->whenLoaded('productionOrders')),
            'shipments' => ShipmentResource::collection($this->whenLoaded('shipments')),
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
