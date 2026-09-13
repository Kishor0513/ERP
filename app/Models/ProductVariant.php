<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'product_id',
        'sku',
        'price',
        'cost_price',
        'stock_quantity',
        'weight_grams',
        'is_active',
        'attribute_values',
        'color_chart_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'attribute_values' => 'array',
        ];
    }

    public function getFullSkuAttribute(): string
    {
        return $this->product->sku_prefix.'-'.$this->sku;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorChartEntry(): BelongsTo
    {
        return $this->belongsTo(ColorChartEntry::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(PriceTier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
