<?php

namespace App\Http\Controllers\Api\V1\Procurement;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Procurement\StorePurchaseOrderRequest;
use App\Http\Requests\Procurement\UpdatePurchaseOrderRequest;
use App\Http\Resources\GoodsReceiptNoteResource;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\Procurement\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends BaseController
{
    public function __construct(
        private PurchaseOrderService $purchaseOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $orders = $this->purchaseOrderService->getAll($request->all());

        return $this->sendPaginated($orders);
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $order = $this->purchaseOrderService->create($request->validated());

        return $this->sendResponse(
            new PurchaseOrderResource($order),
            'Purchase order created successfully',
            201
        );
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        $order = $this->purchaseOrderService->getById($purchaseOrder->id);

        return $this->sendResponse(new PurchaseOrderResource($order));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);

        $order = $this->purchaseOrderService->update($purchaseOrder, $request->validated());

        return $this->sendResponse(
            new PurchaseOrderResource($order),
            'Purchase order updated successfully'
        );
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('delete', $purchaseOrder);

        $this->purchaseOrderService->delete($purchaseOrder);

        return $this->sendResponse([], 'Purchase order deleted successfully');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);

        $validated = $request->validate([
            'status' => 'required|string|in:submitted,confirmed,partially_received,received,cancelled',
        ]);

        $order = $this->purchaseOrderService->updateStatus($purchaseOrder, $validated['status']);

        return $this->sendResponse(
            new PurchaseOrderResource($order),
            'Purchase order status updated'
        );
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('receive', $purchaseOrder);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.qty_received' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $grn = $this->purchaseOrderService->receive(
            $purchaseOrder,
            $validated['items'],
            $validated['notes'] ?? null
        );

        return $this->sendResponse(
            new GoodsReceiptNoteResource($grn),
            'Goods received successfully',
            201
        );
    }
}
