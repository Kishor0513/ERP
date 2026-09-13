<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtisanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'certification_status' => 'nullable|string|in:pending,certified,expired',
            'bank_details' => 'nullable|array',
            'payout_method' => 'nullable|string|in:bank_transfer,cash,upi',
            'join_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,blocked',
        ];
    }
}
