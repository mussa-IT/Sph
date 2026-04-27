<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;

class TrialService
{
    public function canStartTrial(User $user): bool
    {
        // Check if user has already used a trial
        $hasUsedTrial = $user->subscriptions()
            ->where('status', 'trial')
            ->exists();

        if ($hasUsedTrial) {
            return false;
        }

        // Check if user has an active subscription
        if ($user->hasActiveSubscription()) {
            return false;
        }

        return true;
    }

    public function startTrial(User $user, Plan $plan = null): ?Subscription
    {
        if (!$this->canStartTrial($user)) {
            return null;
        }

        // Use Pro plan as default if none specified
        $plan = $plan ?: Plan::findBySlug('pro');
        
        if (!$plan || $plan->trial_days === 0) {
            return null;
        }

        $trialEndsAt = now()->addDays($plan->trial_days);

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'billing_cycle' => 'monthly',
            'price' => 0,
            'currency' => 'USD',
            'starts_at' => now(),
            'ends_at' => $trialEndsAt,
            'trial_ends_at' => $trialEndsAt,
            'payment_gateway' => 'trial',
            'auto_renew' => false,
        ]);
    }

    public function getTrialStatus(User $user): ?array
    {
        $subscription = $user->subscriptions()
            ->where('status', 'trial')
            ->latest()
            ->first();

        if (!$subscription) {
            return null;
        }

        $daysRemaining = $subscription->getTrialDaysRemaining();
        $isExpired = $subscription->isExpired();

        return [
            'is_active' => !$isExpired,
            'days_remaining' => $daysRemaining,
            'ends_at' => $subscription->trial_ends_at,
            'plan_name' => $subscription->plan->name,
            'plan_slug' => $subscription->plan->slug,
            'auto_renew' => $subscription->auto_renew,
            'is_expired' => $isExpired,
            'can_convert' => !$isExpired,
        ];
    }

    public function convertTrialToPaid(User $user, string $billingCycle = 'monthly'): ?Subscription
    {
        $trialSubscription = $user->subscriptions()
            ->where('status', 'trial')
            ->latest()
            ->first();

        if (!$trialSubscription || $trialSubscription->isExpired()) {
            return null;
        }

        $plan = $trialSubscription->plan;
        $price = $plan->getPriceForBillingCycle($billingCycle);

        // Create new paid subscription
        $paidSubscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => $billingCycle,
            'price' => $price,
            'currency' => 'USD',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'payment_gateway' => 'trial_conversion',
            'auto_renew' => true,
        ]);

        // Cancel the trial
        $trialSubscription->update([
            'status' => 'converted',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return $paidSubscription;
    }

    public function extendTrial(User $user, int $additionalDays): bool
    {
        $trialSubscription = $user->subscriptions()
            ->where('status', 'trial')
            ->latest()
            ->first();

        if (!$trialSubscription) {
            return false;
        }

        $newEndsAt = $trialSubscription->trial_ends_at->addDays($additionalDays);
        $trialSubscription->update([
            'trial_ends_at' => $newEndsAt,
            'ends_at' => $newEndsAt,
        ]);

        return true;
    }

    public function cancelTrial(User $user): bool
    {
        $trialSubscription = $user->subscriptions()
            ->where('status', 'trial')
            ->latest()
            ->first();

        if (!$trialSubscription) {
            return false;
        }

        $trialSubscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
            'ends_at' => now(), // End trial immediately
        ]);

        return true;
    }

    public function getTrialConversionRate(): float
    {
        $totalTrials = Subscription::where('status', 'trial')->count();
        $convertedTrials = Subscription::where('status', 'converted')->count();

        if ($totalTrials === 0) {
            return 0;
        }

        return ($convertedTrials / $totalTrials) * 100;
    }

    public function getTrialStats(): array
    {
        $activeTrials = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '>', now())
            ->count();

        $expiredTrials = Subscription::where('status', 'trial')
            ->where('trial_ends_at', '<=', now())
            ->count();

        $convertedTrials = Subscription::where('status', 'converted')->count();
        $cancelledTrials = Subscription::where('status', 'cancelled')
            ->where('payment_gateway', 'trial')
            ->count();

        $conversionRate = $this->getTrialConversionRate();

        return [
            'active_trials' => $activeTrials,
            'expired_trials' => $expiredTrials,
            'converted_trials' => $convertedTrials,
            'cancelled_trials' => $cancelledTrials,
            'conversion_rate' => $conversionRate,
            'total_trials' => $activeTrials + $expiredTrials + $convertedTrials + $cancelledTrials,
        ];
    }

    public function getExpiringTrials(int $daysThreshold = 3): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('status', 'trial')
            ->where('trial_ends_at', '<=', now()->addDays($daysThreshold))
            ->where('trial_ends_at', '>', now())
            ->with('user', 'plan')
            ->orderBy('trial_ends_at', 'asc')
            ->get();
    }

    public function sendTrialExpiryReminders(): void
    {
        $expiringTrials = $this->getExpiringTrials(3);

        foreach ($expiringTrials as $trial) {
            $daysRemaining = $trial->getTrialDaysRemaining();
            
            if ($daysRemaining === 3) {
                // Send 3-day reminder
                // TODO: Send email notification
                $this->sendTrialExpiryEmail($trial, 3);
            } elseif ($daysRemaining === 1) {
                // Send 1-day reminder
                // TODO: Send email notification
                $this->sendTrialExpiryEmail($trial, 1);
            }
        }
    }

    private function sendTrialExpiryEmail(Subscription $trial, int $daysRemaining): void
    {
        // TODO: Implement email sending
        // This would send an email reminder about trial expiry
        // with upgrade options and benefits
    }

    public function getTrialBenefits(): array
    {
        return [
            'Unlimited projects',
            'Advanced AI features',
            'Team collaboration',
            'Analytics dashboard',
            'Priority support',
            'Web3 integration',
            'No credit card required',
        ];
    }

    public function getTrialUpgradeIncentives(): array
    {
        return [
            '20% discount on first month',
            'Free additional trial days',
            'Priority support setup',
            'Personal onboarding session',
        ];
    }

    public function shouldShowTrialPrompt(User $user): bool
    {
        // Don't show if user has active subscription
        if ($user->hasActiveSubscription()) {
            return false;
        }

        // Don't show if user has already used trial
        if (!$this->canStartTrial($user)) {
            return false;
        }

        // Don't show if user recently dismissed trial prompt
        $lastDismissed = session('trial_prompt_dismissed');
        if ($lastDismissed && now()->diffInDays($lastDismissed) < 7) {
            return false;
        }

        return true;
    }

    public function getTrialEligibility(User $user): array
    {
        $canStart = $this->canStartTrial($user);
        $reason = null;

        if (!$canStart) {
            $hasActiveSubscription = $user->hasActiveSubscription();
            $hasUsedTrial = $user->subscriptions()
                ->where('status', 'trial')
                ->exists();

            if ($hasActiveSubscription) {
                $reason = 'You already have an active subscription.';
            } elseif ($hasUsedTrial) {
                $reason = 'You have already used your free trial.';
            }
        }

        return [
            'eligible' => $canStart,
            'reason' => $reason,
            'trial_days' => 14,
            'trial_plan' => 'pro',
        ];
    }
}
