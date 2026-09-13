<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\BaseController;
use App\Http\Resources\RawMaterialResource;
use App\Models\RawMaterial;
use App\Services\Inventory\RawMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RawMaterialController extends BaseController
{
    public function __construct(
        private RawMaterialService $rawMaterialService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $materials = $this->rawMaterialService->getAll($request->all());

        return $this->sendPaginated($materials);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:raw_materials,sku',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|integer|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
        ]);

        $material = $this->rawMaterialService->create($validated);

        return $this->sendResponse(
            new RawMaterialResource($material),
            'Raw material created successfully',
            201
        );
    }

    public function show(RawMaterial $rawMaterial): JsonResponse
    {
        $material = $this->rawMaterialService->getById($rawMaterial->id);

        return $this->sendResponse(new RawMaterialResource($material));
    }

    public function update(Request $request, RawMaterial $rawMaterial): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|required|string|max:50|unique:raw_materials,sku,'.$rawMaterial->id,
            'unit' => 'sometimes|required|string|max:20',
            'description' => 'nullable|string',
            'reorder_point' => 'nullable|integer|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'cost_per_unit' => 'nullable|numeric|min:0',
        ]);

        $material = $this->rawMaterialService->update($rawMaterial, $validated);

        return $this->sendResponse(
            new RawMaterialResource($material),
            'Raw material updated successfully'
        );
    }

    public function destroy(RawMaterial $rawMaterial): JsonResponse
    {
        $this->rawMaterialService->delete($rawMaterial);

        return $this->sendResponse([], 'Raw material deleted successfully');
    }

    public function belowReorderPoint(): JsonResponse
    {
        $materials = $this->rawMaterialService->getBelowReorderPoint();

        return $this->sendResponse(RawMaterialResource::collection($materials));
    }

    public function lowStock()
    {
        return $this->belowReorderPoint();
    }
}
