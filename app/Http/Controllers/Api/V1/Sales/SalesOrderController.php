<?php

namespace App\Http\Controllers\Api\V1\Sales;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Services\Logistics\ShipmentService;
use App\Services\Sales\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends BaseController
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = $this->orderService->getAll($request->all());

        return $this->sendPaginated($orders);
    }

    public function store(StoreSalesOrderRequest $request): JsonResponse
    {
        $this->authorize('create', SalesOrder::class);

        $order = $this->orderService->create($request->validated());

        return $this->sendResponse(
            new SalesOrderResource($order),
            'Sales order created successfully',
            201
        );
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('view', $salesOrder);

        $order = $this->orderService->getById($salesOrder->id);

        return $this->sendResponse(new SalesOrderResource($order));
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('update', $salesOrder);

        $order = $this->orderService->update($salesOrder, $request->validated());

        return $this->sendResponse(
            new SalesOrderResource($order),
            'Sales order updated successfully'
        );
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('delete', $salesOrder);

        $this->orderService->delete($salesOrder);

        return $this->sendResponse([], 'Sales order deleted successfully');
    }

    public function updateStatus(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('updateStatus', $salesOrder);

        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,processing,shipped,partially_shipped,delivered,completed,on_hold,cancelled',
        ]);

        $order = $this->orderService->updateStatus($salesOrder, $validated['status']);

        return $this->sendResponse(
            new SalesOrderResource($order),
            'Sales order status updated'
        );
    }

    public function ship(SalesOrder $id)
    {
        $validated = request()->validate([
            'carrier' => 'required|string',
            'tracking_no' => 'nullable|string',
            'incoterm' => 'nullable|string|in:FOB,CIF,EXW,DDP',
            'estimated_arrival' => 'nullable|date',
        ]);

        $shipment = app(ShipmentService::class)->createShipment($id, $validated);

        return new ShipmentResource($shipment);
    }

    public function cancel(SalesOrder $id)
    {
        $validated = request()->validate([
            'reason' => 'required|string',
        ]);

        $order = app(OrderService::class)->cancelOrder($id, $validated['reason']);

        return new SalesOrderResource($order);
    }
}
