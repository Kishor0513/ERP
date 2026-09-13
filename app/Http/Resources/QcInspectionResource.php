<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QcInspectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'production_order_id' => $this->production_order_id,
            'artisan_id' => $this->artisan_id,
            'inspector_id' => $this->inspector_id,
            'result' => $this->result,
            'defect_reason' => $this->defect_reason,
            'defect_details' => $this->defect_details,
            'notes' => $this->notes,
            'inspected_at' => $this->inspected_at?->toISOString(),
            'production_order' => new ProductionOrderResource($this->whenLoaded('productionOrder')),
            'artisan' => new ArtisanResource($this->whenLoaded('artisan')),
            'inspector' => new UserResource($this->whenLoaded('inspector')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
