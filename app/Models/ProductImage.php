<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'product_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'sort_order',
        'is_primary',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
