<?php

namespace App\Services\Catalog;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function getAll(array $filters = []): Collection
    {
        $query = Category::with('children');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getTree(): Collection
    {
        return Category::with('children.children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getById(int $id): Category
    {
        return Category::with(['parent', 'children', 'products'])->findOrFail($id);
    }

    public function create(array $data): Category
    {
        if (isset($data['parent_id']) && $data['parent_id']) {
            $this->validateParent($data['parent_id']);
        }

        $category = Category::create($data);

        return $category->load('parent');
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['parent_id'])) {
            if ($data['parent_id'] == $category->id) {
                throw new \Exception('Category cannot be its own parent.');
            }

            if ($data['parent_id']) {
                $this->validateParent($data['parent_id'], $category->id);
            }
        }

        $category->update($data);

        return $category->load('parent', 'children');
    }

    public function delete(Category $category): bool
    {
        if ($category->children()->count() > 0) {
            throw new \Exception('Cannot delete category with subcategories.');
        }

        if ($category->products()->count() > 0) {
            throw new \Exception('Cannot delete category with associated products.');
        }

        return $category->delete();
    }

    public function reorder(array $orderData): void
    {
        DB::transaction(function () use ($orderData) {
            foreach ($orderData as $item) {
                Category::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        });
    }

    protected function validateParent(int $parentId, ?int $excludeId = null): void
    {
        $parent = Category::findOrFail($parentId);

        if ($excludeId) {
            $descendants = $this->getDescendantIds($excludeId);
            if (in_array($parentId, $descendants)) {
                throw new \Exception('Cannot assign a descendant as parent.');
            }
        }
    }

    protected function getDescendantIds(int $categoryId): array
    {
        $ids = [];
        $children = Category::where('parent_id', $categoryId)->get();

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child->id));
        }

        return $ids;
    }
}
