<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\BaseController;
use App\Http\Resources\StockMovementResource;
use App\Services\Inventory\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends BaseController
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $movements = $this->stockService->getMovements($request->all());

        return $this->sendPaginated($movements);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|string|in:purchase,production,sale,transfer_in,transfer_out,adjustment_in,adjustment_out',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $movement = $this->stockService->createMovement($validated);

        return $this->sendResponse(
            new StockMovementResource($movement),
            'Stock movement recorded',
            201
        );
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_qty' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        $movement = $this->stockService->adjustStock(
            $validated['item_type'],
            $validated['item_id'],
            $validated['warehouse_id'],
            $validated['adjustment_qty'],
            $validated['reason']
        );

        if (! $movement) {
            return $this->sendResponse([], 'No adjustment needed - stock count matches');
        }

        return $this->sendResponse(
            new StockMovementResource($movement),
            'Stock adjusted successfully'
        );
    }

    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|neq:from_warehouse_id',
            'qty' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
        ]);

        $movements = $this->stockService->transferStock(
            $validated['item_type'],
            $validated['item_id'],
            $validated['from_warehouse_id'],
            $validated['to_warehouse_id'],
            $validated['qty'],
            $validated['reason']
        );

        return $this->sendResponse([
            'out_movement' => new StockMovementResource($movements['out']),
            'in_movement' => new StockMovementResource($movements['in']),
        ], 'Stock transferred successfully');
    }

    public function alerts(): JsonResponse
    {
        $alerts = $this->stockService->getLowStockAlerts();

        return $this->sendResponse($alerts);
    }

    public function cycleCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'warehouse_id' => 'required|exists:warehouses,id',
            'counted_qty' => 'required|integer|min:0',
        ]);

        $movement = $this->stockService->cycleCount(
            $validated['item_type'],
            $validated['item_id'],
            $validated['warehouse_id'],
            $validated['counted_qty']
        );

        if (! $movement) {
            return $this->sendResponse([], 'Stock count matches system count - no adjustment needed');
        }

        return $this->sendResponse(
            new StockMovementResource($movement),
            'Cycle count adjustment recorded'
        );
    }
}
