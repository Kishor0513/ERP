<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|required|string|max:500',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'incurred_at' => 'sometimes|required|date',
            'receipt_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
}
