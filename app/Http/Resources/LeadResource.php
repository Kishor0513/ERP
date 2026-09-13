<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'notes' => $this->notes,
            'assigned_to' => $this->assigned_to,
            'converted_to_account_id' => $this->converted_to_account_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'wholesale_account' => new WholesaleAccountResource($this->whenLoaded('wholesaleAccount')),
            'quotes_count' => $this->whenCounted('quotes'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
