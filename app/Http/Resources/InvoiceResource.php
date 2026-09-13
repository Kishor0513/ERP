<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'sales_order_id' => $this->sales_order_id,
            'wholesale_account_id' => $this->wholesale_account_id,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'currency' => $this->currency,
            'due_date' => $this->due_date?->toISOString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'wholesale_account' => new WholesaleAccountResource($this->whenLoaded('wholesaleAccount')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'total_paid' => $this->whenLoaded('payments', fn () => $this->payments->sum('amount')),
            'balance_due' => $this->whenLoaded('payments', fn () => $this->total - $this->payments->sum('amount')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
