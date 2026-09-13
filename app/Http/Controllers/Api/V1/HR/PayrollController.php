<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\BaseController;
use App\Http\Requests\HR\StorePayrollRunRequest;
use App\Http\Requests\HR\UpdatePayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Models\PayrollRun;
use App\Models\PieceRate;
use App\Services\HR\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends BaseController
{
    public function __construct(
        private PayrollService $payrollService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PayrollRun::class);

        $payrollRuns = $this->payrollService->getAll($request->all());

        return $this->sendPaginated($payrollRuns);
    }

    public function store(StorePayrollRunRequest $request): JsonResponse
    {
        $this->authorize('create', PayrollRun::class);

        $payrollRun = $this->payrollService->create($request->validated());

        return $this->sendResponse(
            new PayrollRunResource($payrollRun),
            'Payroll run created successfully',
            201
        );
    }

    public function show(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('view', $payrollRun);

        $payrollRun = $this->payrollService->getById($payrollRun->id);

        return $this->sendResponse(new PayrollRunResource($payrollRun));
    }

    public function update(UpdatePayrollRunRequest $request, PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('update', $payrollRun);

        $payrollRun = $this->payrollService->update($payrollRun, $request->validated());

        return $this->sendResponse(
            new PayrollRunResource($payrollRun),
            'Payroll run updated successfully'
        );
    }

    public function destroy(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('delete', $payrollRun);

        $this->payrollService->delete($payrollRun);

        return $this->sendResponse([], 'Payroll run deleted successfully');
    }

    public function runPayroll(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('update', $payrollRun);

        $payrollRun = $this->payrollService->runPayroll($payrollRun);

        return $this->sendResponse(
            new PayrollRunResource($payrollRun),
            'Payroll calculated successfully'
        );
    }

    public function approve(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('approve', $payrollRun);

        $payrollRun = $this->payrollService->approve($payrollRun);

        return $this->sendResponse(
            new PayrollRunResource($payrollRun),
            'Payroll run approved'
        );
    }

    public function export(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('export', $payrollRun);

        $exportData = $this->payrollService->export($payrollRun);

        return $this->sendResponse($exportData);
    }

    public function pieceRates()
    {
        $rates = PieceRate::all();

        return response()->json(['data' => $rates]);
    }

    public function storePieceRate(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'operation' => 'required|string',
            'rate' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        $rate = PieceRate::create($validated);

        return response()->json(['data' => $rate], 201);
    }

    public function showPieceRate($id)
    {
        $rate = PieceRate::findOrFail($id);

        return response()->json(['data' => $rate]);
    }

    public function updatePieceRate(Request $request, $id)
    {
        $rate = PieceRate::findOrFail($id);
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'operation' => 'required|string',
            'rate' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        $rate->update($validated);

        return response()->json(['data' => $rate]);
    }

    public function destroyPieceRate($id)
    {
        $rate = PieceRate::findOrFail($id);
        $rate->delete();

        return response()->json(['message' => 'Piece rate deleted']);
    }

    public function pay($id)
    {
        $run = app(PayrollService::class)->markPaid($id);

        return new PayrollRunResource($run);
    }
}
