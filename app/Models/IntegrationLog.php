<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_integration_id',
    'event_type',
    'action',
    'payload',
    'response',
    'status',
    'error_message',
    'duration_ms',
])]
#[Cast('payload', 'array')]
#[Cast('response', 'array')]
class IntegrationLog extends Model
{
    public function userIntegration(): BelongsTo
    {
        return $this->belongsTo(UserIntegration::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function markAsSuccess(array $response = [], int $duration = null): void
    {
        $this->update([
            'status' => 'success',
            'response' => $response,
            'duration_ms' => $duration,
        ]);
    }

    public function markAsError(string $errorMessage, array $response = [], int $duration = null): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
            'response' => $response,
            'duration_ms' => $duration,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
