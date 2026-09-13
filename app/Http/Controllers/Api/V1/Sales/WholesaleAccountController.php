<?php

namespace App\Http\Controllers\Api\V1\Sales;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Sales\StoreWholesaleAccountRequest;
use App\Http\Requests\Sales\UpdateWholesaleAccountRequest;
use App\Http\Resources\WholesaleAccountResource;
use App\Models\WholesaleAccount;
use App\Services\Sales\WholesaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WholesaleAccountController extends BaseController
{
    public function __construct(
        private WholesaleService $wholesaleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WholesaleAccount::class);

        $accounts = $this->wholesaleService->getAll($request->all());

        return $this->sendPaginated($accounts);
    }

    public function store(StoreWholesaleAccountRequest $request): JsonResponse
    {
        $this->authorize('create', WholesaleAccount::class);

        $account = $this->wholesaleService->create($request->validated());

        return $this->sendResponse(
            new WholesaleAccountResource($account),
            'Wholesale account created successfully',
            201
        );
    }

    public function show(WholesaleAccount $wholesaleAccount): JsonResponse
    {
        $this->authorize('view', $wholesaleAccount);

        $account = $this->wholesaleService->getById($wholesaleAccount->id);

        return $this->sendResponse(new WholesaleAccountResource($account));
    }

    public function update(UpdateWholesaleAccountRequest $request, WholesaleAccount $wholesaleAccount): JsonResponse
    {
        $this->authorize('update', $wholesaleAccount);

        $account = $this->wholesaleService->update($wholesaleAccount, $request->validated());

        return $this->sendResponse(
            new WholesaleAccountResource($account),
            'Wholesale account updated successfully'
        );
    }

    public function destroy(WholesaleAccount $wholesaleAccount): JsonResponse
    {
        $this->authorize('delete', $wholesaleAccount);

        $this->wholesaleService->delete($wholesaleAccount);

        return $this->sendResponse([], 'Wholesale account deleted successfully');
    }

    public function approve(WholesaleAccount $wholesaleAccount): JsonResponse
    {
        $this->authorize('approve', $wholesaleAccount);

        $account = $this->wholesaleService->approve($wholesaleAccount);

        return $this->sendResponse(
            new WholesaleAccountResource($account),
            'Wholesale account approved'
        );
    }

    public function reject(Request $request, WholesaleAccount $wholesaleAccount): JsonResponse
    {
        $this->authorize('approve', $wholesaleAccount);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $account = $this->wholesaleService->reject($wholesaleAccount, $validated['reason'] ?? null);

        return $this->sendResponse(
            new WholesaleAccountResource($account),
            'Wholesale account rejected'
        );
    }
}
