<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Services\TrialService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrialController extends Controller
{
    public function __construct(
        private TrialService $trialService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $eligibility = $this->trialService->getTrialEligibility($user);
        $trialStatus = $this->trialService->getTrialStatus($user);
        $benefits = $this->trialService->getTrialBenefits();
        $incentives = $this->trialService->getTrialUpgradeIncentives();

        return view('pages.trial.index', compact(
            'eligibility',
            'trialStatus',
            'benefits',
            'incentives'
        ));
    }

    public function start(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$this->trialService->canStartTrial($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not eligible for a free trial.',
            ]);
        }

        $planSlug = $request->input('plan', 'pro');
        $plan = Plan::findBySlug($planSlug);
        
        if (!$plan || $plan->trial_days === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid plan for trial.',
            ]);
        }

        try {
            $subscription = $this->trialService->startTrial($user, $plan);
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start trial. Please try again.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trial started successfully!',
                'subscription' => $subscription,
                'redirect_url' => route('trial.dashboard'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting the trial.',
            ]);
        }
    }

    public function dashboard(): View
    {
        $user = Auth::user();
        $trialStatus = $this->trialService->getTrialStatus($user);
        
        if (!$trialStatus || $trialStatus['is_expired']) {
            return redirect()->route('trial.index')
                ->with('error', 'You do not have an active trial.');
        }

        $benefits = $this->trialService->getTrialBenefits();
        $incentives = $this->trialService->getTrialUpgradeIncentives();

        return view('pages.trial.dashboard', compact(
            'trialStatus',
            'benefits',
            'incentives'
        ));
    }

    public function convert(Request $request): JsonResponse
    {
        $user = Auth::user();
        $billingCycle = $request->input('billing_cycle', 'monthly');

        try {
            $subscription = $this->trialService->convertTrialToPaid($user, $billingCycle);
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to convert trial. Please check your trial status.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trial converted successfully! Welcome to your paid subscription.',
                'subscription' => $subscription,
                'redirect_url' => route('billing'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while converting your trial.',
            ]);
        }
    }

    public function extend(Request $request): JsonResponse
    {
        $user = Auth::user();
        $additionalDays = $request->input('days', 7);

        if ($additionalDays < 1 || $additionalDays > 30) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid number of days. Must be between 1 and 30.',
            ]);
        }

        try {
            $success = $this->trialService->extendTrial($user, $additionalDays);
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to extend trial. Please check your trial status.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Trial extended by {$additionalDays} days!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while extending your trial.',
            ]);
        }
    }

    public function cancel(): JsonResponse
    {
        $user = Auth::user();

        try {
            $success = $this->trialService->cancelTrial($user);
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel trial. Please check your trial status.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trial cancelled successfully.',
                'redirect_url' => route('trial.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling your trial.',
            ]);
        }
    }

    public function status(): JsonResponse
    {
        $user = Auth::user();
        $trialStatus = $this->trialService->getTrialStatus($user);
        $eligibility = $this->trialService->getTrialEligibility($user);

        return response()->json([
            'success' => true,
            'trial_status' => $trialStatus,
            'eligibility' => $eligibility,
        ]);
    }

    public function eligibility(): JsonResponse
    {
        $user = Auth::user();
        $eligibility = $this->trialService->getTrialEligibility($user);

        return response()->json([
            'success' => true,
            'eligibility' => $eligibility,
        ]);
    }

    public function benefits(): JsonResponse
    {
        $benefits = $this->trialService->getTrialBenefits();
        $incentives = $this->trialService->getTrialUpgradeIncentives();

        return response()->json([
            'success' => true,
            'benefits' => $benefits,
            'upgrade_incentives' => $incentives,
        ]);
    }

    public function stats(): JsonResponse
    {
        // This would be admin-only in a real application
        $stats = $this->trialService->getTrialStats();

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function expiring(): JsonResponse
    {
        // This would be admin-only in a real application
        $expiringTrials = $this->trialService->getExpiringTrials();

        return response()->json([
            'success' => true,
            'expiring_trials' => $expiringTrials,
        ]);
    }

    public function sendReminders(): JsonResponse
    {
        // This would be admin-only in a real application
        try {
            $this->trialService->sendTrialExpiryReminders();

            return response()->json([
                'success' => true,
                'message' => 'Trial expiry reminders sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send trial expiry reminders.',
            ]);
        }
    }

    public function dismissPrompt(): JsonResponse
    {
        session(['trial_prompt_dismissed' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Trial prompt dismissed.',
        ]);
    }

    public function shouldShowPrompt(): JsonResponse
    {
        $user = Auth::user();
        $shouldShow = $this->trialService->shouldShowTrialPrompt($user);

        return response()->json([
            'success' => true,
            'should_show' => $shouldShow,
        ]);
    }
}
