<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\BaseController;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->sendError('The provided credentials do not match our records.', [], 401);
        }

        if (! $user->is_active) {
            return $this->sendError('Account is deactivated.', [], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->load(['roles', 'organizations', 'currentOrganization']);

        return $this->sendResponse([
            'user' => array_merge($user->toArray(), ['permissions' => $user->getAllPermissions()->pluck('name')]),
            'token' => $token,
        ], 'Login successful');
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'organization_name' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        $orgName = $validated['organization_name'] ?? $validated['name']."'s Team";
        $org = Organization::create([
            'name' => $orgName,
            'slug' => Str::slug($orgName).'-'.Str::lower(Str::random(6)),
            'owner_id' => $user->id,
            'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
        ]);
        $org->users()->attach($user->id, ['role' => 'owner']);
        $user->forceFill(['current_organization_id' => $org->id])->saveQuietly();

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['organizations', 'currentOrganization']);

        return $this->sendResponse([
            'user' => array_merge($user->toArray(), ['permissions' => []]),
            'organization' => $org,
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->sendResponse([], 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'organizations', 'currentOrganization']);

        return $this->sendResponse(array_merge($user->toArray(), ['permissions' => $user->getAllPermissions()->pluck('name')]));
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
        ], 'Token refreshed successfully');
    }
}
