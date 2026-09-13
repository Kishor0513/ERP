<?php

namespace App\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'required|exists:sales_orders,id',
            'carrier' => 'nullable|string|max:100',
            'tracking_no' => 'nullable|string|max:100',
            'incoterm' => 'nullable|string|max:10',
            'hs_code' => 'nullable|string|max:20',
            'declared_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'estimated_arrival' => 'nullable|date|after:now',
            'certificate_of_origin' => 'boolean',
            'fair_trade_doc' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }
}
