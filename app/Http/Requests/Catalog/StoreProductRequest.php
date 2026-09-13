<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku_prefix' => 'nullable|string|max:50|unique:products,sku_prefix',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_customizable' => 'boolean',
            'is_active' => 'boolean',
            'moq' => 'nullable|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:0',
            'weight_grams' => 'nullable|numeric|min:0',
            'hs_code' => 'nullable|string|max:20',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:50',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'nullable|integer|min:0',
            'variants.*.weight_grams' => 'nullable|numeric|min:0',
            'variants.*.is_active' => 'boolean',
            'variants.*.attribute_values' => 'nullable|array',
            'variants.*.color_chart_entry_id' => 'nullable|exists:color_chart_entries,id',
        ];
    }
}
