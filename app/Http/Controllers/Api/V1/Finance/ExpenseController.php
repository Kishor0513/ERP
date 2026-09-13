<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Http\Requests\Finance\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\Finance\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends BaseController
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = $this->expenseService->getAll($request->all());

        return $this->sendPaginated($expenses);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenseService->create($request->validated());

        return $this->sendResponse(
            new ExpenseResource($expense),
            'Expense recorded successfully',
            201
        );
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        $expense = $this->expenseService->getById($expense->id);

        return $this->sendResponse(new ExpenseResource($expense));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        $expense = $this->expenseService->update($expense, $request->validated());

        return $this->sendResponse(
            new ExpenseResource($expense),
            'Expense updated successfully'
        );
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $this->authorize('delete', $expense);

        $this->expenseService->delete($expense);

        return $this->sendResponse([], 'Expense deleted successfully');
    }

    public function approve(Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);

        $expense = $this->expenseService->approve($expense);

        return $this->sendResponse(
            new ExpenseResource($expense),
            'Expense approved'
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $summary = $this->expenseService->getSummary($validated);

        return $this->sendResponse($summary);
    }
}
