<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\Finance\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = $this->invoiceService->getAll($request->all());

        return $this->sendPaginated($invoices);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->create($request->validated());

        return $this->sendResponse(
            new InvoiceResource($invoice),
            'Invoice created successfully',
            201
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice = $this->invoiceService->getById($invoice->id);

        return $this->sendResponse(new InvoiceResource($invoice));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $invoice = $this->invoiceService->update($invoice, $request->validated());

        return $this->sendResponse(
            new InvoiceResource($invoice),
            'Invoice updated successfully'
        );
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $this->invoiceService->delete($invoice);

        return $this->sendResponse([], 'Invoice deleted successfully');
    }

    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('recordPayment', $invoice);

        $validated = $request->validate([
            'method' => 'required|string|in:bank_transfer,cash,upi,card,cheque',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'reference_number' => 'nullable|string|max:100',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = $this->invoiceService->recordPayment($invoice, $validated);

        return $this->sendResponse(
            new PaymentResource($payment),
            'Payment recorded successfully',
            201
        );
    }
}
