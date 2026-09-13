<?php

namespace App\Support;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class CurrentOrganization
{
    protected static ?int $overrideId = null;

    public static function override(?int $id): void
    {
        static::$overrideId = $id;
    }

    public static function id(): ?int
    {
        if (static::$overrideId) {
            return static::$overrideId;
        }

        $user = Auth::user();

        if ($user && $user->current_organization_id) {
            return (int) $user->current_organization_id;
        }

        return null;
    }

    public static function resolve(): ?Organization
    {
        $id = static::id();

        return $id ? Organization::find($id) : null;
    }

    public static function reset(): void
    {
        static::$overrideId = null;
    }
}
