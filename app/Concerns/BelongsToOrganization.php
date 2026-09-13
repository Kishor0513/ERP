<?php

namespace App\Concerns;

use App\Models\Organization;
use App\Support\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            $id = CurrentOrganization::id();
            if ($id) {
                $builder->where($builder->getModel()->getTable().'.organization_id', $id);
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->organization_id) && CurrentOrganization::id()) {
                $model->organization_id = CurrentOrganization::id();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeWithoutOrganizationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organization');
    }
}
