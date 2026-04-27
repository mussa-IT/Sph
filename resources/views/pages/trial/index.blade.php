@extends('layouts.app')

@section('title', 'Free Trial')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-secondary/5">
    <div class="container mx-auto px-4 py-16 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground dark:text-foreground-dark mb-4">
                Try Smart Project Hub Free
            </h1>
            <p class="text-xl text-muted dark:text-muted-dark max-w-2xl mx-auto">
                Experience all Pro features for 14 days. No credit card required.
            </p>
        </div>

        @if($eligibility['eligible'])
            <!-- Start Trial Section -->
            <div class="surface-card interactive-lift p-8 mb-8">
                <div class="text-center">
                    <div class="text-5xl mb-6">🚀</div>
                    <h2 class="text-2xl font-semibold text-foreground dark:text-foreground-dark mb-4">
                        Start Your 14-Day Free Trial
                    </h2>
                    <p class="text-muted dark:text-muted-dark mb-8">
                        Get instant access to all Pro features and see how Smart Project Hub can transform your workflow.
                    </p>
                    
                    <button onclick="startTrial()" class="btn-brand text-lg px-8 py-3">
                        Start Free Trial
                    </button>
                    
                    <p class="text-sm text-muted dark:text-muted-dark mt-4">
                        No credit card required • Cancel anytime
                    </p>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="surface-card interactive-lift p-8 mb-8">
                <h3 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-6 text-center">
                    What You'll Get During Your Trial
                </h3>
                
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($benefits as $benefit)
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-success/10 flex items-center justify-center text-success">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-sm text-foreground dark:text-foreground-dark">{{ $benefit }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Upgrade Incentives -->
            <div class="surface-card interactive-lift p-8">
                <h3 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-6 text-center">
                    Special Offer When You Upgrade
                </h3>
                
                <div class="space-y-4">
                    @foreach($incentives as $incentive)
                        <div class="flex items-center justify-between p-4 rounded-xl bg-warning/10 border border-warning/20">
                            <span class="text-sm text-foreground dark:text-foreground-dark">{{ $incentive }}</span>
                            <span class="text-xs text-warning font-medium">Limited Time</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Not Eligible Section -->
            <div class="surface-card interactive-lift p-8">
                <div class="text-center">
                    <div class="text-5xl mb-6">📝</div>
                    <h2 class="text-2xl font-semibold text-foreground dark:text-foreground-dark mb-4">
                        Trial Not Available
                    </h2>
                    <p class="text-muted dark:text-muted-dark mb-6">
                        {{ $eligibility['reason'] }}
                    </p>
                    
                    <a href="{{ route('pricing') }}" class="btn-brand">
                        View Pricing Plans
                    </a>
                </div>
            </div>
        @endif

        <!-- FAQ Section -->
        <div class="mt-16">
            <h3 class="text-2xl font-semibold text-foreground dark:text-foreground-dark mb-8 text-center">
                Frequently Asked Questions
            </h3>
            
            <div class="space-y-4">
                <div class="surface-card interactive-lift p-6">
                    <h4 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                        What happens after my trial ends?
                    </h4>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        You can choose to upgrade to a paid plan or your account will revert to the Free plan. No charges will be made without your consent.
                    </p>
                </div>
                
                <div class="surface-card interactive-lift p-6">
                    <h4 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                        Can I cancel my trial early?
                    </h4>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        Yes, you can cancel your trial at any time. Your access will continue until the end of the trial period.
                    </p>
                </div>
                
                <div class="surface-card interactive-lift p-6">
                    <h4 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                        Do I need to provide payment information?
                    </h4>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        No credit card is required to start your trial. You'll only be asked for payment information if you decide to upgrade.
                    </p>
                </div>
                
                <div class="surface-card interactive-lift p-6">
                    <h4 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                        Can I use all features during the trial?
                    </h4>
                    <p class="text-sm text-muted dark:text-muted-dark">
                        Yes! You get full access to all Pro features including unlimited projects, AI tools, team collaboration, and advanced analytics.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function startTrial() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Starting Trial...';
    
    try {
        const response = await fetch('/trial/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Failed to start trial.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while starting your trial.');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}
</script>
@endsection
