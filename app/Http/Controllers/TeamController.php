<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $teams = $user->getTeams();
        $ownedTeams = $user->getOwnedTeams();
        $pendingInvitations = $user->getPendingTeamInvitations();

        return view('pages.teams.index', compact('teams', 'ownedTeams', 'pendingInvitations'));
    }

    public function create(): View
    {
        $user = Auth::user();
        
        if (!$user->canCreateMoreTeams()) {
            return redirect()->route('teams.index')
                ->with('error', 'You have reached the maximum number of teams for your plan.');
        }

        return view('pages.teams.create');
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canCreateMoreTeams()) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of teams for your plan.',
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $team = Team::create([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'owner_id' => $user->id,
            ]);

            // Add owner as team member
            $team->addMember($user, 'owner');

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully!',
                'redirect_url' => route('teams.show', $team),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create team. Please try again.',
            ]);
        }
    }

    public function show(Team $team): View
    {
        $user = Auth::user();
        
        if (!$team->isMember($user)) {
            abort(403, 'You are not a member of this team.');
        }

        $team->load(['members.user', 'projects', 'invitations']);
        $userRole = $team->getMemberRole($user);
        $canManageTeam = $team->hasPermission($user, 'manage_team');
        $canInviteMembers = $team->hasPermission($user, 'invite_members');

        return view('pages.teams.show', compact(
            'team',
            'userRole',
            'canManageTeam',
            'canInviteMembers'
        ));
    }

    public function edit(Team $team): View
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'manage_team')) {
            abort(403, 'You do not have permission to edit this team.');
        }

        return view('pages.teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'manage_team')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this team.',
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $team->update([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team. Please try again.',
            ]);
        }
    }

    public function inviteMember(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'invite_members')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to invite members.',
            ]);
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,editor,viewer'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $invitation = $team->inviteUser(
                $request->input('email'),
                $request->input('role'),
                $user,
                $request->input('message')
            );

            // TODO: Send invitation email
            // Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function removeMember(Request $request, Team $team, User $member): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'remove_members')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to remove members.',
            ]);
        }

        // Cannot remove the owner
        if ($team->isOwner($member)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the team owner.',
            ]);
        }

        // Cannot remove yourself if you're the owner
        if ($team->isOwner($user) && $user->id === $member->id) {
            return response()->json([
                'success' => false,
                'message' => 'Team owner cannot leave the team. Transfer ownership first.',
            ]);
        }

        try {
            $team->removeMember($member);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member. Please try again.',
            ]);
        }
    }

    public function updateMemberRole(Request $request, Team $team, User $member): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'manage_team')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to manage team members.',
            ]);
        }

        $request->validate([
            'role' => ['required', 'in:owner,admin,editor,viewer'],
        ]);

        try {
            $team->updateMemberRole($member, $request->input('role'));

            return response()->json([
                'success' => true,
                'message' => 'Member role updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member role. Please try again.',
            ]);
        }
    }

    public function leave(Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this team.',
            ]);
        }

        // Cannot leave if you're the owner
        if ($team->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Team owner cannot leave the team. Transfer ownership first.',
            ]);
        }

        try {
            $team->removeMember($user);

            return response()->json([
                'success' => true,
                'message' => 'You have left the team successfully!',
                'redirect_url' => route('teams.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave team. Please try again.',
            ]);
        }
    }

    public function transferOwnership(Request $request, Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only the team owner can transfer ownership.',
            ]);
        }

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $newOwner = User::findOrFail($request->input('user_id'));

        if (!$team->isMember($newOwner)) {
            return response()->json([
                'success' => false,
                'message' => 'The selected user is not a member of this team.',
            ]);
        }

        try {
            // Update team owner
            $team->update(['owner_id' => $newOwner->id]);

            // Update roles
            $team->updateMemberRole($newOwner, 'owner');
            $team->updateMemberRole($user, 'admin');

            return response()->json([
                'success' => true,
                'message' => 'Ownership transferred successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer ownership. Please try again.',
            ]);
        }
    }

    public function acceptInvitation(string $token): View
    {
        $user = Auth::user();
        
        $invitation = TeamInvitation::where('token', $token)
            ->where('email', $user->email)
            ->valid()
            ->with('team')
            ->first();

        if (!$invitation) {
            abort(404, 'Invalid or expired invitation.');
        }

        // Check if user is already a member
        if ($invitation->team->isMember($user)) {
            return redirect()->route('teams.show', $invitation->team)
                ->with('info', 'You are already a member of this team.');
        }

        return view('pages.teams.accept-invitation', compact('invitation'));
    }

    public function processInvitation(string $token): JsonResponse
    {
        $user = Auth::user();
        
        $invitation = TeamInvitation::where('token', $token)
            ->where('email', $user->email)
            ->valid()
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired invitation.',
            ]);
        }

        try {
            $success = $invitation->team->acceptInvitation($token, $user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'You have joined the team successfully!',
                    'redirect_url' => route('teams.show', $invitation->team),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to join team. Please try again.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the invitation.',
            ]);
        }
    }

    public function declineInvitation(string $token): JsonResponse
    {
        $user = Auth::user();
        
        $invitation = TeamInvitation::where('token', $token)
            ->where('email', $user->email)
            ->valid()
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired invitation.',
            ]);
        }

        try {
            $invitation->decline();

            return response()->json([
                'success' => true,
                'message' => 'Invitation declined successfully!',
                'redirect_url' => route('teams.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline invitation. Please try again.',
            ]);
        }
    }

    public function destroy(Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->isOwner($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only the team owner can delete the team.',
            ]);
        }

        try {
            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team deleted successfully!',
                'redirect_url' => route('teams.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete team. Please try again.',
            ]);
        }
    }

    public function getInvitations(Team $team): JsonResponse
    {
        $user = Auth::user();
        
        if (!$team->hasPermission($user, 'invite_members')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view invitations.',
            ]);
        }

        $invitations = $team->invitations()
            ->with('invitedBy')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'invitations' => $invitations,
        ]);
    }

    public function resendInvitation(TeamInvitation $invitation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$invitation->team->hasPermission($user, 'invite_members')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to resend invitations.',
            ]);
        }

        try {
            $invitation->resend();

            // TODO: Send invitation email
            // Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

            return response()->json([
                'success' => true,
                'message' => 'Invitation resent successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invitation. Please try again.',
            ]);
        }
    }

    public function cancelInvitation(TeamInvitation $invitation): JsonResponse
    {
        $user = Auth::user();
        
        if (!$invitation->team->hasPermission($user, 'invite_members')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel invitations.',
            ]);
        }

        try {
            $invitation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invitation cancelled successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invitation. Please try again.',
            ]);
        }
    }
}
