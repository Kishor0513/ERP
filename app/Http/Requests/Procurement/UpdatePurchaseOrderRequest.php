<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'expected_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.raw_material_id' => 'required_with:items|exists:raw_materials,id',
            'items.*.qty_ordered' => 'required_with:items|integer|min:1',
            'items.*.unit_cost' => 'required_with:items|numeric|min:0',
        ];
    }
}
