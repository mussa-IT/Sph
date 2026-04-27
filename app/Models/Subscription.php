<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'plan_id',
    'status',
    'billing_cycle',
    'price',
    'currency',
    'starts_at',
    'ends_at',
    'trial_ends_at',
    'cancelled_at',
    'payment_gateway',
    'gateway_subscription_id',
    'gateway_customer_id',
    'gateway_data',
    'auto_renew',
])]
#[Cast('gateway_data', 'array')]
class Subscription extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function billingHistory(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->ends_at || $this->ends_at->isFuture()) &&
               (!$this->trial_ends_at || $this->trial_ends_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->ends_at && $this->ends_at->isPast());
    }

    public function getTrialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) return 0;
        
        return max(0, $this->trial_ends_at->diffInDays(now()));
    }

    public function getDaysUntilRenewal(): int
    {
        if (!$this->ends_at) return 0;
        
        return max(0, $this->ends_at->diffInDays(now()));
    }

    public function getNextBillingDate(): ?\Carbon\Carbon
    {
        if ($this->isOnTrial()) {
            return $this->trial_ends_at;
        }
        
        return $this->ends_at;
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedBillingCycle(): string
    {
        return ucfirst($this->billing_cycle);
    }

    public function canAccessFeature(string $feature): bool
    {
        if (!$this->isActive() && !$this->isOnTrial()) {
            return false;
        }

        return $this->plan->hasFeature($feature);
    }

    public function getUsageLimit(string $limit): mixed
    {
        return $this->plan->getLimit($limit);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'trial');
        })->where(function ($q) {
            $q->whereNull('ends_at')
              ->orWhere('ends_at', '>', now());
        });
    }

    public static function getActiveSubscriptionForUser(User $user): ?self
    {
        return static::forUser($user)->current()->first();
    }

    public static function createSubscription(User $user, Plan $plan, string $billingCycle, array $gatewayData = []): self
    {
        $price = $plan->getPriceForBillingCycle($billingCycle);
        $startsAt = now();
        $trialEndsAt = $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null;
        $endsAt = $trialEndsAt ? $trialEndsAt : now()->addMonth();

        return static::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => $trialEndsAt ? 'trial' : 'active',
            'billing_cycle' => $billingCycle,
            'price' => $price,
            'currency' => 'USD',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
            'payment_gateway' => $gatewayData['gateway'] ?? 'stripe',
            'gateway_subscription_id' => $gatewayData['subscription_id'] ?? null,
            'gateway_customer_id' => $gatewayData['customer_id'] ?? null,
            'gateway_data' => $gatewayData,
            'auto_renew' => true,
        ]);
    }
}
