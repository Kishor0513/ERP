<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['parent_id', 'search', 'is_active']);
        $categories = $this->categoryService->getAll($filters);

        return $this->sendResponse(CategoryResource::collection($categories));
    }

    public function tree(): JsonResponse
    {
        $tree = $this->categoryService->getTree();

        return $this->sendResponse(CategoryResource::collection($tree));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return $this->sendResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    public function show(Category $category): JsonResponse
    {
        $category = $this->categoryService->getById($category->id);

        return $this->sendResponse(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());

        return $this->sendResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return $this->sendResponse([], 'Category deleted successfully');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        $this->categoryService->reorder($request->order);

        return $this->sendResponse([], 'Categories reordered successfully');
    }
}
