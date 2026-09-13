<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Http\Controllers\BaseController;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return $this->sendPaginated($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return $this->sendResponse(
            new SupplierResource($supplier),
            'Supplier created successfully',
            201
        );
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return $this->sendResponse(new SupplierResource($supplier->load(['rawMaterialBatches.rawMaterial', 'purchaseOrders'])));
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return $this->sendResponse(
            new SupplierResource($supplier),
            'Supplier updated successfully'
        );
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return $this->sendError('Cannot delete supplier with existing purchase orders.', [], 422);
        }

        $supplier->delete();

        return $this->sendResponse([], 'Supplier deleted successfully');
    }
}
