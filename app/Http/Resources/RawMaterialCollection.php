<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RawMaterialCollection extends ResourceCollection
{
    public $collects = RawMaterialResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
