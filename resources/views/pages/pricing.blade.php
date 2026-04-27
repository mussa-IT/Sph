@extends('layouts.app')

@section('title', 'Pricing Plans')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-secondary/5">
    <!-- Hero Section -->
    <div class="container mx-auto px-4 py-16 max-w-7xl">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-6xl font-bold text-foreground dark:text-foreground-dark mb-6">
                Choose Your
                <span class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    Perfect Plan
                </span>
            </h1>
            <p class="text-xl text-muted dark:text-muted-dark max-w-3xl mx-auto mb-8">
                Start free and scale as you grow. No hidden fees, no surprises. Cancel anytime.
            </p>
            
            <!-- Billing Toggle -->
            <div class="inline-flex items-center bg-background dark:bg-background-dark rounded-2xl p-1 border border-muted/20">
                <button 
                    id="monthly-toggle" 
                    class="px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-primary text-primary-foreground"
                    onclick="setBillingCycle('monthly')"
                >
                    Monthly
                </button>
                <button 
                    id="yearly-toggle" 
                    class="px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 text-muted dark:text-muted-dark"
                    onclick="setBillingCycle('yearly')"
                >
                    Yearly
                    <span class="ml-2 px-2 py-0.5 rounded-full bg-success/10 text-success text-xs font-medium">
                        Save 20%
                    </span>
                </button>
            </div>
        </div>

        <!-- Pricing Cards -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 mb-16">
            @foreach($plans as $plan)
                <div class="relative group">
                    <!-- Popular Badge -->
                    @if($plan->is_popular)
                        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                            <span class="inline-flex px-4 py-1 rounded-full bg-gradient-to-r from-primary to-secondary text-white text-xs font-bold shadow-lg">
                                MOST POPULAR
                            </span>
                        </div>
                    @endif

                    <div class="relative h-full rounded-3xl border border-muted/20 bg-background dark:bg-background-dark shadow-2xl transition-all duration-300 hover:shadow-3xl hover:scale-105 @if($plan->is_popular) ring-2 ring-primary/50 @endif">
                        <!-- Plan Header -->
                        <div class="p-8 pb-6 border-b border-muted/10">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-2">
                                    {{ $plan->name }}
                                </h3>
                                <p class="text-sm text-muted dark:text-muted-dark mb-4">
                                    {{ $plan->description }}
                                </p>
                                
                                <!-- Price Display -->
                                <div class="mb-4">
                                    <span class="text-5xl font-bold text-foreground dark:text-foreground-dark monthly-price">
                                        {{ $plan->getFormattedMonthlyPrice() }}
                                    </span>
                                    <span class="text-5xl font-bold text-foreground dark:text-foreground-dark yearly-price hidden">
                                        {{ $plan->getFormattedYearlyPrice() }}
                                    </span>
                                    <span class="text-lg text-muted dark:text-muted-dark">/month</span>
                                </div>
                                
                                @if($plan->yearly_price > 0)
                                    <p class="text-xs text-muted dark:text-muted-dark yearly-savings hidden">
                                        Save {{ $plan->getYearlyDiscount() }}% with yearly billing
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="p-8 pt-6">
                            <ul class="space-y-4 mb-8">
                                @foreach(json_decode($plan->features) as $feature)
                                    <li class="flex items-start gap-3">
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <span class="text-sm text-foreground dark:text-foreground-dark">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <!-- CTA Button -->
                            @if($currentPlan && $currentPlan->id === $plan->id)
                                <button class="w-full py-3 px-6 rounded-2xl border-2 border-success/20 bg-success/10 text-success font-medium transition-colors cursor-default">
                                    Current Plan
                                </button>
                            @else
                                <a href="{{ route('subscription.checkout', ['plan' => $plan->slug, 'billing_cycle' => 'monthly']) }}" 
                                   class="monthly-cta block w-full py-3 px-6 rounded-2xl bg-primary text-primary-foreground font-medium text-center transition-all duration-200 hover:bg-primary/90 hover:shadow-lg">
                                    @if($plan->monthly_price > 0)
                                        Start Trial
                                    @else
                                        Get Started
                                    @endif
                                </a>
                                <a href="{{ route('subscription.checkout', ['plan' => $plan->slug, 'billing_cycle' => 'yearly']) }}" 
                                   class="yearly-cta hidden block w-full py-3 px-6 rounded-2xl bg-primary text-primary-foreground font-medium text-center transition-all duration-200 hover:bg-primary/90 hover:shadow-lg">
                                    @if($plan->yearly_price > 0)
                                        Start Trial
                                    @else
                                        Get Started
                                    @endif
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Feature Comparison -->
        <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-8 shadow-xl">
            <h2 class="text-3xl font-bold text-foreground dark:text-foreground-dark text-center mb-12">
                Compare All Features
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-muted/20">
                            <th class="text-left py-4 px-4 text-sm font-medium text-muted dark:text-muted-dark">Features</th>
                            @foreach($plans as $plan)
                                <th class="text-center py-4 px-4 text-sm font-medium text-foreground dark:text-foreground-dark">
                                    {{ $plan->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-muted/10">
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">Projects</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center text-sm text-foreground dark:text-foreground-dark">
                                    {{ $plan->getLimit('projects') ?? 'Unlimited' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-muted/10">
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">AI Chat</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center">
                                    @if($plan->hasFeature('ai_chat'))
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-muted dark:text-muted-dark">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-muted/10">
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">Advanced AI Builder</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center">
                                    @if($plan->hasFeature('advanced_ai_builder'))
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-muted dark:text-muted-dark">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-muted/10">
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">Analytics</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center">
                                    @if($plan->hasFeature('analytics'))
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-muted dark:text-muted-dark">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="border-b border-muted/10">
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">Team Collaboration</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center">
                                    @if($plan->hasFeature('team_collaboration'))
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-muted dark:text-muted-dark">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="py-4 px-4 text-sm text-foreground dark:text-foreground-dark">Priority Support</td>
                            @foreach($plans as $plan)
                                <td class="py-4 px-4 text-center">
                                    @if($plan->hasFeature('priority_support'))
                                        <span class="w-5 h-5 rounded-full bg-success/10 flex items-center justify-center mx-auto">
                                            <svg class="w-3 h-3 text-success" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-muted dark:text-muted-dark">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-foreground dark:text-foreground-dark text-center mb-12">
                Frequently Asked Questions
            </h2>
            
            <div class="grid gap-6 md:grid-cols-2">
                <div class="bg-background dark:bg-background-dark rounded-2xl border border-muted/20 p-6">
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-3">
                        Can I change plans anytime?
                    </h3>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we'll prorate any differences.
                    </p>
                </div>
                
                <div class="bg-background dark:bg-background-dark rounded-2xl border border-muted/20 p-6">
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-3">
                        What happens if I cancel?
                    </h3>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        You'll continue to have access to your plan until the end of your billing period. No refunds for partial months.
                    </p>
                </div>
                
                <div class="bg-background dark:bg-background-dark rounded-2xl border border-muted/20 p-6">
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-3">
                        Do you offer refunds?
                    </h3>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        We offer a 14-day money-back guarantee for new subscriptions. After that, we provide prorated refunds for annual plans.
                    </p>
                </div>
                
                <div class="bg-background dark:bg-background-dark rounded-2xl border border-muted/20 p-6">
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-3">
                        Is my data secure?
                    </h3>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        Absolutely! We use industry-standard encryption and security practices. Your data is always safe and private.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentBillingCycle = 'monthly';

function setBillingCycle(cycle) {
    currentBillingCycle = cycle;
    
    // Update toggle buttons
    const monthlyBtn = document.getElementById('monthly-toggle');
    const yearlyBtn = document.getElementById('yearly-toggle');
    
    if (cycle === 'monthly') {
        monthlyBtn.className = 'px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-primary text-primary-foreground';
        yearlyBtn.className = 'px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 text-muted dark:text-muted-dark';
    } else {
        monthlyBtn.className = 'px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 text-muted dark:text-muted-dark';
        yearlyBtn.className = 'px-6 py-3 rounded-xl text-sm font-medium transition-all duration-200 bg-primary text-primary-foreground';
    }
    
    // Update price displays
    document.querySelectorAll('.monthly-price').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'monthly');
    });
    
    document.querySelectorAll('.yearly-price').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'yearly');
    });
    
    document.querySelectorAll('.monthly-savings').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'yearly');
    });
    
    document.querySelectorAll('.monthly-cta').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'monthly');
    });
    
    document.querySelectorAll('.yearly-cta').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'yearly');
    });
    
    // Update CTA links
    document.querySelectorAll('.monthly-cta').forEach(el => {
        if (!el.classList.contains('hidden')) {
            const href = el.getAttribute('href');
            el.setAttribute('href', href.replace(/billing_cycle=[^&]*/, 'billing_cycle=monthly'));
        }
    });
    
    document.querySelectorAll('.yearly-cta').forEach(el => {
        if (!el.classList.contains('hidden')) {
            const href = el.getAttribute('href');
            el.setAttribute('href', href.replace(/billing_cycle=[^&]*/, 'billing_cycle=yearly'));
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    setBillingCycle('monthly');
});
</script>
@endsection
