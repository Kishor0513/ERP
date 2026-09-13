<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Organization extends Model
{
    use Billable, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'personal_team',
        'trial_ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'trial_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->onTrial()) {
            return true;
        }

        if (! method_exists($this, 'subscribed')) {
            return false;
        }

        return $this->subscribed('default');
    }
}
