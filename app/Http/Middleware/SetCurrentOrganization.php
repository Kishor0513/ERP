<?php

namespace App\Http\Middleware;

use App\Support\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $headerId = $request->header('X-Organization-ID');

            if ($headerId && $user->organizations()->where('organizations.id', $headerId)->exists()) {
                if ((int) $user->current_organization_id !== (int) $headerId) {
                    $user->forceFill(['current_organization_id' => $headerId])->saveQuietly();
                    $user->refresh();
                }
            }

            if ($user->current_organization_id) {
                CurrentOrganization::override((int) $user->current_organization_id);
            }
        }

        $response = $next($request);

        CurrentOrganization::reset();

        return $response;
    }
}
