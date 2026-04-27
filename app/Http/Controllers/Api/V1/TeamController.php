<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Http\Resources\TeamMemberResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $teams = $user->getTeams()
            ->with(['owner', 'members.user'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => TeamResource::collection($teams->items()),
            'meta' => [
                'current_page' => $teams->currentPage(),
                'per_page' => $teams->perPage(),
                'total' => $teams->total(),
                'last_page' => $teams->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => \Str::slug($request->name) . '-' . time(),
            'owner_id' => $request->user()->id,
        ]);

        // Add owner as member
        $team->members()->create([
            'user_id' => $request->user()->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team->load(['owner', 'members'])),
            'message' => 'Team created successfully.',
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        if (!$team->isMember($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $team->load(['owner', 'members.user', 'projects', 'invitations']);

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team),
        ]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        if (!$team->hasPermission($request->user(), 'edit')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $team->update($request->only(['name', 'description']));

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team->load(['owner', 'members'])),
            'message' => 'Team updated successfully.',
        ]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        if (!$team->isOwner($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully.',
        ]);
    }

    public function members(Request $request, Team $team): JsonResponse
    {
        if (!$team->isMember($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $members = $team->members()
            ->with('user')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => TeamMemberResource::collection($members),
        ]);
    }

    public function invite(Request $request, Team $team): JsonResponse
    {
        if (!$team->hasPermission($request->user(), 'invite')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,editor,viewer'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        // Check if user is already a member
        if ($team->isMemberByEmail($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this team.',
            ], Response::HTTP_CONFLICT);
        }

        // Check if invitation already exists
        if ($team->hasPendingInvitation($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation already sent to this email.',
            ], Response::HTTP_CONFLICT);
        }

        $invitation = $team->invitations()->create([
            'email' => $request->email,
            'role' => $request->role,
            'token' => \Str::random(32),
            'expires_at' => now()->addDays(7),
            'invited_by' => $request->user()->id,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'data' => $invitation,
            'message' => 'Invitation sent successfully.',
        ], Response::HTTP_CREATED);
    }

    public function leave(Request $request, Team $team): JsonResponse
    {
        if (!$team->isMember($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this team.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($team->isOwner($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Team owners cannot leave the team.',
            ], Response::HTTP_FORBIDDEN);
        }

        $team->members()->where('user_id', $request->user()->id)->update([
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have left the team successfully.',
        ]);
    }
}
