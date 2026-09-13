<?php

namespace App\Http\Controllers\Api\V1\Production;

use App\Http\Controllers\BaseController;
use App\Http\Resources\QcInspectionResource;
use App\Models\QcInspection;
use App\Services\Production\QcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QcInspectionController extends BaseController
{
    public function __construct(
        private QcService $qcService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $inspections = $this->qcService->getAll($request->all());

        return $this->sendPaginated($inspections);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'artisan_id' => 'required|exists:artisans,id',
            'result' => 'required|string|in:pass,fail,rework',
            'defect_reason' => 'nullable|string|max:255',
            'defect_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $inspection = $this->qcService->create($validated);

        return $this->sendResponse(
            new QcInspectionResource($inspection),
            'QC inspection recorded',
            201
        );
    }

    public function show(QcInspection $qcInspection): JsonResponse
    {
        $inspection = $this->qcService->getById($qcInspection->id);

        return $this->sendResponse(new QcInspectionResource($inspection));
    }

    public function update(Request $request, QcInspection $qcInspection): JsonResponse
    {
        $validated = $request->validate([
            'result' => 'sometimes|required|string|in:pass,fail,rework',
            'defect_reason' => 'nullable|string|max:255',
            'defect_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $inspection = $this->qcService->update($qcInspection, $validated);

        return $this->sendResponse(
            new QcInspectionResource($inspection),
            'QC inspection updated'
        );
    }

    public function destroy(QcInspection $qcInspection): JsonResponse
    {
        $this->qcService->delete($qcInspection);

        return $this->sendResponse([], 'QC inspection deleted');
    }

    public function markPass(QcInspection $qcInspection): JsonResponse
    {
        $inspection = $this->qcService->markPass($qcInspection);

        return $this->sendResponse(
            new QcInspectionResource($inspection),
            'Inspection marked as passed'
        );
    }

    public function markFail(Request $request, QcInspection $qcInspection): JsonResponse
    {
        $validated = $request->validate([
            'defect_reason' => 'required|string|max:255',
            'defect_details' => 'nullable|array',
        ]);

        $inspection = $this->qcService->markFail(
            $qcInspection,
            $validated['defect_reason'],
            $validated['defect_details'] ?? null
        );

        return $this->sendResponse(
            new QcInspectionResource($inspection),
            'Inspection marked as failed'
        );
    }

    public function markRework(Request $request, QcInspection $qcInspection): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $inspection = $this->qcService->markRework($qcInspection, $validated['notes'] ?? null);

        return $this->sendResponse(
            new QcInspectionResource($inspection),
            'Inspection marked for rework'
        );
    }

    public function defectSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'artisan_id' => 'nullable|exists:artisans,id',
        ]);

        $summary = $this->qcService->getDefectSummary($validated);

        return $this->sendResponse($summary);
    }
}
