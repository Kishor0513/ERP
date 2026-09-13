<?php

namespace App\Http\Controllers\Api\V1\Logistics;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Logistics\StoreShipmentRequest;
use App\Http\Requests\Logistics\UpdateShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Models\Shipment;
use App\Services\Logistics\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends BaseController
{
    public function __construct(
        private ShipmentService $shipmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shipment::class);

        $shipments = $this->shipmentService->getAll($request->all());

        return $this->sendPaginated($shipments);
    }

    public function store(StoreShipmentRequest $request): JsonResponse
    {
        $this->authorize('create', Shipment::class);

        $shipment = $this->shipmentService->create($request->validated());

        return $this->sendResponse(
            new ShipmentResource($shipment),
            'Shipment created successfully',
            201
        );
    }

    public function show(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $shipment = $this->shipmentService->getById($shipment->id);

        return $this->sendResponse(new ShipmentResource($shipment));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): JsonResponse
    {
        $this->authorize('update', $shipment);

        $shipment = $this->shipmentService->update($shipment, $request->validated());

        return $this->sendResponse(
            new ShipmentResource($shipment),
            'Shipment updated successfully'
        );
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->delete($shipment);

        return $this->sendResponse([], 'Shipment deleted successfully');
    }

    public function dispatch(Shipment $shipment): JsonResponse
    {
        $this->authorize('dispatch', $shipment);

        $shipment = $this->shipmentService->markDispatched($shipment);

        return $this->sendResponse(
            new ShipmentResource($shipment),
            'Shipment marked as dispatched'
        );
    }

    public function deliver(Shipment $shipment): JsonResponse
    {
        $this->authorize('deliver', $shipment);

        $shipment = $this->shipmentService->markDelivered($shipment);

        return $this->sendResponse(
            new ShipmentResource($shipment),
            'Shipment marked as delivered'
        );
    }

    public function customsDocuments(Shipment $shipment): JsonResponse
    {
        $this->authorize('view', $shipment);

        $documents = $this->shipmentService->generateCustomsDocuments($shipment);

        return $this->sendResponse($documents);
    }
}
