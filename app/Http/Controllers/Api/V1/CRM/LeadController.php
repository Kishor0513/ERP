<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Http\Controllers\BaseController;
use App\Http\Resources\LeadResource;
use App\Http\Resources\WholesaleAccountResource;
use App\Models\Lead;
use App\Services\CRM\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends BaseController
{
    public function __construct(
        private LeadService $leadService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $leads = $this->leadService->getAll($request->all());

        return $this->sendPaginated($leads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = $this->leadService->create($validated);

        return $this->sendResponse(
            new LeadResource($lead),
            'Lead created successfully',
            201
        );
    }

    public function show(Lead $lead): JsonResponse
    {
        $lead = $this->leadService->getById($lead->id);

        return $this->sendResponse(new LeadResource($lead));
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'sometimes|required|string|max:100',
            'company_name' => 'sometimes|required|string|max:255',
            'contact_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = $this->leadService->update($lead, $validated);

        return $this->sendResponse(
            new LeadResource($lead),
            'Lead updated successfully'
        );
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->leadService->delete($lead);

        return $this->sendResponse([], 'Lead deleted successfully');
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:contacted,qualified,converted,lost',
        ]);

        $lead = $this->leadService->updateStatus($lead, $validated['status']);

        return $this->sendResponse(
            new LeadResource($lead),
            'Lead status updated'
        );
    }

    public function convert(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'account_data' => 'nullable|array',
            'account_data.payment_terms' => 'nullable|string|max:100',
            'account_data.credit_limit' => 'nullable|numeric|min:0',
        ]);

        $account = $this->leadService->convert($lead, $validated['account_data'] ?? []);

        return $this->sendResponse(
            new WholesaleAccountResource($account),
            'Lead converted to wholesale account',
            201
        );
    }

    public function pipeline(): JsonResponse
    {
        $pipeline = $this->leadService->getPipeline();

        return $this->sendResponse($pipeline);
    }
}
