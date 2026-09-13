<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtisanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'location' => $this->location,
            'phone' => $this->phone,
            'skills' => $this->skills,
            'certification_status' => $this->certification_status,
            'payout_method' => $this->payout_method,
            'join_date' => $this->join_date?->toISOString(),
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'production_assignments_count' => $this->whenCounted('productionAssignments'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
