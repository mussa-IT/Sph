<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'user_id',
    'integration_id',
    'team_id',
    'settings',
    'credentials',
    'is_enabled',
    'status',
    'error_message',
    'last_sync_at',
    'expires_at',
])]
#[Cast('settings', 'array')]
#[Cast('credentials', 'array')]
class UserIntegration extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(IntegrationWebhook::class);
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function isDisconnected(): bool
    {
        return $this->status === 'disconnected';
    }

    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getSettings(): array
    {
        $defaultSettings = $this->integration->getDefaultSettings();
        return array_merge($defaultSettings, $this->settings ?? []);
    }

    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
    }

    public function getCredentials(): array
    {
        if (empty($this->credentials)) {
            return [];
        }

        // Decrypt credentials if they're encrypted
        try {
            return array_map(function ($value) {
                return is_string($value) ? Crypt::decrypt($value) : $value;
            }, $this->credentials);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function setCredentials(array $credentials): void
    {
        // Encrypt sensitive credentials
        $encrypted = array_map(function ($value) {
            return is_string($value) ? Crypt::encrypt($value) : $value;
        }, $credentials);

        $this->credentials = $encrypted;
    }

    public function getAccessToken(): ?string
    {
        $credentials = $this->getCredentials();
        return $credentials['access_token'] ?? null;
    }

    public function getRefreshToken(): ?string
    {
        $credentials = $this->getCredentials();
        return $credentials['refresh_token'] ?? null;
    }

    public function connect(array $credentials): void
    {
        $this->setCredentials($credentials);
        $this->update([
            'status' => 'connected',
            'error_message' => null,
            'last_sync_at' => now(),
        ]);

        $this->integration->incrementUsage();
    }

    public function disconnect(): void
    {
        $this->update([
            'status' => 'disconnected',
            'credentials' => null,
            'error_message' => null,
        ]);
    }

    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => 'connected',
            'error_message' => null,
            'last_sync_at' => now(),
        ]);
    }

    public function enable(): void
    {
        $this->update(['is_enabled' => true]);
    }

    public function disable(): void
    {
        $this->update(['is_enabled' => false]);
    }

    public function sync(): bool
    {
        // TODO: Implement integration-specific sync logic
        $this->update(['last_sync_at' => now()]);
        return true;
    }

    public function canSync(): bool
    {
        return $this->isEnabled() && $this->isConnected() && !$this->isExpired();
    }

    public function logEvent(string $eventType, string $action = null, array $payload = [], array $response = []): IntegrationLog
    {
        return $this->logs()->create([
            'event_type' => $eventType,
            'action' => $action,
            'payload' => $payload,
            'response' => $response,
            'status' => 'pending',
        ]);
    }

    public function createWebhook(string $event, string $url, array $headers = []): IntegrationWebhook
    {
        return $this->webhooks()->create([
            'event' => $event,
            'url' => $url,
            'secret' => \Str::random(32),
            'headers' => $headers,
        ]);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByTeam($query, Team $team)
    {
        return $query->where('team_id', $team->id);
    }

    public function scopeByIntegration($query, Integration $integration)
    {
        return $query->where('integration_id', $integration->id);
    }
}
