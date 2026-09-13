<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ArtisanCollection extends ResourceCollection
{
    public $collects = ArtisanResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
