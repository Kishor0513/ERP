<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_run_id' => $this->payroll_run_id,
            'artisan_id' => $this->artisan_id,
            'staff_user_id' => $this->staff_user_id,
            'gross_amount' => $this->gross_amount,
            'deductions' => $this->deductions,
            'net_amount' => $this->net_amount,
            'units_completed' => $this->units_completed,
            'period_notes' => $this->period_notes,
            'artisan' => new ArtisanResource($this->whenLoaded('artisan')),
            'staff_user' => new UserResource($this->whenLoaded('staffUser')),
        ];
    }
}
