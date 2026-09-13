<?php

namespace App\Services\Production;

use App\Models\QcInspection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QcService
{
    private const VALID_RESULTS = ['pass', 'fail', 'rework'];

    public function getAll(array $filters = [])
    {
        $query = QcInspection::with(['productionOrder.productVariant.product', 'artisan', 'inspector']);

        if (isset($filters['production_order_id'])) {
            $query->where('production_order_id', $filters['production_order_id']);
        }

        if (isset($filters['artisan_id'])) {
            $query->where('artisan_id', $filters['artisan_id']);
        }

        if (isset($filters['result'])) {
            $query->where('result', $filters['result']);
        }

        return $query->orderByDesc('inspected_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): QcInspection
    {
        return QcInspection::with([
            'productionOrder.productVariant.product',
            'artisan',
            'inspector',
        ])->findOrFail($id);
    }

    public function create(array $data): QcInspection
    {
        $data['inspector_id'] = Auth::id();
        $data['inspected_at'] = $data['inspected_at'] ?? now();

        $inspection = QcInspection::create($data);

        return $inspection->load(['productionOrder.productVariant.product', 'artisan', 'inspector']);
    }

    public function update(QcInspection $inspection, array $data): QcInspection
    {
        if (in_array($inspection->result, ['pass'])) {
            throw new \Exception('Cannot update a passed inspection.');
        }

        $inspection->update($data);

        return $inspection->fresh(['productionOrder.productVariant.product', 'artisan', 'inspector']);
    }

    public function markPass(QcInspection $inspection): QcInspection
    {
        if ($inspection->result === 'pass') {
            throw new \Exception('Inspection is already marked as pass.');
        }

        $inspection->update(['result' => 'pass', 'defect_reason' => null, 'defect_details' => null]);

        return $inspection->fresh(['productionOrder.productVariant.product', 'artisan', 'inspector']);
    }

    public function markFail(QcInspection $inspection, string $defectReason, ?array $defectDetails = null): QcInspection
    {
        if ($inspection->result === 'fail') {
            throw new \Exception('Inspection is already marked as fail.');
        }

        $inspection->update([
            'result' => 'fail',
            'defect_reason' => $defectReason,
            'defect_details' => $defectDetails,
        ]);

        return $inspection->fresh(['productionOrder.productVariant.product', 'artisan', 'inspector']);
    }

    public function markRework(QcInspection $inspection, ?string $notes = null): QcInspection
    {
        if ($inspection->result === 'rework') {
            throw new \Exception('Inspection is already marked as rework.');
        }

        $updateData = ['result' => 'rework'];
        if ($notes) {
            $updateData['notes'] = $notes;
        }

        $inspection->update($updateData);

        return $inspection->fresh(['productionOrder.productVariant.product', 'artisan', 'inspector']);
    }

    public function getDefectSummary(array $filters = []): array
    {
        $query = QcInspection::where('result', 'fail');

        if (isset($filters['from_date'])) {
            $query->where('inspected_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('inspected_at', '<=', $filters['to_date']);
        }

        if (isset($filters['artisan_id'])) {
            $query->where('artisan_id', $filters['artisan_id']);
        }

        $defects = $query->select('defect_reason', DB::raw('count(*) as count'))
            ->groupBy('defect_reason')
            ->orderByDesc('count')
            ->get();

        $totalInspections = QcInspection::count();
        $totalFails = $query->count();
        $passRate = $totalInspections > 0
            ? round((($totalInspections - $totalFails) / $totalInspections) * 100, 2)
            : 0;

        return [
            'defects' => $defects,
            'total_inspections' => $totalInspections,
            'total_fails' => $totalFails,
            'pass_rate' => $passRate,
        ];
    }

    public function delete(QcInspection $inspection): bool
    {
        if ($inspection->result === 'pass') {
            throw new \Exception('Cannot delete a passed inspection.');
        }

        return $inspection->delete();
    }
}
