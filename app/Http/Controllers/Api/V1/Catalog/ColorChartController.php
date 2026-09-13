<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\BaseController;
use App\Models\ColorChartEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColorChartController extends BaseController
{
    public function index(): JsonResponse
    {
        $entries = ColorChartEntry::orderBy('code')->get();

        return $this->sendResponse($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:color_chart_entries,code',
            'name' => 'required|string|max:100',
            'hex_color' => 'required|string|max:7',
            'swatch_image' => 'nullable|string|max:500',
        ]);

        $entry = ColorChartEntry::create($validated);

        return $this->sendResponse($entry, 'Color chart entry created', 201);
    }

    public function show(ColorChartEntry $colorChartEntry): JsonResponse
    {
        return $this->sendResponse($colorChartEntry);
    }

    public function update(Request $request, ColorChartEntry $colorChartEntry): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:color_chart_entries,code,'.$colorChartEntry->id,
            'name' => 'sometimes|required|string|max:100',
            'hex_color' => 'sometimes|required|string|max:7',
            'swatch_image' => 'nullable|string|max:500',
        ]);

        $colorChartEntry->update($validated);

        return $this->sendResponse($colorChartEntry, 'Color chart entry updated');
    }

    public function destroy(ColorChartEntry $colorChartEntry): JsonResponse
    {
        if ($colorChartEntry->productVariants()->exists()) {
            return $this->sendError('Cannot delete color chart entry with associated variants.', [], 422);
        }

        $colorChartEntry->delete();

        return $this->sendResponse([], 'Color chart entry deleted');
    }
}
