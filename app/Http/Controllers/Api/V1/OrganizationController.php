<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\Organization;
use App\Support\CurrentOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        return $this->sendResponse($request->user()->organizations()->withPivot('role')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $org = Organization::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
            'owner_id' => $request->user()->id,
            'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
        ]);

        $org->users()->attach($request->user()->id, ['role' => 'owner']);
        $request->user()->forceFill(['current_organization_id' => $org->id])->saveQuietly();

        return $this->sendResponse($org, 'Organization created', 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        $this->authorizeOrg($organization);

        return $this->sendResponse($organization->load(['users', 'subscriptions']));
    }

    public function switch(Request $request, Organization $organization): JsonResponse
    {
        if (! $request->user()->organizations()->where('organizations.id', $organization->id)->exists()) {
            return $this->sendError('Not a member of this organization.', [], 403);
        }

        $request->user()->forceFill(['current_organization_id' => $organization->id])->saveQuietly();
        CurrentOrganization::override($organization->id);

        return $this->sendResponse($organization, 'Organization switched');
    }

    public function invite(Request $request, Organization $organization): JsonResponse
    {
        $this->authorizeOrg($organization, ['owner', 'admin']);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,admin,member,viewer',
        ]);

        $organization->users()->syncWithoutDetaching([$validated['user_id'] => ['role' => $validated['role']]]);

        return $this->sendResponse([], 'Member added');
    }

    public function removeMember(Request $request, Organization $organization, int $userId): JsonResponse
    {
        $this->authorizeOrg($organization, ['owner', 'admin']);

        $organization->users()->detach($userId);

        return $this->sendResponse([], 'Member removed');
    }

    protected function authorizeOrg(Organization $organization, array $roles = []): void
    {
        $membership = $organization->users()->where('user_id', request()->user()->id)->first();

        abort_unless($membership, 403, 'Not a member.');

        if ($roles && ! in_array($membership->pivot->role, $roles, true)) {
            abort(403, 'Insufficient role.');
        }
    }
}
