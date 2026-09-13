<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->getAll($request->all());

        return $this->sendPaginated($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->create($request->validated());

        return $this->sendResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product = $this->productService->getById($product->id);

        return $this->sendResponse(new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->productService->update($product, $request->validated());

        return $this->sendResponse(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return $this->sendResponse([], 'Product deleted successfully');
    }

    public function storeVariant(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight_grams' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'attribute_values' => 'nullable|array',
            'color_chart_entry_id' => 'nullable|exists:color_chart_entries,id',
        ]);

        $variant = $product->variants()->create($validated);

        return $this->sendResponse(
            new ProductVariantResource($variant),
            'Variant created successfully',
            201
        );
    }

    public function bulkImport(Request $request): JsonResponse
    {
        $this->authorize('import', Product::class);

        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.category_id' => 'required|exists:categories,id',
            'products.*.base_price' => 'required|numeric|min:0',
        ]);

        $results = $this->productService->bulkImport($request->products);

        return $this->sendResponse($results, 'Bulk import completed');
    }
}
