<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WholesaleAccountDocResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wholesale_account_id' => $this->wholesale_account_id,
            'type' => $this->type,
            'path' => $this->path,
            'original_name' => $this->original_name,
        ];
    }
}
