@extends('layouts.app')

@section('title', 'Accept Referral Invitation')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary/5 via-background to-secondary/5">
    <div class="container mx-auto px-4 py-16 max-w-md">
        <div class="surface-card interactive-lift p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="text-4xl mb-4">🎁</div>
                <h1 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">
                    You've Been Invited!
                </h1>
                <p class="text-muted dark:text-muted-dark">
                    Join Smart Project Hub with {{ $referral->referrer->name }}'s referral and get special rewards.
                </p>
            </div>

            <!-- Referral Info -->
            <div class="p-4 rounded-xl bg-primary/10 border border-primary/20 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-primary">Referral Code:</span>
                    <span class="font-mono text-sm text-primary">{{ $referral->referral_code }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-primary">Your Reward:</span>
                    <span class="text-sm text-primary">{{ $referral->getRewardLabel() }}</span>
                </div>
            </div>

            <!-- Accept Form -->
            <form id="accept-referral-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" required
                           class="input-brand w-full" 
                           placeholder="your@email.com"
                           value="{{ $referral->referred_email ?? '' }}">
                </div>
                
                <div class="p-3 rounded-xl bg-info/10 border border-info/20">
                    <p class="text-xs text-info">
                        <strong>What happens next:</strong><br>
                        1. Enter your email above<br>
                        2. Complete your registration<br>
                        3. Your reward will be applied automatically
                    </p>
                </div>
                
                <button type="submit" class="w-full btn-brand">
                    Accept Invitation & Sign Up
                </button>
            </form>

            <!-- Alternative -->
            <div class="mt-6 text-center">
                <p class="text-sm text-muted dark:text-muted-dark mb-3">
                    Already have an account?
                </p>
                <a href="{{ route('login') }}" class="btn-brand-muted text-sm">
                    Sign In Instead
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('accept-referral-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Processing...';
        
        try {
            const response = await fetch('/referrals/{{ $referral->referral_code }}/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    email: formData.get('email'),
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Redirect to registration page
                window.location.href = result.redirect_url;
            } else {
                alert(result.message || 'Failed to process referral.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while processing the referral.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
});
</script>
@endsection
