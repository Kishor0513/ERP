<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_start' => 'sometimes|required|date',
            'period_end' => 'sometimes|required|date|after_or_equal:period_start',
        ];
    }
}
