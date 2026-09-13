<?php

namespace App\Services\HR;

use App\Models\Artisan;

class ArtisanService
{
    public function getAll(array $filters = [])
    {
        $query = Artisan::with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['certification_status'])) {
            $query->where('certification_status', $filters['certification_status']);
        }

        if (isset($filters['skill'])) {
            $query->whereJsonContains('skills', $filters['skill']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('location', 'like', "%{$filters['search']}%")
                    ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getById(int $id): Artisan
    {
        return Artisan::with(['user', 'productionAssignments.productionOrder.productVariant.product', 'payrollItems.payrollRun'])
            ->findOrFail($id);
    }

    public function create(array $data): Artisan
    {
        $data['status'] = $data['status'] ?? 'active';
        $data['join_date'] = $data['join_date'] ?? now();

        return Artisan::create($data);
    }

    public function update(Artisan $artisan, array $data): Artisan
    {
        $artisan->update($data);

        return $artisan->fresh(['user', 'productionAssignments']);
    }

    public function delete(Artisan $artisan): bool
    {
        if ($artisan->productionAssignments()->where('status', '!=', 'completed')->exists()) {
            throw new \Exception('Cannot delete artisan with active production assignments.');
        }

        return $artisan->delete();
    }

    public function updateSkills(Artisan $artisan, array $skills): Artisan
    {
        $artisan->update(['skills' => $skills]);

        return $artisan->fresh();
    }

    public function getSkillMap(): array
    {
        $artisans = Artisan::where('status', 'active')->get();

        $skillMap = [];
        foreach ($artisans as $artisan) {
            if ($artisan->skills) {
                foreach ($artisan->skills as $skill) {
                    $skillMap[$skill][] = [
                        'id' => $artisan->id,
                        'name' => $artisan->name,
                        'certification_status' => $artisan->certification_status,
                    ];
                }
            }
        }

        return $skillMap;
    }
}
