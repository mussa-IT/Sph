<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'icon',
    'category',
    'configuration_schema',
    'default_settings',
    'is_active',
    'is_beta',
    'requires_oauth',
    'oauth_provider',
    'oauth_scopes',
    'webhook_url',
    'supported_events',
    'supported_actions',
    'usage_count',
])]
#[Cast('configuration_schema', 'array')]
#[Cast('default_settings', 'array')]
#[Cast('oauth_scopes', 'array')]
#[Cast('supported_events', 'array')]
#[Cast('supported_actions', 'array')]
class Integration extends Model
{
    public function userIntegrations(): HasMany
    {
        return $this->hasMany(UserIntegration::class);
    }

    public function logs(): HasMany
    {
        return $this->hasManyThrough(IntegrationLog::class, UserIntegration::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasManyThrough(IntegrationWebhook::class, UserIntegration::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isBeta(): bool
    {
        return $this->is_beta;
    }

    public function requiresOAuth(): bool
    {
        return $this->requires_oauth;
    }

    public function getConfigurationSchema(): array
    {
        return $this->configuration_schema ?? [];
    }

    public function getDefaultSettings(): array
    {
        return $this->default_settings ?? [];
    }

    public function getSupportedEvents(): array
    {
        return $this->supported_events ?? [];
    }

    public function getSupportedActions(): array
    {
        return $this->supported_actions ?? [];
    }

    public function supportsEvent(string $event): bool
    {
        return in_array($event, $this->getSupportedEvents());
    }

    public function supportsAction(string $action): bool
    {
        return in_array($action, $this->getSupportedActions());
    }

    public function getOAuthScopes(): array
    {
        return $this->oauth_scopes ?? [];
    }

    public function getIconUrl(): string
    {
        return $this->icon ?: asset('images/integrations/default.png');
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBeta($query)
    {
        return $query->where('is_beta', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRequiresOAuth($query)
    {
        return $query->where('requires_oauth', true);
    }

    public function scopeByOAuthProvider($query, string $provider)
    {
        return $query->where('oauth_provider', $provider);
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('usage_count');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($integration) {
            if (empty($integration->slug)) {
                $integration->slug = \Str::slug($integration->name);
            }
        });

        static::updating(function ($integration) {
            if ($integration->isDirty('name') && empty($integration->slug)) {
                $integration->slug = \Str::slug($integration->name);
            }
        });
    }
}
