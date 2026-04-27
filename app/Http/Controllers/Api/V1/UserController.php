<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['ownedTeams', 'teamMemberships.team', 'subscriptions.plan']);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $user->update($request->only([
            'name',
            'email',
            'location',
            'website',
            'bio',
        ]));

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'projects_count' => $user->projects()->count(),
            'owned_teams_count' => $user->ownedTeams()->count(),
            'team_memberships_count' => $user->teamMemberships()->where('is_active', true)->count(),
            'tasks_count' => $user->assignedTasks()->count(),
            'completed_tasks_count' => $user->assignedTasks()->where('status', 'completed')->count(),
            'subscription_status' => $user->hasActiveSubscription() ? 'active' : 'inactive',
            'current_plan' => $user->getPlanName(),
            'referral_stats' => $user->getReferralStats(),
            'created_at' => $user->created_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
