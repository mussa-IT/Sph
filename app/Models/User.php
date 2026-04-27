<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\BrandedResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'email',
    'password',
    'preferred_locale',
    'timezone',
    'receive_product_updates',
    'receive_marketing_emails',
    'avatar_path',
    'bio',
    'google_id',
    'location',
    'website',
    'theme_preference',
    'compact_mode',
    'comfortable_spacing',
    'sidebar_collapsed_default',
    'two_factor_enabled',
    'two_factor_channel',
    'role',
    'suspended',
    'wallet_address',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function billingHistory(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'invited_by');
    }

    public function receivedTeamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'email', 'email');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class);
    }

    public function readMentions(): HasMany
    {
        return $this->hasMany(ReadMention::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_user_id');
    }

    public function activeReferrals(): HasMany
    {
        return $this->referrals()->active();
    }

    public function convertedReferrals(): HasMany
    {
        return $this->referrals()->converted();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'receive_product_updates' => 'boolean',
            'receive_marketing_emails' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'suspended' => 'boolean',
        ];
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (filled($this->avatar_path)) {
                return Storage::disk('public')->url((string) $this->avatar_path);
            }

            return '';
        });
    }

    public function isAdmin(): bool
    {
        if (in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true)) {
            return true;
        }

        $adminEmails = config('security.admin_emails', []);

        if (!is_array($adminEmails) || $adminEmails === []) {
            return false;
        }

        return in_array(strtolower((string) $this->email), $adminEmails, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new BrandedResetPasswordNotification((string) $token));
    }

    public function getCurrentSubscription(): ?Subscription
    {
        return Subscription::getActiveSubscriptionForUser($this);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->getCurrentSubscription() !== null;
    }

    public function isOnTrial(): bool
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->isOnTrial() ?? false;
    }

    public function canAccessFeature(string $feature): bool
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->canAccessFeature($feature) ?? false;
    }

    public function getUsageLimit(string $limit): mixed
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->getUsageLimit($limit) ?? null;
    }

    public function getPlan(): ?Plan
    {
        return $this->getCurrentSubscription()?->plan;
    }

    public function getPlanName(): string
    {
        return $this->getPlan()?->name ?? 'Free';
    }

    public function isSubscribedToPlan(string $planSlug): bool
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->plan?->slug === $planSlug ?? false;
    }

    public function getTrialDaysRemaining(): int
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->getTrialDaysRemaining() ?? 0;
    }

    public function getDaysUntilRenewal(): int
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->getDaysUntilRenewal() ?? 0;
    }

    public function getNextBillingDate(): ?\Carbon\Carbon
    {
        $subscription = $this->getCurrentSubscription();
        return $subscription?->getNextBillingDate();
    }

    public function getTeams(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->teamMemberships()
            ->with('team')
            ->where('is_active', true)
            ->get()
            ->map(function ($membership) {
                return $membership->team;
            });
    }

    public function getOwnedTeams(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->ownedTeams()->active()->get();
    }

    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->owner_id;
    }

    public function isTeamMember(Team $team): bool
    {
        return $this->teamMemberships()
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->exists();
    }

    public function getTeamRole(Team $team): ?string
    {
        $membership = $this->teamMemberships()
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->first();

        return $membership?->role;
    }

    public function hasTeamPermission(Team $team, string $permission): bool
    {
        return $team->hasPermission($this, $permission);
    }

    public function getPendingTeamInvitations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->receivedTeamInvitations()
            ->valid()
            ->with('team', 'invitedBy')
            ->get();
    }

    public function getTeamInvitationCount(): int
    {
        return $this->receivedTeamInvitations()->valid()->count();
    }

    public function canCreateMoreTeams(): bool
    {
        $limit = $this->getUsageLimit('teams');
        
        if ($limit === null || $limit === -1) {
            return true;
        }

        return $this->ownedTeams()->count() < $limit;
    }

    public function canJoinMoreTeams(): bool
    {
        $limit = $this->getUsageLimit('team_memberships');
        
        if ($limit === null || $limit === -1) {
            return true;
        }

        return $this->teamMemberships()->where('is_active', true)->count() < $limit;
    }

    public function getReferralCode(): string
    {
        $referral = $this->referrals()->active()->first();
        
        if (!$referral) {
            $referral = Referral::createReferral($this);
        }

        return $referral->referral_code;
    }

    public function getReferralUrl(): string
    {
        return url('/ref/' . $this->getReferralCode());
    }

    public function getReferralStats(): array
    {
        $totalReferrals = $this->referrals()->count();
        $activeReferrals = $this->activeReferrals()->count();
        $convertedReferrals = $this->convertedReferrals()->count();
        $pendingReferrals = $this->referrals()->pending()->count();
        $totalEarnings = $this->convertedReferrals()->sum('reward_amount');

        return [
            'total_referrals' => $totalReferrals,
            'active_referrals' => $activeReferrals,
            'converted_referrals' => $convertedReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_earnings' => $totalEarnings,
            'conversion_rate' => $totalReferrals > 0 ? ($convertedReferrals / $totalReferrals) * 100 : 0,
        ];
    }

    public function wasReferred(): bool
    {
        return $this->referredBy()->exists();
    }

    public function getReferredBy(): ?Referral
    {
        return $this->referredBy()->first();
    }

    public function canCreateReferrals(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function getTotalReferralEarnings(): float
    {
        return $this->convertedReferrals()->sum('reward_amount');
    }

    public function getPendingReferralEarnings(): float
    {
        return $this->referrals()->registered()->sum('reward_amount');
    }
}
