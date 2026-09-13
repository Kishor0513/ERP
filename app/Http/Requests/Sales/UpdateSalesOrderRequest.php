<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => 'sometimes|required|string|in:wholesale,retail,online,marketplace',
            'wholesale_account_id' => 'nullable|exists:wholesale_accounts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'currency' => 'nullable|string|max:3',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_variant_id' => 'required_with:items|exists:product_variants,id',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }
}
