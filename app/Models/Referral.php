<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'referrer_id',
    'referred_user_id',
    'referral_code',
    'referred_email',
    'status',
    'reward_amount',
    'reward_type',
    'registered_at',
    'converted_at',
    'expires_at',
    'reward_data',
    'notes',
])]
#[Cast('reward_data', 'array')]
class Referral extends Model
{
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'registered' => 'Registered',
            'converted' => 'Converted',
            'expired' => 'Expired',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'registered' => 'info',
            'converted' => 'success',
            'expired' => 'muted',
            default => 'muted',
        };
    }

    public function getRewardLabel(): string
    {
        return match($this->reward_type) {
            'credit' => '$' . number_format($this->reward_amount, 2) . ' Credit',
            'discount' => '$' . number_format($this->reward_amount, 2) . ' Discount',
            'upgrade' => 'Free Upgrade',
            'trial' => 'Extended Trial',
            default => ucfirst($this->reward_type),
        };
    }

    public function getReferralUrl(): string
    {
        return route('referrals.accept', $this->referral_code);
    }

    public function getShareUrl(): string
    {
        return url('/ref/' . $this->referral_code);
    }

    public function getDaysUntilExpiry(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return max(0, $this->expires_at->diffInDays(now()));
    }

    public function markAsRegistered(User $user): void
    {
        $this->update([
            'referred_user_id' => $user->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);
    }

    public function markAsConverted(): void
    {
        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        // Apply reward to referrer
        $this->applyReward();
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    private function applyReward(): void
    {
        $referrer = $this->referrer;
        
        if (!$referrer) {
            return;
        }

        switch ($this->reward_type) {
            case 'credit':
                // Add credit to user account
                $this->addCreditToUser($referrer);
                break;
                
            case 'discount':
                // Create discount coupon
                $this->createDiscountCoupon($referrer);
                break;
                
            case 'upgrade':
                // Upgrade user's plan
                $this->upgradeUserPlan($referrer);
                break;
                
            case 'trial':
                // Extend trial period
                $this->extendUserTrial($referrer);
                break;
        }
    }

    private function addCreditToUser(User $user): void
    {
        // Implementation for adding credit to user account
        // This could be stored in user credits table or billing history
    }

    private function createDiscountCoupon(User $user): void
    {
        // Implementation for creating discount coupon
        // This would create a coupon in payment system
    }

    private function upgradeUserPlan(User $user): void
    {
        // Implementation for upgrading user's plan
        // This would upgrade user to next plan level
    }

    private function extendUserTrial(User $user): void
    {
        // Implementation for extending trial period
        // This would extend user's trial by specified days
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('expires_at', '<', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForReferrer($query, User $user)
    {
        return $query->where('referrer_id', $user->id);
    }

    public function scopeForReferredUser($query, User $user)
    {
        return $query->where('referred_user_id', $user->id);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('referral_code', $code);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public static function createReferral(User $referrer, array $data = []): self
    {
        return static::create([
            'referrer_id' => $referrer->id,
            'referral_code' => static::generateReferralCode(),
            'referred_email' => $data['referred_email'] ?? null,
            'reward_amount' => $data['reward_amount'] ?? 10.00,
            'reward_type' => $data['reward_type'] ?? 'credit',
            'expires_at' => now()->addDays(30),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('referral_code', $code)
            ->active()
            ->first();
    }

    public static function processReferral(string $code, User $user): ?self
    {
        $referral = static::findByCode($code);
        
        if (!$referral) {
            return null;
        }

        // Check if user is already registered
        if ($referral->referred_user_id) {
            return null;
        }

        // Mark as registered
        $referral->markAsRegistered($user);

        return $referral;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referral) {
            if (empty($referral->referral_code)) {
                $referral->referral_code = static::generateReferralCode();
            }
        });

        static::updated(function ($referral) {
            // Auto-expire expired referrals
            if ($referral->isExpired() && $referral->status !== 'expired') {
                $referral->markAsExpired();
            }
        });
    }
}
