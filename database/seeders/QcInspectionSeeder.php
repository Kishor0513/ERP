<?php

namespace Database\Seeders;

use App\Models\ProductionOrder;
use App\Models\QcInspection;
use App\Models\User;
use Illuminate\Database\Seeder;

class QcInspectionSeeder extends Seeder
{
    public function run(): void
    {
        $inspector = User::first();
        $orders = ProductionOrder::take(3)->get();

        if ($orders->isEmpty()) {
            return;
        }

        $results = ['passed', 'passed', 'failed'];

        foreach ($orders as $i => $order) {
            QcInspection::create([
                'production_order_id' => $order->id,
                'artisan_id' => $order->artisan_id ?? $order->assigned_artisan_id ?? null,
                'inspector_id' => $inspector?->id,
                'result' => $results[$i % count($results)],
                'defect_reason' => $i === 2 ? 'Uneven stitching on edge' : null,
                'notes' => $i === 2 ? 'Sent back for rework.' : 'Meets quality checklist.',
                'inspected_at' => now()->subDays(2 - $i),
            ]);
        }
    }
}
