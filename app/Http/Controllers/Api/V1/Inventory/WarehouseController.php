<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\BaseController;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends BaseController
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return $this->sendResponse($warehouses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'type' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated);

        return $this->sendResponse($warehouse, 'Warehouse created successfully', 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return $this->sendResponse($warehouse);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:warehouses,name,'.$warehouse->id,
            'type' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return $this->sendResponse($warehouse, 'Warehouse updated successfully');
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if ($warehouse->stockMovements()->exists()) {
            return $this->sendError('Cannot delete warehouse with stock movements.', [], 422);
        }

        $warehouse->delete();

        return $this->sendResponse([], 'Warehouse deleted successfully');
    }
}
