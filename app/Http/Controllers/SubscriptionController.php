<?php

namespace App\Http\Controllers;

use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function pricing(): View
    {
        $plans = Plan::getActivePlans();
        $currentPlan = Auth::user()?->getCurrentSubscription()?->plan;
        
        return view('pages.pricing', compact('plans', 'currentPlan'));
    }

    public function checkout(Request $request, Plan $plan): View
    {
        $billingCycle = $request->get('billing_cycle', 'monthly');
        
        if (!in_array($billingCycle, ['monthly', 'yearly'])) {
            abort(400, 'Invalid billing cycle');
        }

        $price = $plan->getPriceForBillingCycle($billingCycle);
        $user = Auth::user();
        $currentSubscription = $user->getCurrentSubscription();

        // Check if user is trying to upgrade/downgrade
        if ($currentSubscription && $currentSubscription->plan_id === $plan->id) {
            return redirect()->route('pricing')
                ->with('info', 'You are already subscribed to this plan.');
        }

        return view('pages.checkout', compact(
            'plan',
            'billingCycle',
            'price',
            'currentSubscription'
        ));
    }

    public function processCheckout(Request $request, Plan $plan): JsonResponse
    {
        $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:stripe,paypal,mpesa,bank_transfer'],
            'payment_token' => ['required_if:payment_method,stripe'],
        ]);

        $user = Auth::user();
        $billingCycle = $request->input('billing_cycle');
        $paymentMethod = $request->input('payment_method');
        $price = $plan->getPriceForBillingCycle($billingCycle);

        try {
            // Process payment through the appropriate gateway
            $result = $this->paymentService->processPayment([
                'user' => $user,
                'plan' => $plan,
                'billing_cycle' => $billingCycle,
                'payment_method' => $paymentMethod,
                'amount' => $price,
                'payment_token' => $request->input('payment_token'),
            ]);

            if ($result['success']) {
                // Create or update subscription
                $subscription = Subscription::createSubscription(
                    $user,
                    $plan,
                    $billingCycle,
                    $result['gateway_data']
                );

                // Create billing record
                BillingHistory::createPaymentRecord(
                    $user,
                    $subscription,
                    $price,
                    "Subscription to {$plan->name} ({$billingCycle})",
                    $paymentMethod,
                    $result['gateway_data']
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription created successfully!',
                    'redirect_url' => route('billing.success'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment failed. Please try again.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Subscription checkout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your subscription. Please try again.',
            ]);
        }
    }

    public function success(): View
    {
        $subscription = Auth::user()->getCurrentSubscription();
        
        if (!$subscription) {
            return redirect()->route('pricing');
        }

        return view('pages.subscription-success', compact('subscription'));
    }

    public function billing(): View
    {
        $user = Auth::user();
        $subscription = $user->getCurrentSubscription();
        $billingHistory = $user->billingHistory()
            ->with('subscription.plan')
            ->latest('processed_at')
            ->paginate(20);

        return view('pages.billing', compact(
            'subscription',
            'billingHistory'
        ));
    }

    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        try {
            $result = $this->paymentService->cancelSubscription($subscription);

            if ($result['success']) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'auto_renew' => false,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to cancel subscription.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Subscription cancellation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling your subscription.',
            ]);
        }
    }

    public function resume(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('resume', $subscription);

        try {
            $result = $this->paymentService->resumeSubscription($subscription);

            if ($result['success']) {
                $subscription->update([
                    'status' => 'active',
                    'cancelled_at' => null,
                    'auto_renew' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription resumed successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to resume subscription.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Subscription resume error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resuming your subscription.',
            ]);
        }
    }

    public function updatePaymentMethod(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'in:stripe,paypal'],
            'payment_token' => ['required_if:payment_method,stripe'],
        ]);

        $user = Auth::user();
        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ]);
        }

        try {
            $result = $this->paymentService->updatePaymentMethod(
                $subscription,
                $request->input('payment_method'),
                $request->input('payment_token')
            );

            if ($result['success']) {
                $subscription->update([
                    'gateway_data' => array_merge(
                        $subscription->gateway_data ?? [],
                        $result['gateway_data']
                    ),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment method updated successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to update payment method.',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Payment method update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating your payment method.',
            ]);
        }
    }

    public function downloadInvoice(BillingHistory $billingHistory)
    {
        $this->authorize('view', $billingHistory);

        try {
            $pdf = $this->paymentService->generateInvoice($billingHistory);
            
            return response()->streamDownload(
                fn () => print($pdf),
                "invoice-{$billingHistory->invoice_number}.pdf",
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="invoice-{$billingHistory->invoice_number}.pdf"',
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Invoice generation error: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to generate invoice. Please try again.');
        }
    }
}
