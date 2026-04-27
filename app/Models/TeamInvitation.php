<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'team_id',
    'invited_by',
    'email',
    'role',
    'token',
    'status',
    'expires_at',
    'accepted_at',
    'declined_at',
    'message',
])]
class TeamInvitation extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    public function isValid(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'expired' => 'Expired',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'accepted' => 'success',
            'declined' => 'danger',
            'expired' => 'muted',
            default => 'muted',
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

    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }

    public function expire(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    public function resend(): void
    {
        $this->update([
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function getDaysUntilExpiry(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return max(0, $this->expires_at->diffInDays(now()));
    }

    public function getFormattedExpiryDate(): string
    {
        return $this->expires_at?->format('M j, Y') ?? '';
    }

    public function getFormattedSentDate(): string
    {
        return $this->created_at->format('M j, Y');
    }

    public function getInviteUrl(): string
    {
        return route('teams.invitations.accept', $this->token);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('expires_at', '<', now());
            });
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    public function scopeForTeam($query, Team $team)
    {
        return $query->where('team_id', $team->id);
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeInvitedBy($query, User $user)
    {
        return $query->where('invited_by', $user->id);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(32);
            }
            
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }
}
