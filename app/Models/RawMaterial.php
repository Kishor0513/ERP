<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'unit',
        'description',
        'reorder_point',
        'current_stock',
        'cost_per_unit',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(RawMaterialBatch::class);
    }

    public function boms(): HasMany
    {
        return $this->hasMany(ProductBom::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function scopeBelowReorderPoint(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'reorder_point');
    }
}
