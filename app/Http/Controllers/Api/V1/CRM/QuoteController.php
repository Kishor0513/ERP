<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CRM\StoreQuoteRequest;
use App\Http\Requests\CRM\UpdateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\SalesOrderResource;
use App\Models\Quote;
use App\Services\CRM\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends BaseController
{
    public function __construct(
        private QuoteService $quoteService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Quote::class);

        $quotes = $this->quoteService->getAll($request->all());

        return $this->sendPaginated($quotes);
    }

    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $this->authorize('create', Quote::class);

        $quote = $this->quoteService->create($request->validated());

        return $this->sendResponse(
            new QuoteResource($quote),
            'Quote created successfully',
            201
        );
    }

    public function show(Quote $quote): JsonResponse
    {
        $this->authorize('view', $quote);

        $quote = $this->quoteService->getById($quote->id);

        return $this->sendResponse(new QuoteResource($quote));
    }

    public function update(UpdateQuoteRequest $request, Quote $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $quote = $this->quoteService->update($quote, $request->validated());

        return $this->sendResponse(
            new QuoteResource($quote),
            'Quote updated successfully'
        );
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $this->authorize('delete', $quote);

        $this->quoteService->delete($quote);

        return $this->sendResponse([], 'Quote deleted successfully');
    }

    public function updateStatus(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'status' => 'required|string|in:sent,accepted,rejected,expired,cancelled',
        ]);

        $quote = $this->quoteService->updateStatus($quote, $validated['status']);

        return $this->sendResponse(
            new QuoteResource($quote),
            'Quote status updated'
        );
    }

    public function convertToOrder(Quote $quote): JsonResponse
    {
        $this->authorize('convert', $quote);

        $order = $this->quoteService->convertToOrder($quote);

        return $this->sendResponse(
            new SalesOrderResource($order),
            'Quote converted to order successfully',
            201
        );
    }

    public function send(Quote $id)
    {
        $quote = app(QuoteService::class)->sendQuote($id);

        return new QuoteResource($quote);
    }

    public function accept(Quote $id)
    {
        $quote = app(QuoteService::class)->acceptQuote($id);

        return new QuoteResource($quote);
    }
}
