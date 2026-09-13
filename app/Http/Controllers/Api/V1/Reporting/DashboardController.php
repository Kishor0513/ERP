<?php

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Http\Controllers\BaseController;
use App\Models\ProductionOrder;
use App\Models\ProductVariant;
use App\Models\QcInspection;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function dashboard(Request $request): JsonResponse
    {
        $data = [
            'sales' => $this->getSalesData(),
            'production' => $this->getProductionData(),
            'inventory' => $this->getInventoryData(),
            'recent_orders' => $this->getRecentOrders(),
            'top_products' => $this->getTopProducts(),
        ];

        return $this->sendResponse($data);
    }

    protected function getSalesData(): array
    {
        $now = Carbon::now();
        $thisMonthTotal = SalesOrder::where('created_at', '>=', $now->copy()->startOfMonth())
            ->where('status', '!=', 'cancelled')->sum('total');
        $lastMonthTotal = SalesOrder::whereBetween('created_at', [
            $now->copy()->startOfMonth()->subMonth(),
            $now->copy()->startOfMonth(),
        ])->where('status', '!=', 'cancelled')->sum('total');

        $change = $lastMonthTotal > 0
            ? round(($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal * 100, 1)
            : 0;

        $chart = [];
        $day = Carbon::now()->subDays(29);
        for ($i = 0; $i < 30; $i++) {
            $date = $day->copy()->addDays($i);
            $chart[] = [
                'date' => $date->format('M j'),
                'amount' => (float) SalesOrder::whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')->sum('total'),
            ];
        }

        return [
            'total' => (float) SalesOrder::where('status', '!=', 'cancelled')->sum('total'),
            'change' => $change,
            'chart' => $chart,
        ];
    }

    protected function getProductionData(): array
    {
        return [
            'active_orders' => ProductionOrder::whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_today' => ProductionOrder::where('status', 'completed')
                ->whereDate('updated_at', today())->count(),
            'pending_qc' => ProductionOrder::where('status', 'completed')
                ->whereDoesntHave('qcInspections')->count(),
        ];
    }

    protected function getInventoryData(): array
    {
        return [
            'low_stock_items' => RawMaterial::whereColumn('current_stock', '<=', 'reorder_point')->count(),
            'total_value' => (float) ProductVariant::sum(DB::raw('stock_quantity * COALESCE(cost_price, 0)')),
        ];
    }

    protected function getRecentOrders(): array
    {
        return SalesOrder::with('wholesaleAccount')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->wholesaleAccount->company_name ?? 'Direct',
                'status' => $order->status,
                'total' => (float) $order->total,
            ])
            ->toArray();
    }

    protected function getTopProducts(): array
    {
        return ProductVariant::select('product_id', DB::raw('SUM(stock_quantity) as total_stock'))
            ->where('stock_quantity', '>', 0)
            ->groupBy('product_id')
            ->orderByDesc('total_stock')
            ->take(5)
            ->with('product')
            ->get()
            ->map(fn ($variant) => [
                'product' => [
                    'id' => $variant->product_id,
                    'name' => $variant->product->name ?? 'Unknown',
                ],
                'quantity' => (int) $variant->total_stock,
            ])
            ->toArray();
    }

    public function productionSummary()
    {
        $data = [
            'total_orders' => ProductionOrder::count(),
            'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'completed' => ProductionOrder::where('status', 'completed')->count(),
            'qc_pass_rate' => QcInspection::where('result', 'pass')->count() / max(QcInspection::count(), 1) * 100,
        ];

        return response()->json(['data' => $data]);
    }

    public function inventorySummary()
    {
        $data = [
            'total_raw_materials' => RawMaterial::count(),
            'low_stock_count' => RawMaterial::whereColumn('current_stock', '<=', 'reorder_point')->count(),
            'total_variants' => ProductVariant::count(),
            'out_of_stock' => ProductVariant::where('stock_quantity', '<=', 0)->count(),
        ];

        return response()->json(['data' => $data]);
    }
}
