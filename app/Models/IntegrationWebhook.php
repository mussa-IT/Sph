<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

#[Fillable([
    'user_integration_id',
    'event',
    'url',
    'secret',
    'headers',
    'is_active',
    'delivery_count',
    'failure_count',
    'last_delivered_at',
])]
#[Cast('headers', 'array')]
class IntegrationWebhook extends Model
{
    public function userIntegration(): BelongsTo
    {
        return $this->belongsTo(UserIntegration::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function enable(): void
    {
        $this->update(['is_active' => true]);
    }

    public function disable(): void
    {
        $this->update(['is_active' => false]);
    }

    public function incrementDelivery(): void
    {
        $this->increment('delivery_count');
        $this->update(['last_delivered_at' => now()]);
    }

    public function incrementFailure(): void
    {
        $this->increment('failure_count');
        
        // Auto-disable after too many failures
        if ($this->failure_count >= 5) {
            $this->disable();
        }
    }

    public function getSuccessRate(): float
    {
        $total = $this->delivery_count + $this->failure_count;
        return $total > 0 ? ($this->delivery_count / $total) * 100 : 0;
    }

    public function deliver(array $payload): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        try {
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'SmartProjectHub-Webhook/1.0',
            ], $this->headers ?? []);

            if ($this->secret) {
                $signature = hash_hmac('sha256', json_encode($payload), $this->secret);
                $headers['X-Webhook-Signature'] = "sha256={$signature}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($this->url, $payload);

            if ($response->successful()) {
                $this->incrementDelivery();
                return true;
            } else {
                $this->incrementFailure();
                return false;
            }
        } catch (\Exception $e) {
            $this->incrementFailure();
            return false;
        }
    }

    public function test(): bool
    {
        $testPayload = [
            'test' => true,
            'event' => $this->event,
            'timestamp' => now()->toISOString(),
            'integration' => $this->userIntegration->integration->name,
        ];

        return $this->deliver($testPayload);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByUserIntegration($query, UserIntegration $userIntegration)
    {
        return $query->where('user_integration_id', $userIntegration->id);
    }
}
