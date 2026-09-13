<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesaleAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'business_type' => $this->business_type,
            'website' => $this->website,
            'expected_monthly_volume' => $this->expected_monthly_volume,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'payment_terms' => $this->payment_terms,
            'credit_limit' => $this->credit_limit,
            'notes' => $this->notes,
            'approver' => new UserResource($this->whenLoaded('approver')),
            'documents' => WholesaleAccountDocResource::collection($this->whenLoaded('documents')),
            'sales_orders_count' => $this->whenCounted('salesOrders'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
