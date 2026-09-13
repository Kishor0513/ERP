<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionOrderAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'production_order_id' => $this->production_order_id,
            'artisan_id' => $this->artisan_id,
            'qty_assigned' => $this->qty_assigned,
            'qty_completed' => $this->qty_completed,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'artisan' => new ArtisanResource($this->whenLoaded('artisan')),
        ];
    }
}
