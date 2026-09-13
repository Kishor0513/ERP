<?php

namespace App\Http\Controllers\Api\V1\Production;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Production\StoreProductionOrderRequest;
use App\Http\Requests\Production\UpdateProductionOrderRequest;
use App\Http\Resources\ProductionOrderResource;
use App\Http\Resources\QcInspectionResource;
use App\Models\ProductionOrder;
use App\Services\Production\ProductionOrderService;
use App\Services\Production\QcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrderController extends BaseController
{
    public function __construct(
        private ProductionOrderService $productionOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ProductionOrder::class);

        $orders = $this->productionOrderService->getAll($request->all());

        return $this->sendPaginated($orders);
    }

    public function store(StoreProductionOrderRequest $request): JsonResponse
    {
        $this->authorize('create', ProductionOrder::class);

        $order = $this->productionOrderService->create($request->validated());

        return $this->sendResponse(
            new ProductionOrderResource($order),
            'Production order created successfully',
            201
        );
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        $this->authorize('view', $productionOrder);

        $order = $this->productionOrderService->getById($productionOrder->id);

        return $this->sendResponse(new ProductionOrderResource($order));
    }

    public function update(UpdateProductionOrderRequest $request, ProductionOrder $productionOrder): JsonResponse
    {
        $this->authorize('update', $productionOrder);

        $order = $this->productionOrderService->update($productionOrder, $request->validated());

        return $this->sendResponse(
            new ProductionOrderResource($order),
            'Production order updated successfully'
        );
    }

    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        $this->authorize('delete', $productionOrder);

        $productionOrder->delete();

        return $this->sendResponse([], 'Production order deleted successfully');
    }

    public function assignArtisan(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $this->authorize('assign', $productionOrder);

        $validated = $request->validate([
            'artisan_id' => 'required|exists:artisans,id',
            'qty' => 'required|integer|min:1',
        ]);

        $assignment = $this->productionOrderService->assignArtisan(
            $productionOrder,
            $validated['artisan_id'],
            $validated['qty']
        );

        return $this->sendResponse($assignment->load('artisan'), 'Artisan assigned successfully', 201);
    }

    public function updateStatus(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $this->authorize('update', $productionOrder);

        $validated = $request->validate([
            'status' => 'required|string|in:in_progress,completed,on_hold,cancelled',
        ]);

        $order = $this->productionOrderService->updateStatus($productionOrder, $validated['status']);

        return $this->sendResponse(
            new ProductionOrderResource($order),
            'Production order status updated'
        );
    }

    public function capacityPlanning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $capacity = $this->productionOrderService->getCapacityPlanning($validated);

        return $this->sendResponse($capacity);
    }

    public function assign(Request $request, $id)
    {
        return $this->assignArtisan($request, $id);
    }

    public function qc(Request $request, $id)
    {
        $validated = $request->validate([
            'artisan_id' => 'required|exists:artisans,id',
            'result' => 'required|in:pass,fail,rework',
            'defect_reason' => 'nullable|string',
            'defect_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $inspection = app(QcService::class)->recordInspection(
            $id,
            $validated['artisan_id'],
            auth()->id(),
            $validated['result'],
            $validated['defect_reason'] ?? null,
            $validated['defect_details'] ?? null,
            $validated['notes'] ?? null
        );

        return new QcInspectionResource($inspection);
    }
}
