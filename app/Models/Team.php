<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'description',
    'avatar_url',
    'settings',
    'owner_id',
    'is_active',
    'trial_ends_at',
])]
#[Cast('settings', 'array')]
class Team extends Model
{
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getMemberCount(): int
    {
        return $this->members()->where('is_active', true)->count();
    }

    public function getPendingInvitationsCount(): int
    {
        return $this->invitations()->where('status', 'pending')->count();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('is_active', true)->exists();
    }

    public function getMemberRole(User $user): ?string
    {
        $member = $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
        return $member?->role;
    }

    public function hasPermission(User $user, string $permission): bool
    {
        $member = $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
        
        if (!$member) {
            return false;
        }

        // Owner has all permissions
        if ($member->role === 'owner') {
            return true;
        }

        // Check role-based permissions
        $rolePermissions = $this->getRolePermissions($member->role);
        
        return in_array($permission, $rolePermissions) || 
               in_array($permission, $member->permissions ?? []);
    }

    private function getRolePermissions(string $role): array
    {
        return match($role) {
            'owner' => [
                'manage_team',
                'invite_members',
                'remove_members',
                'manage_projects',
                'view_analytics',
                'manage_billing',
            ],
            'admin' => [
                'manage_team',
                'invite_members',
                'remove_members',
                'manage_projects',
                'view_analytics',
            ],
            'editor' => [
                'manage_projects',
                'view_analytics',
            ],
            'viewer' => [
                'view_projects',
                'view_analytics',
            ],
            default => [],
        };
    }

    public function addMember(User $user, string $role = 'member'): TeamMember
    {
        return $this->members()->create([
            'user_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
            'accepted_at' => now(),
        ]);
    }

    public function removeMember(User $user): bool
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        
        if ($member) {
            return $member->delete();
        }
        
        return false;
    }

    public function updateMemberRole(User $user, string $role): bool
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        
        if ($member) {
            $member->update(['role' => $role]);
            return true;
        }
        
        return false;
    }

    public function inviteUser(string $email, string $role = 'member', User $invitedBy = null, string $message = null): TeamInvitation
    {
        // Check if user is already a member
        $user = User::where('email', $email)->first();
        if ($user && $this->isMember($user)) {
            throw new \Exception('User is already a member of this team');
        }

        // Check if there's already a pending invitation
        $existingInvitation = $this->invitations()
            ->where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvitation) {
            throw new \Exception('Invitation already sent to this email');
        }

        return $this->invitations()->create([
            'email' => $email,
            'role' => $role,
            'invited_by' => $invitedBy?->id ?? $this->owner_id,
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
            'message' => $message,
        ]);
    }

    public function acceptInvitation(string $token, User $user): bool
    {
        $invitation = $this->invitations()
            ->where('token', $token)
            ->where('email', $user->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return false;
        }

        // Add user as team member
        $this->addMember($user, $invitation->role);

        // Update invitation status
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return true;
    }

    public function declineInvitation(string $token, User $user): bool
    {
        $invitation = $this->invitations()
            ->where('token', $token)
            ->where('email', $user->email)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return false;
        }

        $invitation->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);

        return true;
    }

    public function getActiveMembers()
    {
        return $this->members()
            ->where('is_active', true)
            ->with('user')
            ->get();
    }

    public function getProjects()
    {
        return $this->projects()
            ->with('user', 'tasks', 'budgets')
            ->latest()
            ->get();
    }

    public function getSettings(): array
    {
        return $this->settings ?? [
            'allow_public_projects' => false,
            'require_approval_for_projects' => false,
            'default_project_visibility' => 'team',
            'max_members' => null,
            'allow_member_invites' => true,
        ];
    }

    public function updateSetting(string $key, mixed $value): void
    {
        $settings = $this->getSettings();
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getTrialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_active', true);
        });
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('owner_id', $user->id);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if (empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });

        static::updating(function ($team) {
            if ($team->isDirty('name') && empty($team->slug)) {
                $team->slug = Str::slug($team->name);
            }
        });
    }
}
