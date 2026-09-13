<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $orgId = CurrentOrganization::id() ?? $request->user()->current_organization_id;

        $users = User::whereHas('organizations', function ($q) use ($orgId) {
            $q->where('organizations.id', $orgId);
        })->with(['roles', 'organizations'])->paginate($request->get('per_page', 15));

        return $this->sendPaginated($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles', 'organizations'])->findOrFail($id);

        return $this->sendResponse(new UserResource($user));
    }
}
