<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShipmentCollection extends ResourceCollection
{
    public $collects = ShipmentResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
