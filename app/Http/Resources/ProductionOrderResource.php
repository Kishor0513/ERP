<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'production_order_number' => $this->production_order_number,
            'sales_order_id' => $this->sales_order_id,
            'product_variant_id' => $this->product_variant_id,
            'qty_ordered' => $this->qty_ordered,
            'qty_completed' => $this->qty_completed,
            'status' => $this->status,
            'due_date' => $this->due_date?->toISOString(),
            'is_custom' => $this->is_custom,
            'custom_spec_url' => $this->custom_spec_url,
            'custom_notes' => $this->custom_notes,
            'is_overdue' => $this->due_date && $this->status !== 'completed' && $this->due_date->isPast(),
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'assignments' => ProductionOrderAssignmentResource::collection($this->whenLoaded('assignments')),
            'qc_inspections' => QcInspectionResource::collection($this->whenLoaded('qcInspections')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
