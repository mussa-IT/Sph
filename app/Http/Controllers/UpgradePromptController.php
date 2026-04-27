<?php

namespace App\Http\Controllers;

use App\Services\UpgradePromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpgradePromptController extends Controller
{
    public function __construct(
        private UpgradePromptService $upgradePromptService
    ) {}

    public function getPrompts(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $prompts = $this->upgradePromptService->getUpgradePrompts($user);

        return response()->json([
            'success' => true,
            'prompts' => $prompts,
        ]);
    }

    public function dismiss(string $promptId): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $this->upgradePromptService->dismissPrompt($user, $promptId);

        return response()->json([
            'success' => true,
            'message' => 'Prompt dismissed successfully.',
        ]);
    }

    public function getSmartSuggestion(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $suggestion = $this->upgradePromptService->getSmartUpgradeSuggestion($user);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion,
        ]);
    }

    public function getTrialEligibility(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $eligibility = $this->upgradePromptService->getTrialEligibility($user);

        return response()->json([
            'success' => true,
            'eligibility' => $eligibility,
        ]);
    }

    public function getDiscountOpportunity(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $discount = $this->upgradePromptService->getDiscountOpportunity($user);

        return response()->json([
            'success' => true,
            'discount' => $discount,
        ]);
    }

    public function getContextualPrompt(string $context): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $prompt = $this->upgradePromptService->getPromptForContext($user, $context);

        return response()->json([
            'success' => true,
            'prompt' => $prompt,
        ]);
    }

    public function getPersonalizedMessage(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $message = $this->upgradePromptService->getPersonalizedMessage($user);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function checkEligibility(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $shouldShow = $this->upgradePromptService->shouldShowPrompt($user);

        return response()->json([
            'success' => true,
            'should_show' => $shouldShow,
        ]);
    }

    public function trackInteraction(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $request->validate([
            'prompt_id' => ['required', 'string'],
            'action' => ['required', 'in:view,click,dismiss,upgrade'],
            'context' => ['nullable', 'string'],
        ]);

        // TODO: Store interaction analytics in database
        // This would help improve the upgrade prompt effectiveness

        return response()->json([
            'success' => true,
            'message' => 'Interaction tracked successfully.',
        ]);
    }

    public function getUpgradeStats(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ]);
        }

        $stats = [
            'current_plan' => $user->getPlanName(),
            'projects_count' => $user->projects()->count(),
            'teams_count' => $user->ownedTeams()->count(),
            'usage_stats' => [
                'projects' => [
                    'current' => $user->projects()->count(),
                    'limit' => $user->getUsageLimit('projects'),
                    'percentage' => $this->calculateUsagePercentage($user, 'projects'),
                ],
                'teams' => [
                    'current' => $user->ownedTeams()->count(),
                    'limit' => $user->getUsageLimit('teams'),
                    'percentage' => $this->calculateUsagePercentage($user, 'teams'),
                ],
                'ai_messages' => [
                    'current' => 0, // TODO: Implement AI usage tracking
                    'limit' => $user->getUsageLimit('ai_messages'),
                    'percentage' => 0,
                ],
            ],
            'upgrade_suggestions' => $this->upgradePromptService->getUpgradePrompts($user)->map(function ($prompt) {
                return [
                    'id' => $prompt['id'],
                    'title' => $prompt['title'],
                    'priority' => $prompt['priority'],
                    'type' => $prompt['type'],
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    private function calculateUsagePercentage(User $user, string $feature): float
    {
        $limit = $user->getUsageLimit($feature);
        
        if (!$limit || $limit === -1) {
            return 0;
        }

        $current = match($feature) {
            'projects' => $user->projects()->count(),
            'teams' => $user->ownedTeams()->count(),
            'ai_messages' => 0, // TODO: Implement AI usage tracking
            default => 0,
        };

        return ($current / $limit) * 100;
    }
}
