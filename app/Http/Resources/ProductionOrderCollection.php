<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductionOrderCollection extends ResourceCollection
{
    public $collects = ProductionOrderResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
