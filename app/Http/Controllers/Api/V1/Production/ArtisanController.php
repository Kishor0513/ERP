<?php

namespace App\Http\Controllers\Api\V1\Production;

use App\Http\Controllers\BaseController;
use App\Http\Requests\HR\StoreArtisanRequest;
use App\Http\Requests\HR\UpdateArtisanRequest;
use App\Http\Resources\ArtisanResource;
use App\Models\Artisan;
use App\Services\HR\ArtisanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtisanController extends BaseController
{
    public function __construct(
        private ArtisanService $artisanService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Artisan::class);

        $artisans = $this->artisanService->getAll($request->all());

        return $this->sendPaginated($artisans);
    }

    public function store(StoreArtisanRequest $request): JsonResponse
    {
        $this->authorize('create', Artisan::class);

        $artisan = $this->artisanService->create($request->validated());

        return $this->sendResponse(
            new ArtisanResource($artisan),
            'Artisan created successfully',
            201
        );
    }

    public function show(Artisan $artisan): JsonResponse
    {
        $this->authorize('view', $artisan);

        $artisan = $this->artisanService->getById($artisan->id);

        return $this->sendResponse(new ArtisanResource($artisan));
    }

    public function update(UpdateArtisanRequest $request, Artisan $artisan): JsonResponse
    {
        $this->authorize('update', $artisan);

        $artisan = $this->artisanService->update($artisan, $request->validated());

        return $this->sendResponse(
            new ArtisanResource($artisan),
            'Artisan updated successfully'
        );
    }

    public function destroy(Artisan $artisan): JsonResponse
    {
        $this->authorize('delete', $artisan);

        $this->artisanService->delete($artisan);

        return $this->sendResponse([], 'Artisan deleted successfully');
    }

    public function updateSkills(Request $request, Artisan $artisan): JsonResponse
    {
        $this->authorize('update', $artisan);

        $validated = $request->validate([
            'skills' => 'required|array',
            'skills.*' => 'string|max:100',
        ]);

        $artisan = $this->artisanService->updateSkills($artisan, $validated['skills']);

        return $this->sendResponse(
            new ArtisanResource($artisan),
            'Artisan skills updated'
        );
    }

    public function skillMap(): JsonResponse
    {
        $skillMap = $this->artisanService->getSkillMap();

        return $this->sendResponse($skillMap);
    }
}
