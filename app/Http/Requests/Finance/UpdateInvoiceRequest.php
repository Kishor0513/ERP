<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'sometimes|required|exists:sales_orders,id',
            'wholesale_account_id' => 'nullable|exists:wholesale_accounts,id',
            'subtotal' => 'sometimes|required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'due_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ];
    }
}
