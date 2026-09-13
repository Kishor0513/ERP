<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
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
            'valid_until' => 'nullable|date|after:now',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.custom_description' => 'nullable|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }
}
