<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\Plan;
use Illuminate\Support\Collection;

class UpgradePromptService
{
    public function getUpgradePrompts(User $user): Collection
    {
        $prompts = collect([]);

        // Check if user is on free plan
        if (!$user->hasActiveSubscription()) {
            $prompts->push($this->getFreePlanPrompt($user));
        }

        // Check project limit
        $projectPrompt = $this->getProjectLimitPrompt($user);
        if ($projectPrompt) {
            $prompts->push($projectPrompt);
        }

        // Check team limit
        $teamPrompt = $this->getTeamLimitPrompt($user);
        if ($teamPrompt) {
            $prompts->push($teamPrompt);
        }

        // Check AI usage limit
        $aiPrompt = $this->getAIUsagePrompt($user);
        if ($aiPrompt) {
            $prompts->push($aiPrompt);
        }

        // Check feature-specific prompts
        $featurePrompts = $this->getFeaturePrompts($user);
        $prompts = $prompts->merge($featurePrompts);

        return $prompts->sortBy('priority')->values();
    }

    private function getFreePlanPrompt(User $user): array
    {
        return [
            'id' => 'free_plan_upgrade',
            'type' => 'upgrade',
            'title' => 'Upgrade Your Plan',
            'message' => 'Unlock unlimited projects, advanced AI features, and team collaboration.',
            'priority' => 1,
            'action' => 'upgrade',
            'target_plan' => 'pro',
            'discount' => null,
            'urgency' => 'low',
        ];
    }

    private function getProjectLimitPrompt(User $user): ?array
    {
        $projectLimit = $user->getUsageLimit('projects');
        $projectCount = $user->projects()->count();

        if ($projectLimit && $projectCount >= $projectLimit) {
            return [
                'id' => 'project_limit',
                'type' => 'limit',
                'title' => 'Project Limit Reached',
                'message' => "You've reached your limit of {$projectLimit} projects. Upgrade to create unlimited projects.",
                'priority' => 2,
                'action' => 'upgrade',
                'target_plan' => 'pro',
                'discount' => null,
                'urgency' => 'high',
                'current_usage' => $projectCount,
                'limit' => $projectLimit,
            ];
        }

        if ($projectLimit && $projectCount >= ($projectLimit * 0.8)) {
            return [
                'id' => 'project_limit_warning',
                'type' => 'warning',
                'title' => 'Approaching Project Limit',
                'message' => "You're using {$projectCount} of {$projectLimit} projects. Upgrade for unlimited projects.",
                'priority' => 5,
                'action' => 'upgrade',
                'target_plan' => 'pro',
                'discount' => null,
                'urgency' => 'medium',
                'current_usage' => $projectCount,
                'limit' => $projectLimit,
            ];
        }

        return null;
    }

    private function getTeamLimitPrompt(User $user): ?array
    {
        $teamLimit = $user->getUsageLimit('teams');
        $teamCount = $user->ownedTeams()->count();

        if ($teamLimit && $teamCount >= $teamLimit) {
            return [
                'id' => 'team_limit',
                'type' => 'limit',
                'title' => 'Team Limit Reached',
                'message' => "You've reached your limit of {$teamLimit} teams. Upgrade to create unlimited teams.",
                'priority' => 3,
                'action' => 'upgrade',
                'target_plan' => 'team',
                'discount' => null,
                'urgency' => 'high',
                'current_usage' => $teamCount,
                'limit' => $teamLimit,
            ];
        }

        return null;
    }

    private function getAIUsagePrompt(User $user): ?array
    {
        $aiLimit = $user->getUsageLimit('ai_messages');
        
        if ($aiLimit && $aiLimit > 0) {
            // This would require tracking AI usage in a separate table
            // For now, we'll show a generic prompt
            return [
                'id' => 'ai_usage',
                'type' => 'feature',
                'title' => 'Unlock Advanced AI',
                'message' => 'Get unlimited AI-powered project generation and smart recommendations.',
                'priority' => 4,
                'action' => 'upgrade',
                'target_plan' => 'pro',
                'discount' => null,
                'urgency' => 'low',
            ];
        }

        return null;
    }

    private function getFeaturePrompts(User $user): Collection
    {
        $prompts = collect([]);

        // Check if user is trying to access advanced features
        if ($this->shouldPromptForAnalytics($user)) {
            $prompts->push([
                'id' => 'analytics_upgrade',
                'type' => 'feature',
                'title' => 'Unlock Advanced Analytics',
                'message' => 'Get detailed insights into your project performance and team productivity.',
                'priority' => 6,
                'action' => 'upgrade',
                'target_plan' => 'pro',
                'discount' => null,
                'urgency' => 'low',
            ]);
        }

        if ($this->shouldPromptForWeb3($user)) {
            $prompts->push([
                'id' => 'web3_upgrade',
                'type' => 'feature',
                'title' => 'Unlock Web3 Features',
                'message' => 'Enable blockchain verification, NFT badges, and onchain project ownership.',
                'priority' => 7,
                'action' => 'upgrade',
                'target_plan' => 'pro',
                'discount' => null,
                'urgency' => 'low',
            ]);
        }

        return $prompts;
    }

