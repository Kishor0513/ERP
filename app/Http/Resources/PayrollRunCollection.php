<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayrollRunCollection extends ResourceCollection
{
    public $collects = PayrollRunResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }
}
