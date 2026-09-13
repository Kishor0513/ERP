<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'description' => $this->description,
            'reorder_point' => $this->reorder_point,
            'current_stock' => $this->current_stock,
            'cost_per_unit' => $this->cost_per_unit,
            'batches' => RawMaterialBatchResource::collection($this->whenLoaded('batches')),
            'is_below_reorder' => $this->current_stock <= $this->reorder_point,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
