<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\BillingHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private array $gatewayConfig;

    public function __construct()
    {
        $this->gatewayConfig = [
            'stripe' => [
                'secret_key' => config('services.stripe.secret_key'),
                'publishable_key' => config('services.stripe.publishable_key'),
                'webhook_secret' => config('services.stripe.webhook_secret'),
            ],
            'paypal' => [
                'client_id' => config('services.paypal.client_id'),
                'client_secret' => config('services.paypal.client_secret'),
                'sandbox' => config('services.paypal.sandbox', true),
            ],
            'mpesa' => [
                'consumer_key' => config('services.mpesa.consumer_key'),
                'consumer_secret' => config('services.mpesa.consumer_secret'),
                'passkey' => config('services.mpesa.passkey'),
                'shortcode' => config('services.mpesa.shortcode'),
            ],
        ];
    }

    public function processPayment(array $data): array
    {
        $gateway = $data['payment_method'];
        
        try {
            return match($gateway) {
                'stripe' => $this->processStripePayment($data),
                'paypal' => $this->processPayPalPayment($data),
                'mpesa' => $this->processMpesaPayment($data),
                'bank_transfer' => $this->processBankTransfer($data),
                default => throw new \Exception("Unsupported payment gateway: {$gateway}"),
            };
        } catch (\Exception $e) {
            Log::error("Payment processing error for {$gateway}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'gateway_data' => [],
            ];
        }
    }

    private function processStripePayment(array $data): array
    {
        $secretKey = $this->gatewayConfig['stripe']['secret_key'];
        
        // Create or retrieve Stripe customer
        $customer = $this->createStripeCustomer($data['user'], $secretKey);
        
        // Create subscription
        $subscriptionData = [
            'customer' => $customer['id'],
            'items' => [[
                'price' => $data['plan']->getStripePriceIdForBillingCycle($data['billing_cycle']),
            ]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ];

        // Add trial period if applicable
        if ($data['plan']->trial_days > 0) {
            $subscriptionData['trial_period_days'] = $data['plan']->trial_days;
        }

        $response = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/subscriptions', $subscriptionData);

        if (!$response->successful()) {
            throw new \Exception('Stripe subscription creation failed: ' . $response->body());
        }

        $subscription = $response->json();

        return [
            'success' => true,
            'gateway_data' => [
                'gateway' => 'stripe',
                'subscription_id' => $subscription['id'],
                'customer_id' => $customer['id'],
                'client_secret' => $subscription['latest_invoice']['payment_intent']['client_secret'],
                'payment_intent_id' => $subscription['latest_invoice']['payment_intent']['id'],
            ],
        ];
    }

    private function processPayPalPayment(array $data): array
    {
        // PayPal implementation would go here
        // For now, return a mock response
        return [
            'success' => true,
            'gateway_data' => [
                'gateway' => 'paypal',
                'subscription_id' => 'paypal_sub_' . uniqid(),
                'customer_id' => 'paypal_cust_' . uniqid(),
                'approval_url' => 'https://www.sandbox.paypal.com/approve-now',
            ],
        ];
    }

    private function processMpesaPayment(array $data): array
    {
        // M-Pesa implementation would go here
        // For now, return a mock response
        return [
            'success' => true,
            'gateway_data' => [
                'gateway' => 'mpesa',
                'transaction_id' => 'mpesa_' . uniqid(),
                'phone_number' => $data['phone_number'] ?? '',
                'checkout_request_id' => 'ws_CO_' . uniqid(),
            ],
        ];
    }

    private function processBankTransfer(array $data): array
    {
        // Bank transfer implementation would go here
        // For now, return a mock response
        return [
            'success' => true,
            'gateway_data' => [
                'gateway' => 'bank_transfer',
                'reference' => 'BT_' . uniqid(),
                'bank_details' => [
                    'account_name' => 'Smart Project Hub',
                    'account_number' => '1234567890',
                    'bank_name' => 'Example Bank',
                    'routing_number' => '123456789',
                ],
            ],
        ];
    }

    private function createStripeCustomer($user, string $secretKey): array
    {
        $response = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/customers', [
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create Stripe customer: ' . $response->body());
        }

        return $response->json();
    }

    public function cancelSubscription(Subscription $subscription): array
    {
        $gateway = $subscription->payment_gateway;
        
        try {
            return match($gateway) {
                'stripe' => $this->cancelStripeSubscription($subscription),
                'paypal' => $this->cancelPayPalSubscription($subscription),
                default => [
                    'success' => true,
                    'message' => 'Subscription cancelled successfully.',
                ],
            };
        } catch (\Exception $e) {
            Log::error("Subscription cancellation error for {$gateway}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function cancelStripeSubscription(Subscription $subscription): array
    {
        $secretKey = $this->gatewayConfig['stripe']['secret_key'];
        $subscriptionId = $subscription->gateway_subscription_id;

        if (!$subscriptionId) {
            throw new \Exception('No Stripe subscription ID found.');
        }

        $response = Http::withToken($secretKey)
            ->delete("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to cancel Stripe subscription: ' . $response->body());
        }

        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully.',
        ];
    }

    private function cancelPayPalSubscription(Subscription $subscription): array
    {
        // PayPal cancellation implementation
        return [
            'success' => true,
            'message' => 'PayPal subscription cancelled successfully.',
        ];
    }

    public function resumeSubscription(Subscription $subscription): array
    {
        $gateway = $subscription->payment_gateway;
        
        try {
            return match($gateway) {
                'stripe' => $this->resumeStripeSubscription($subscription),
                'paypal' => $this->resumePayPalSubscription($subscription),
                default => [
                    'success' => true,
                    'message' => 'Subscription resumed successfully.',
                ],
            };
        } catch (\Exception $e) {
            Log::error("Subscription resume error for {$gateway}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function resumeStripeSubscription(Subscription $subscription): array
    {
        $secretKey = $this->gatewayConfig['stripe']['secret_key'];
        $subscriptionId = $subscription->gateway_subscription_id;

        if (!$subscriptionId) {
            throw new \Exception('No Stripe subscription ID found.');
        }

        $response = Http::withToken($secretKey)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscriptions/{$subscriptionId}/resume");

        if (!$response->successful()) {
            throw new \Exception('Failed to resume Stripe subscription: ' . $response->body());
        }

        return [
            'success' => true,
            'message' => 'Subscription resumed successfully.',
        ];
    }

    private function resumePayPalSubscription(Subscription $subscription): array
    {
        // PayPal resume implementation
        return [
            'success' => true,
            'message' => 'PayPal subscription resumed successfully.',
        ];
    }

    public function updatePaymentMethod(Subscription $subscription, string $gateway, string $paymentToken): array
    {
        try {
            return match($gateway) {
                'stripe' => $this->updateStripePaymentMethod($subscription, $paymentToken),
                'paypal' => $this->updatePayPalPaymentMethod($subscription, $paymentToken),
                default => [
                    'success' => false,
                    'message' => 'Payment method updates not supported for this gateway.',
                ],
            };
        } catch (\Exception $e) {
            Log::error("Payment method update error for {$gateway}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function updateStripePaymentMethod(Subscription $subscription, string $paymentToken): array
    {
        // Stripe payment method update implementation
        return [
            'success' => true,
            'gateway_data' => [
                'payment_method_id' => $paymentToken,
                'updated_at' => now()->toISOString(),
            ],
        ];
    }

    private function updatePayPalPaymentMethod(Subscription $subscription, string $paymentToken): array
    {
        // PayPal payment method update implementation
        return [
            'success' => true,
            'gateway_data' => [
                'paypal_token' => $paymentToken,
                'updated_at' => now()->toISOString(),
            ],
        ];
    }

    public function generateInvoice(BillingHistory $billingHistory): string
    {
        // Generate PDF invoice
        $pdf = new \Dompdf\Dompdf();
        
        $html = view('pdfs.invoice', [
            'billingHistory' => $billingHistory,
            'user' => $billingHistory->user,
            'subscription' => $billingHistory->subscription,
        ])->render();

        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return $pdf->output();
    }

    public function handleWebhook(string $gateway, array $payload): array
    {
        try {
            return match($gateway) {
                'stripe' => $this->handleStripeWebhook($payload),
                'paypal' => $this->handlePayPalWebhook($payload),
                default => [
                    'success' => false,
                    'message' => 'Unsupported webhook gateway.',
                ],
            };
        } catch (\Exception $e) {
            Log::error("Webhook handling error for {$gateway}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function handleStripeWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? '';
        
        return match($eventType) {
            'invoice.payment_succeeded' => $this->handleStripeInvoicePaymentSucceeded($payload),
            'invoice.payment_failed' => $this->handleStripeInvoicePaymentFailed($payload),
            'customer.subscription.deleted' => $this->handleStripeSubscriptionDeleted($payload),
            default => [
                'success' => true,
                'message' => 'Webhook received but not processed.',
            ],
        };
    }

    private function handleStripeInvoicePaymentSucceeded(array $payload): array
    {
        // Handle successful payment
        $invoice = $payload['data']['object'];
        $subscriptionId = $invoice['subscription'];
        
        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            // Update subscription end date
            $subscription->update([
                'ends_at' => now()->addMonth(),
                'status' => 'active',
            ]);
            
            // Create billing record
            BillingHistory::createPaymentRecord(
                $subscription->user,
                $subscription,
                $invoice['amount_paid'] / 100,
                'Monthly subscription payment',
                'stripe',
                [
                    'invoice_id' => $invoice['id'],
                    'payment_intent_id' => $invoice['payment_intent'],
                ]
            );
        }
        
        return ['success' => true];
    }

    private function handleStripeInvoicePaymentFailed(array $payload): array
    {
        // Handle failed payment
        $invoice = $payload['data']['object'];
        $subscriptionId = $invoice['subscription'];
        
        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            // Update subscription status
            $subscription->update([
                'status' => 'past_due',
            ]);
        }
        
        return ['success' => true];
    }

    private function handleStripeSubscriptionDeleted(array $payload): array
    {
        // Handle subscription deletion
        $stripeSubscription = $payload['data']['object'];
        $subscriptionId = $stripeSubscription['id'];
        
        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);
        }
        
        return ['success' => true];
    }

    private function handlePayPalWebhook(array $payload): array
    {
        // PayPal webhook handling
        return ['success' => true];
    }
}
