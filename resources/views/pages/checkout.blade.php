@extends('layouts.app')

@section('title', 'Checkout - ' . $plan->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-secondary/5">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Progress Steps -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">1</div>
                    <span class="ml-2 text-sm font-medium text-foreground dark:text-foreground-dark">Choose Plan</span>
                </div>
                <div class="w-8 h-0.5 bg-muted/30"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm font-medium text-foreground dark:text-foreground-dark">Payment</span>
                </div>
                <div class="w-8 h-0.5 bg-muted/30"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-muted/30 text-muted flex items-center justify-center text-sm font-medium">3</div>
                    <span class="ml-2 text-sm text-muted dark:text-muted-dark">Complete</span>
                </div>
            </div>
        </div>

        <form id="checkout-form" class="grid gap-8 lg:grid-cols-3">
            <!-- Left Column - Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 shadow-xl">
                    <h2 class="text-xl font-bold text-foreground dark:text-foreground-dark mb-6">Order Summary</h2>
                    
                    <!-- Plan Details -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $plan->name }} Plan</h3>
                                <p class="text-sm text-muted dark:text-muted-dark">{{ ucfirst($billingCycle) }} billing</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-foreground dark:text-foreground-dark">
                                    {{ $plan->getFormattedMonthlyPrice() }}
                                </p>
                                <p class="text-sm text-muted dark:text-muted-dark">/month</p>
                            </div>
                        </div>
                        
                        @if($plan->trial_days > 0)
                            <div class="p-3 rounded-xl bg-success/10 border border-success/20">
                                <div class="flex items-center gap-2">
                                    <span class="text-success">🎁</span>
                                    <div>
                                        <p class="text-sm font-medium text-success">{{ $plan->trial_days }}-day free trial</p>
                                        <p class="text-xs text-success">Cancel anytime during trial</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted dark:text-muted-dark">Base price</span>
                            <span class="text-foreground dark:text-foreground-dark">{{ $plan->getFormattedMonthlyPrice() }}</span>
                        </div>
                        
                        @if($billingCycle === 'yearly' && $plan->getYearlyDiscount() > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-muted dark:text-muted-dark">Yearly discount</span>
                                <span class="text-success">-{{ $plan->getYearlyDiscount() }}%</span>
                            </div>
                        @endif
                        
                        @if($plan->trial_days > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-muted dark:text-muted-dark">Trial period</span>
                                <span class="text-success">FREE</span>
                            </div>
                        @endif
                        
                        <div class="border-t border-muted/20 pt-3">
                            <div class="flex justify-between font-semibold">
                                <span>Total</span>
                                <span>{{ $plan->getFormattedMonthlyPrice() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div class="mb-6">
                        <h4 class="font-medium text-foreground dark:text-foreground-dark mb-3">What's included:</h4>
                        <ul class="space-y-2">
                            @foreach(json_decode($plan->features) as $feature)
                                <li class="flex items-center gap-2 text-sm text-foreground dark:text-foreground-dark">
                                    <span class="w-4 h-4 rounded-full bg-success/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-2.5 h-2.5 text-success" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </span>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Trust Badges -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-xs text-muted dark:text-muted-dark">
                            <span class="w-4 h-4">🔒</span>
                            <span>Secure payment processing</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-muted dark:text-muted-dark">
                            <span class="w-4 h-4">🛡️</span>
                            <span>30-day money-back guarantee</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-muted dark:text-muted-dark">
                            <span class="w-4 h-4">❌</span>
                            <span>Cancel anytime</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 shadow-xl">
                    <h2 class="text-xl font-bold text-foreground dark:text-foreground-dark mb-6">Payment Information</h2>
                    
                    <!-- Payment Method Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-3">
                            Choose Payment Method
                        </label>
                        <div class="grid gap-3">
                            <label class="relative flex items-center p-4 border border-muted/20 rounded-xl cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="radio" name="payment_method" value="stripe" class="sr-only" checked>
                                <div class="flex items-center w-full">
                                    <div class="w-5 h-5 rounded-full border-2 border-muted-30 flex items-center justify-center mr-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary hidden payment-method-indicator"></div>
                                    </div>
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-400 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">S</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground dark:text-foreground-dark">Credit Card</p>
                                                <p class="text-xs text-muted dark:text-muted-dark">Visa, Mastercard, Amex</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-1">
                                            <img src="https://img.icons8.com/color/24/000000/visa.png" alt="Visa" class="w-6 h-4">
                                            <img src="https://img.icons8.com/color/24/000000/mastercard.png" alt="Mastercard" class="w-6 h-4">
                                            <img src="https://img.icons8.com/color/24/000000/amex.png" alt="Amex" class="w-6 h-4">
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 border border-muted/20 rounded-xl cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="radio" name="payment_method" value="paypal" class="sr-only">
                                <div class="flex items-center w-full">
                                    <div class="w-5 h-5 rounded-full border-2 border-muted-30 flex items-center justify-center mr-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary hidden payment-method-indicator"></div>
                                    </div>
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-300 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">P</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground dark:text-foreground-dark">PayPal</p>
                                                <p class="text-xs text-muted dark:text-muted-dark">Fast & secure</p>
                                            </div>
                                        </div>
                                        <img src="https://img.icons8.com/color/24/000000/paypal.png" alt="PayPal" class="w-8 h-5">
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center p-4 border border-muted/20 rounded-xl cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="radio" name="payment_method" value="mpesa" class="sr-only">
                                <div class="flex items-center w-full">
                                    <div class="w-5 h-5 rounded-full border-2 border-muted-30 flex items-center justify-center mr-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary hidden payment-method-indicator"></div>
                                    </div>
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-green-400 rounded flex items-center justify-center">
                                                <span class="text-white text-xs font-bold">M</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground dark:text-foreground-dark">M-Pesa</p>
                                                <p class="text-xs text-muted dark:text-muted-dark">Mobile money</p>
                                            </div>
                                        </div>
                                        <div class="text-xs text-muted dark:text-muted-dark">KE, TZ, UG</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Billing Information -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-3">
                            Billing Information
                        </label>
                        <div class="grid gap-4">
                            <div>
                                <label class="block text-xs text-muted dark:text-muted-dark mb-1">Email</label>
                                <input type="email" value="{{ Auth::user()->email }}" readonly 
                                       class="input-brand w-full" placeholder="your@email.com">
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs text-muted dark:text-muted-dark mb-1">First Name</label>
                                    <input type="text" value="{{ Auth::user()->name }}" readonly 
                                           class="input-brand w-full" placeholder="John">
                                </div>
                                <div>
                                    <label class="block text-xs text-muted dark:text-muted-dark mb-1">Last Name</label>
                                    <input type="text" value="{{ Auth::user()->name }}" readonly 
                                           class="input-brand w-full" placeholder="Doe">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form (Stripe) -->
                    <div id="stripe-payment-form" class="mb-6">
                        <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-3">
                            Card Information
                        </label>
                        <div class="space-y-4">
                            <div id="card-element" class="p-3 border border-muted/20 rounded-xl">
                                <!-- Stripe Elements will be inserted here -->
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs text-muted dark:text-muted-dark mb-1">Country</label>
                                    <select class="input-brand w-full">
                                        <option value="US">United States</option>
                                        <option value="KE">Kenya</option>
                                        <option value="TZ">Tanzania</option>
                                        <option value="UG">Uganda</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-muted dark:text-muted-dark mb-1">ZIP Code</label>
                                    <input type="text" class="input-brand w-full" placeholder="12345">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Form (Hidden by default) -->
                    <div id="paypal-payment-form" class="mb-6 hidden">
                        <div class="p-6 border border-muted/20 rounded-xl text-center">
                            <p class="text-sm text-muted dark:text-muted-dark mb-4">
                                You'll be redirected to PayPal to complete your payment securely.
                            </p>
                            <div class="flex justify-center">
                                <img src="https://img.icons8.com/color/48/000000/paypal.png" alt="PayPal" class="w-12 h-8">
                            </div>
                        </div>
                    </div>

                    <!-- M-Pesa Form (Hidden by default) -->
                    <div id="mpesa-payment-form" class="mb-6 hidden">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-muted dark:text-muted-dark mb-1">Phone Number</label>
                                <input type="tel" class="input-brand w-full" placeholder="+254700000000" id="mpesa-phone">
                            </div>
                            <div class="p-4 bg-info/10 border border-info/20 rounded-xl">
                                <p class="text-sm text-info">
                                    You'll receive an M-Pesa prompt on your phone to complete the payment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" id="terms-checkbox" class="mt-1" required>
                            <span class="text-sm text-foreground dark:text-foreground-dark">
                                I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a> and 
                                <a href="#" class="text-primary hover:underline">Privacy Policy</a>. 
                                I understand that I can cancel anytime.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-button" 
                            class="w-full btn-brand py-4 text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="button-text">
                            @if($plan->monthly_price > 0)
                                Start Free Trial
                            @else
                                Get Started
                            @endif
                        </span>
                    </button>

                    <!-- Error Display -->
                    <div id="payment-errors" class="hidden mt-4 p-4 rounded-xl bg-danger/10 border border-danger/20">
                        <p class="text-sm text-danger"></p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const paymentErrors = document.getElementById('payment-errors');
    const termsCheckbox = document.getElementById('terms-checkbox');
    
    // Payment method selection
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const paymentForms = {
        stripe: document.getElementById('stripe-payment-form'),
        paypal: document.getElementById('paypal-payment-form'),
        mpesa: document.getElementById('mpesa-payment-form')
    };

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all payment forms
            Object.values(paymentForms).forEach(form => form.classList.add('hidden'));
            
            // Show selected payment form
            paymentForms[this.value].classList.remove('hidden');
            
            // Update radio button indicators
            document.querySelectorAll('.payment-method-indicator').forEach(indicator => {
                indicator.classList.add('hidden');
            });
            this.nextElementSibling.querySelector('.payment-method-indicator').classList.remove('hidden');
        });
    });

    // Initialize with Stripe form visible
    paymentForms.stripe.classList.remove('hidden');
    document.querySelector('input[value="stripe"]').nextElementSibling.querySelector('.payment-method-indicator').classList.remove('hidden');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!termsCheckbox.checked) {
            showError('Please accept the Terms of Service and Privacy Policy.');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        setLoading(true);
        hideError();

        try {
            const formData = {
                payment_method: paymentMethod,
                billing_cycle: '{{ $billingCycle }}',
            };

            if (paymentMethod === 'mpesa') {
                formData.phone_number = document.getElementById('mpesa-phone').value;
            }

            // For Stripe, you would get the payment token here
            if (paymentMethod === 'stripe') {
                // This would be implemented with Stripe Elements
                formData.payment_token = 'stripe_token_placeholder';
            }

            const response = await fetch('{{ route("subscription.process", $plan->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to success page
                window.location.href = result.redirect_url || '{{ route("subscription.success") }}';
            } else {
                showError(result.message || 'Payment failed. Please try again.');
            }
        } catch (error) {
            console.error('Payment error:', error);
            showError('An error occurred while processing your payment. Please try again.');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(isLoading) {
        submitButton.disabled = isLoading;
        if (isLoading) {
            buttonText.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Processing...';
        } else {
            buttonText.innerHTML = '{{ $plan->monthly_price > 0 ? "Start Free Trial" : "Get Started" }}';
        }
    }

    function showError(message) {
        paymentErrors.querySelector('p').textContent = message;
        paymentErrors.classList.remove('hidden');
    }

    function hideError() {
        paymentErrors.classList.add('hidden');
    }
});
</script>
@endsection
