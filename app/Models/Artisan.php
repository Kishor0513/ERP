<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artisan extends Model
{
    use BelongsToOrganization, HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'location',
        'phone',
        'skills',
        'certification_status',
        'bank_details',
        'payout_method',
        'join_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'bank_details' => 'encrypted:array',
            'join_date' => 'date',
        ];
    }

    public function getSkillsListAttribute(): ?array
    {
        return $this->skills;
    }

    public function setSkillsListAttribute(array $value): void
    {
        $this->attributes['skills'] = json_encode($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productionAssignments(): HasMany
    {
        return $this->hasMany(ProductionOrderAssignment::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
