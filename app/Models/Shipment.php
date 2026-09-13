<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'shipment_number',
        'sales_order_id',
        'carrier',
        'tracking_no',
        'incoterm',
        'hs_code',
        'declared_value',
        'currency',
        'status',
        'estimated_arrival',
        'actual_arrival',
        'certificate_of_origin',
        'fair_trade_doc',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'declared_value' => 'decimal:2',
            'estimated_arrival' => 'date',
            'actual_arrival' => 'date',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
