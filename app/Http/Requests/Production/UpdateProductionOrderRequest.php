<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'product_variant_id' => 'sometimes|required|exists:product_variants,id',
            'qty_ordered' => 'sometimes|required|integer|min:1',
            'due_date' => 'sometimes|required|date',
            'is_custom' => 'boolean',
            'custom_spec_url' => 'nullable|url|max:500',
            'custom_notes' => 'nullable|string',
        ];
    }
}
