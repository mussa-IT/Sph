<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'description',
    'logo_url',
    'favicon_url',
    'primary_color',
    'secondary_color',
    'accent_color',
    'custom_domain',
    'custom_domain_active',
    'custom_css',
    'custom_js',
    'custom_footer_text',
    'custom_header_text',
    'remove_branding',
    'enable_white_label',
    'plan',
    'plan_limits',
    'settings',
    'owner_id',
    'is_active',
    'plan_expires_at',
])]
#[Cast('plan_limits', 'array')]
#[Cast('settings', 'array')]
#[Cast('custom_css', 'array')]
#[Cast('custom_js', 'array')]
class Organization extends Model
{
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    public function organizationSettings(): HasMany
    {
        return $this->hasMany(OrganizationSetting::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->where('is_active', true)->exists();
    }

    public function isAdmin(User $user): bool
    {
        $member = $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
        return $member && in_array($member->role, ['owner', 'admin']);
    }

    public function getMemberRole(User $user): ?string
    {
        $member = $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
        return $member ? $member->role : null;
    }

    public function hasPermission(User $user, string $permission): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        $member = $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
        if (!$member) {
            return false;
        }

        $permissions = $member->permissions ?? [];
        return in_array($permission, $permissions);
    }

    public function isWhiteLabelEnabled(): bool
    {
        return $this->enable_white_label && $this->plan !== 'free';
    }

    public function canRemoveBranding(): bool
    {
        return $this->remove_branding && in_array($this->plan, ['professional', 'enterprise']);
    }

    public function canUseCustomDomain(): bool
    {
        return $this->custom_domain && $this->plan === 'enterprise';
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function isFreePlan(): bool
    {
        return $this->plan === 'free';
    }

    public function isProfessionalPlan(): bool
    {
        return $this->plan === 'professional';
    }

    public function isEnterprisePlan(): bool
    {
        return $this->plan === 'enterprise';
    }

    public function getPlanLimits(): array
    {
        $defaultLimits = [
            'free' => [
                'members' => 5,
                'projects' => 10,
                'storage' => 1024, // MB
                'custom_domain' => false,
                'white_label' => false,
                'remove_branding' => false,
            ],
            'professional' => [
                'members' => 50,
                'projects' => 100,
                'storage' => 10240, // 10GB
                'custom_domain' => false,
                'white_label' => true,
                'remove_branding' => true,
            ],
            'enterprise' => [
                'members' => -1, // unlimited
                'projects' => -1, // unlimited
                'storage' => -1, // unlimited
                'custom_domain' => true,
                'white_label' => true,
                'remove_branding' => true,
            ],
        ];

        return array_merge(
            $defaultLimits[$this->plan] ?? $defaultLimits['free'],
            $this->plan_limits ?? []
        );
    }

    public function getLimit(string $key)
    {
        $limits = $this->getPlanLimits();
        return $limits[$key] ?? null;
    }

    public function hasReachedLimit(string $key): bool
    {
        $limit = $this->getLimit($key);
        
        if ($limit === -1) {
            return false; // unlimited
        }

        $current = match($key) {
            'members' => $this->members()->where('is_active', true)->count(),
            'projects' => $this->projects()->count(), // TODO: Implement projects relationship
            'storage' => $this->getStorageUsage(), // TODO: Implement storage usage calculation
            default => 0,
        };

        return $current >= $limit;
    }

    public function getStorageUsage(): int
    {
        // TODO: Implement storage usage calculation
        return 0;
    }

    public function getLogoUrl(): string
    {
        return $this->logo_url ?: asset('images/organization-logo-placeholder.png');
    }

    public function getFaviconUrl(): string
    {
        return $this->favicon_url ?: asset('favicon.ico');
    }

    public function getCustomCSS(): string
    {
        if (!$this->isWhiteLabelEnabled()) {
            return '';
        }

        $css = '';
        
        if ($this->custom_css) {
            foreach ($this->custom_css as $rule) {
                $css .= $rule . "\n";
            }
        }

        // Add color variables
        if ($this->primary_color || $this->secondary_color || $this->accent_color) {
            $css .= ":root {\n";
            if ($this->primary_color) {
                $css .= "  --primary-color: {$this->primary_color};\n";
            }
            if ($this->secondary_color) {
                $css .= "  --secondary-color: {$this->secondary_color};\n";
            }
            if ($this->accent_color) {
                $css .= "  --accent-color: {$this->accent_color};\n";
            }
            $css .= "}\n";
        }

        return $css;
    }

    public function getCustomJS(): string
    {
        if (!$this->isWhiteLabelEnabled()) {
            return '';
        }

        $js = '';
        
        if ($this->custom_js) {
            foreach ($this->custom_js as $script) {
                $js .= $script . "\n";
            }
        }

        return $js;
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->organizationSettings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, $value, string $type = 'string'): void
    {
        $this->organizationSettings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public function upgradePlan(string $newPlan): void
    {
        $this->update(['plan' => $newPlan]);
    }

    public function addMember(User $user, string $role = 'member'): OrganizationMember
    {
        return $this->members()->create([
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    public function removeMember(User $user): bool
    {
        if ($this->isOwner($user)) {
            return false; // Cannot remove owner
        }

        return $this->members()->where('user_id', $user->id)->delete() > 0;
    }

    public function inviteUser(string $email, string $role = 'member', User $invitedBy = null): OrganizationInvitation
    {
        return $this->invitations()->create([
            'email' => $email,
            'role' => $role,
            'token' => Str::random(32),
            'invited_by' => $invitedBy?->id ?? $this->owner_id,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    public function scopeByOwner($query, User $owner)
    {
        return $query->where('owner_id', $owner->id);
    }

    public function scopeWithCustomDomain($query)
    {
        return $query->whereNotNull('custom_domain')->where('custom_domain_active', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name) . '-' . time();
            }
        });

        static::updating(function ($organization) {
            if ($organization->isDirty('name') && empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name) . '-' . time();
            }
        });
    }
}
