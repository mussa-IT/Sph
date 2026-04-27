<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'team_id',
    'user_id',
    'role',
    'permissions',
    'joined_at',
    'invited_at',
    'accepted_at',
    'is_active',
])]
#[Cast('permissions', 'array')]
class TeamMember extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function hasPermission(string $permission): bool
    {
        // Owner has all permissions
        if ($this->isOwner()) {
            return true;
        }

        // Check role-based permissions
        $rolePermissions = $this->getRolePermissions($this->role);
        
        return in_array($permission, $rolePermissions) || 
               in_array($permission, $this->permissions ?? []);
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

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'owner' => 'Owner',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
            default => 'Member',
        };
    }

    public function getRoleColor(): string
    {
        return match($this->role) {
            'owner' => 'danger',
            'admin' => 'warning',
            'editor' => 'info',
            'viewer' => 'muted',
            default => 'muted',
        };
    }

    public function getRoleIcon(): string
    {
        return match($this->role) {
            'owner' => '👑',
            'admin' => '🛡️',
            'editor' => '✏️',
            'viewer' => '👁️',
            default => '👤',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeEditors($query)
    {
        return $query->where('role', 'editor');
    }

    public function scopeViewers($query)
    {
        return $query->where('role', 'viewer');
    }

    public function scopeForTeam($query, Team $team)
    {
        return $query->where('team_id', $team->id);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function updateRole(string $role): void
    {
        $this->update(['role' => $role]);
    }

    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $key = array_search($permission, $permissions);
        if ($key !== false) {
            unset($permissions[$key]);
            $this->update(['permissions' => array_values($permissions)]);
        }
    }

    public function getJoinedDate(): string
    {
        return $this->joined_at?->format('M j, Y') ?? '';
    }

    public function getJoinedDaysAgo(): int
    {
        if (!$this->joined_at) {
            return 0;
        }

        return $this->joined_at->diffInDays(now());
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function acceptInvitation(): void
    {
        $this->update([
            'accepted_at' => now(),
            'is_active' => true,
        ]);
    }

    public function leave(): void
    {
        $this->update([
            'is_active' => false,
        ]);
    }
}
