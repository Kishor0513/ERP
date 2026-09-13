<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'incurred_at' => 'required|date',
            'receipt_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
}
