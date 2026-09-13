<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wholesale_account_id' => 'nullable|exists:wholesale_accounts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'currency' => 'nullable|string|max:3',
            'discount' => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_variant_id' => 'required_with:items|exists:product_variants,id',
            'items.*.custom_description' => 'nullable|string',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }
}
