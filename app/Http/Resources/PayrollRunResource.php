<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_start' => $this->period_start?->toISOString(),
            'period_end' => $this->period_end?->toISOString(),
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'approved_by' => $this->approved_by,
            'paid_at' => $this->paid_at?->toISOString(),
            'items' => PayrollItemResource::collection($this->whenLoaded('items')),
            'approver' => new UserResource($this->whenLoaded('approver')),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
