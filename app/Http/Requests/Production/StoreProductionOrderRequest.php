<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'qty_ordered' => 'required|integer|min:1',
            'due_date' => 'required|date|after:now',
            'is_custom' => 'boolean',
            'custom_spec_url' => 'nullable|url|max:500',
            'custom_notes' => 'nullable|string',
        ];
    }
}
