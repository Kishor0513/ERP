<?php

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Http\Controllers\BaseController;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends BaseController
{
    public function salesSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'channel' => 'nullable|string',
        ]);

        $query = SalesOrder::where('status', '!=', 'cancelled');

        if (isset($validated['from_date'])) {
            $query->where('created_at', '>=', $validated['from_date']);
        }

        if (isset($validated['to_date'])) {
            $query->where('created_at', '<=', $validated['to_date']);
        }

        if (isset($validated['channel'])) {
            $query->where('channel', $validated['channel']);
        }

        $summary = [
            'total_orders' => (clone $query)->count(),
            'total_revenue' => (clone $query)->sum('total'),
            'average_order_value' => (clone $query)->avg('total'),
            'by_status' => (clone $query)
                ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total) as revenue'))
                ->groupBy('status')
                ->get(),
        ];

        return $this->sendResponse($summary);
    }

    public function byChannel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $query = SalesOrder::where('status', '!=', 'cancelled');

        if (isset($validated['from_date'])) {
            $query->where('created_at', '>=', $validated['from_date']);
        }

        if (isset($validated['to_date'])) {
            $query->where('created_at', '<=', $validated['to_date']);
        }

        $byChannel = (clone $query)
            ->select('channel', DB::raw('count(*) as count'), DB::raw('sum(total) as revenue'))
            ->groupBy('channel')
            ->orderByDesc('revenue')
            ->get();

        return $this->sendResponse($byChannel);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $query = SalesOrderItem::with('productVariant.product.category')
            ->whereHas('salesOrder', function ($q) use ($validated) {
                $q->where('status', '!=', 'cancelled');

                if (isset($validated['from_date'])) {
                    $q->where('created_at', '>=', $validated['from_date']);
                }

                if (isset($validated['to_date'])) {
                    $q->where('created_at', '<=', $validated['to_date']);
                }
            });

        $items = $query->get();

        $byCategory = $items->groupBy('productVariant.product.category.name')
            ->map(function ($group) {
                return [
                    'count' => $group->sum('qty'),
                    'revenue' => $group->sum('total'),
                ];
            })
            ->toArray();

        return $this->sendResponse($byCategory);
    }

    public function monthlyTrend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        $months = $validated['months'] ?? 12;

        $trend = SalesOrder::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('count(*) as orders'),
                DB::raw('sum(total) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->sendResponse($trend);
    }
}
