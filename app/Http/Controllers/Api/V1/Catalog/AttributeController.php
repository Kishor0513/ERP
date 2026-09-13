<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\BaseController;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Attribute::with('values');

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        return $this->sendResponse($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'values' => 'sometimes|array',
            'values.*' => 'string|max:255',
        ]);

        $attribute = Attribute::create(['name' => $validated['name']]);

        if (! empty($validated['values'])) {
            foreach ($validated['values'] as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        return $this->sendResponse($attribute->load('values'), 'Attribute created', 201);
    }

    public function show(Attribute $attribute): JsonResponse
    {
        return $this->sendResponse($attribute->load('values'));
    }

    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,'.$attribute->id,
        ]);

        $attribute->update($validated);

        return $this->sendResponse($attribute->load('values'));
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        $attribute->values()->delete();
        $attribute->delete();

        return $this->sendResponse([], 'Attribute deleted');
    }
}
