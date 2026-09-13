<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductVariant;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function getAll(array $filters = [])
    {
        $query = Product::with(['category', 'variants', 'images']);

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku_prefix', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_customizable'])) {
            $query->where('is_customizable', $filters['is_customizable']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getById(int $id): Product
    {
        return Product::with(['category', 'variants.priceTiers', 'variants.colorChartEntry', 'variants.images', 'images', 'boms.rawMaterial'])
            ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variantData['sku'] = $variantData['sku'] ?? $this->generateSku($product);
                    $product->variants()->create($variantData);
                }
            }

            return $product->load(['category', 'variants']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    if (isset($variantData['id'])) {
                        $product->variants()->where('id', $variantData['id'])->update($variantData);
                    } else {
                        $variantData['sku'] = $variantData['sku'] ?? $this->generateSku($product);
                        $product->variants()->create($variantData);
                    }
                }
            }

            return $product->load(['category', 'variants']);
        });
    }

    public function delete(Product $product): bool
    {
        $hasOrders = SalesOrderItem::whereHas('productVariant', fn ($q) => $q->where('product_id', $product->id))->exists();
        $hasProduction = ProductionOrder::whereHas('productVariant', fn ($q) => $q->where('product_id', $product->id))->exists();
        if ($hasOrders || $hasProduction) {
            throw new \Exception('Cannot delete product with associated orders.');
        }

        return $product->delete();
    }

    public function generateSku(Product $product): string
    {
        $prefix = $product->sku_prefix ?: Str::slug($product->name);
        $lastVariant = ProductVariant::where('product_id', $product->id)
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastVariant && preg_match('/(\d+)$/', $lastVariant->sku, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return strtoupper($prefix.'-'.str_pad($sequence, 3, '0', STR_PAD_LEFT));
    }

    public function bulkImport(array $products): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        DB::transaction(function () use ($products, &$results) {
            foreach ($products as $index => $productData) {
                try {
                    $this->create($productData);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    public function generatePdfCatalog(?int $categoryId = null): string
    {
        $query = Product::with(['category', 'variants.colorChartEntry', 'images'])
            ->where('is_active', true);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->get();

        $html = '<html><body>';
        $html .= '<h1>Product Catalog</h1>';
        $html .= '<p>Generated: '.now()->format('F j, Y').'</p>';

        foreach ($products as $product) {
            $html .= '<div style="page-break-after: always;">';
            $html .= '<h2>'.e($product->name).'</h2>';
            $html .= '<p>SKU: '.e($product->sku_prefix).'</p>';
            $html .= '<p>Base Price: '.number_format($product->base_price, 2).'</p>';
            $html .= '<p>'.e($product->description).'</p>';

            if ($product->variants->count()) {
                $html .= '<h3>Variants</h3>';
                $html .= '<table border="1"><tr><th>SKU</th><th>Price</th><th>Color</th></tr>';
                foreach ($product->variants as $variant) {
                    $html .= '<tr>';
                    $html .= '<td>'.e($variant->full_sku).'</td>';
                    $html .= '<td>'.number_format($variant->price, 2).'</td>';
                    $html .= '<td>'.e($variant->colorChartEntry?->name ?? '-').'</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }

            $html .= '</div>';
        }

        $html .= '</body></html>';

        $path = 'catalogs/catalog_'.now()->format('Y-m-d_His').'.html';
        Storage::disk('public')->put($path, $html);

        return $path;
    }
}
