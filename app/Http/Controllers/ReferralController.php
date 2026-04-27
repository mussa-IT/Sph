<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        
        if (!$user->canCreateReferrals()) {
            return redirect()->route('pricing')
                ->with('info', 'Upgrade to a paid plan to access the referral program.');
        }

        $referralCode = $user->getReferralCode();
        $referralUrl = $user->getReferralUrl();
        $referralStats = $user->getReferralStats();
        
        $referrals = $user->referrals()
            ->with('referredUser')
            ->latest()
            ->paginate(20);

        return view('pages.referrals.index', compact(
            'referralCode',
            'referralUrl',
            'referralStats',
            'referrals'
        ));
    }

    public function accept(string $code): View
    {
        $referral = Referral::findByCode($code);
        
        if (!$referral) {
            abort(404, 'Invalid referral code.');
        }

        if ($referral->isExpired()) {
            return view('pages.referrals.expired');
        }

        return view('pages.referrals.accept', compact('referral'));
    }

    public function process(string $code, Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $referral = Referral::findByCode($code);
        
        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code.',
            ]);
        }

        if ($referral->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This referral code has expired.',
            ]);
        }

        // Check if email is already registered
        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered.',
            ]);
        }

        // Update referral with email
        $referral->update([
            'referred_email' => $request->input('email'),
        ]);

        // Store referral code in session for registration
        session(['referral_code' => $code]);

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied! Complete your registration to claim your rewards.',
            'redirect_url' => route('register'),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canCreateReferrals()) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade to a paid plan to create referral codes.',
            ]);
        }

        $request->validate([
            'referred_email' => ['nullable', 'email', 'max:255'],
            'reward_type' => ['required', 'in:credit,discount,upgrade,trial'],
            'reward_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $referral = Referral::createReferral($user, $request->only([
                'referred_email',
                'reward_type',
                'reward_amount',
                'notes',
            ]));

            // TODO: Send invitation email
            // Mail::to($referral->referred_email)->send(new ReferralInvitationMail($referral));

            return response()->json([
                'success' => true,
                'message' => 'Referral code created successfully!',
                'referral' => $referral,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create referral code. Please try again.',
            ]);
        }
    }

    public function share(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canCreateReferrals()) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade to a paid plan to share referral links.',
            ]);
        }

        $referralUrl = $user->getReferralUrl();
        $referralCode = $user->getReferralCode();

        $shareData = [
            'url' => $referralUrl,
            'code' => $referralCode,
            'title' => 'Join Smart Project Hub',
            'description' => 'Start managing your projects smarter with Smart Project Hub. Get advanced features, AI-powered insights, and team collaboration tools.',
        ];

        return response()->json([
            'success' => true,
            'share_data' => $shareData,
        ]);
    }

    public function convert(string $code): JsonResponse
    {
        $user = Auth::user();
        
        $referral = Referral::findByCode($code);
        
        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code.',
            ]);
        }

        if ($referral->referred_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'This referral code is not associated with your account.',
            ]);
        }

        if ($referral->isConverted()) {
            return response()->json([
                'success' => false,
                'message' => 'This referral has already been converted.',
            ]);
        }

        try {
            $referral->markAsConverted();

            return response()->json([
                'success' => true,
                'message' => 'Referral converted successfully! Your reward has been applied.',
                'reward' => $referral->getRewardLabel(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert referral. Please try again.',
            ]);
        }
    }

    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canCreateReferrals()) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade to a paid plan to view referral stats.',
            ]);
        }

        $stats = $user->getReferralStats();
        $recentReferrals = $user->referrals()
            ->with('referredUser')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_referrals' => $recentReferrals,
        ]);
    }

    public function leaderboard(): JsonResponse
    {
        $topReferrers = User::withCount(['referrals' => function ($query) {
                $query->converted();
            }])
            ->whereHas('referrals', function ($query) {
                $query->converted();
            })
            ->orderByDesc('referrals_count')
            ->take(10)
            ->get(['id', 'name', 'referrals_count']);

        return response()->json([
            'success' => true,
            'leaderboard' => $topReferrers,
        ]);
    }

    public function earnings(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canCreateReferrals()) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade to a paid plan to view earnings.',
            ]);
        }

        $earnings = [
            'total_earned' => $user->getTotalReferralEarnings(),
            'pending_earnings' => $user->getPendingReferralEarnings(),
            'available_earnings' => $user->getTotalReferralEarnings(), // Available for withdrawal
        ];

        $recentEarnings = $user->convertedReferrals()
            ->with('referredUser')
            ->latest('converted_at')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'earnings' => $earnings,
            'recent_earnings' => $recentEarnings,
        ]);
    }

    public function resendInvitation(Referral $referral): JsonResponse
    {
        $user = Auth::user();
        
        if ($referral->referrer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only resend invitations for your own referrals.',
            ]);
        }

        if (!$referral->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This referral is no longer pending.',
            ]);
        }

        try {
            // TODO: Send invitation email
            // Mail::to($referral->referred_email)->send(new ReferralInvitationMail($referral));

            return response()->json([
                'success' => true,
                'message' => 'Invitation resent successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invitation. Please try again.',
            ]);
        }
    }

    public function cancel(Referral $referral): JsonResponse
    {
        $user = Auth::user();
        
        if ($referral->referrer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel your own referrals.',
            ]);
        }

        if (!$referral->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'This referral cannot be cancelled.',
            ]);
        }

        try {
            $referral->markAsExpired();

            return response()->json([
                'success' => true,
                'message' => 'Referral cancelled successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel referral. Please try again.',
            ]);
        }
    }

    public function publicPage(string $code): View
    {
        $referral = Referral::findByCode($code);
        
        if (!$referral) {
            abort(404, 'Invalid referral code.');
        }

        if ($referral->isExpired()) {
            return view('pages.referrals.expired');
        }

        return view('pages.referrals.public', compact('referral'));
    }
}