    private function shouldPromptForAnalytics(User $user): bool
    {
        // Check if user has visited analytics-related pages
        // This would require tracking page visits in a separate table
        return !$user->canAccessFeature('analytics') && $user->projects()->count() >= 3;
    }

    private function shouldPromptForWeb3(User $user): bool
    {
        // Check if user has shown interest in Web3 features
        return !$user->canAccessFeature('web3_features') && $user->projects()->count() >= 5;
    }

    public function getSmartUpgradeSuggestion(User $user): ?array
    {
        $currentPlan = $user->getPlan();
        
        if (!$currentPlan || $currentPlan->slug === 'free') {
            // Analyze user behavior to suggest the best plan
            $projectCount = $user->projects()->count();
            $teamCount = $user->ownedTeams()->count();
            
            if ($teamCount > 0) {
                return [
                    'recommended_plan' => 'team',
                    'reason' => 'You have teams and need collaboration features',
                    'benefits' => ['Unlimited teams', 'Advanced collaboration', 'Team analytics', 'Priority support'],
                ];
            } elseif ($projectCount > 3) {
                return [
                    'recommended_plan' => 'pro',
                    'reason' => 'You have multiple projects and need advanced features',
                    'benefits' => ['Unlimited projects', 'Advanced AI', 'Analytics', 'Web3 features'],
                ];
            } else {
                return [
                    'recommended_plan' => 'pro',
                    'reason' => 'Unlock powerful features to grow your projects',
                    'benefits' => ['Unlimited projects', 'Advanced AI', 'Analytics', 'Web3 features'],
                ];
            }
        }

        return null;
    }

    public function getTrialEligibility(User $user): ?array
    {
        // Check if user is eligible for a trial
        if ($user->hasActiveSubscription()) {
            return null;
        }

        $hasUsedTrialBefore = $user->subscriptions()
            ->where('status', 'trial')
            ->exists();

        if ($hasUsedTrialBefore) {
            return null;
        }

        return [
            'eligible' => true,
            'trial_days' => 14,
            'trial_plan' => 'pro',
            'message' => 'Try Pro plan free for 14 days. No credit card required.',
        ];
    }

    public function getDiscountOpportunity(User $user): ?array
    {
        // Check for special discount opportunities
        $projectCount = $user->projects()->count();
        
        if ($projectCount >= 10) {
            return [
                'type' => 'loyalty_discount',
                'discount' => 20,
                'message' => 'As a valued user with 10+ projects, get 20% off your first year!',
                'expires_at' => now()->addDays(7),
            ];
        }

        if ($user->created_at->diffInDays(now()) > 30 && !$user->hasActiveSubscription()) {
            return [
                'type' => 'welcome_back_discount',
                'discount' => 15,
                'message' => 'Welcome back! Get 15% off your first month as a thank you.',
                'expires_at' => now()->addDays(3),
            ];
        }

        return null;
    }

    public function shouldShowPrompt(User $user, string $context = null): bool
    {
        // Don't show prompts to users who recently upgraded
        $recentUpgrade = $user->subscriptions()
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($recentUpgrade) {
            return false;
        }

        // Don't show prompts too frequently
        $lastPromptShown = session('last_upgrade_prompt_shown');
        if ($lastPromptShown && now()->diffInHours($lastPromptShown) < 24) {
            return false;
        }

        // Check if there are relevant prompts
        $prompts = $this->getUpgradePrompts($user);
        
        if ($context) {
            return $prompts->contains('id', $context);
        }

        return $prompts->isNotEmpty();
    }

    public function dismissPrompt(User $user, string $promptId): void
    {
        session()->put("dismissed_prompt_{$promptId}", now());
        session()->put('last_upgrade_prompt_shown', now());
    }

    public function isPromptDismissed(User $user, string $promptId): bool
    {
        $dismissedAt = session("dismissed_prompt_{$promptId}");
        
        if (!$dismissedAt) {
            return false;
        }

        // Re-show prompts after 30 days
        return $dismissedAt->diffInDays(now()) < 30;
    }

    public function getPromptForContext(User $user, string $context): ?array
    {
        $prompts = $this->getUpgradePrompts($user);
        
        return $prompts->firstWhere('id', $context);
    }

    public function getPersonalizedMessage(User $user): string
    {
        $projectCount = $user->projects()->count();
        $currentPlan = $user->getPlanName();
        
        if ($projectCount === 0) {
            return "Start your journey with Smart Project Hub! Upgrade to unlock powerful features.";
        } elseif ($projectCount === 1) {
            return "You have 1 project! Upgrade to unlock advanced features and analytics.";
        } elseif ($projectCount < 5) {
            return "You have {$projectCount} projects! Upgrade to unlock unlimited projects and team collaboration.";
        } else {
            return "You're managing {$projectCount} projects! Upgrade to Pro for unlimited projects and advanced features.";
        }
    }
}
