@extends('layouts.app')

@section('title', 'Referral Program')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Referral Program</h1>
        <p class="text-muted mt-2">Earn rewards by inviting friends to join Smart Project Hub</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Referral Stats -->
            <div class="surface-card interactive-lift p-6">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-6">Your Referral Stats</h2>
                
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $referralStats['total_referrals'] }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Total Referrals</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $referralStats['converted_referrals'] }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Converted</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ number_format($referralStats['total_earnings'], 2) }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Total Earnings</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ number_format($referralStats['conversion_rate'], 1) }}%</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Conversion Rate</div>
                    </div>
                </div>
            </div>

            <!-- Referral Code -->
            <div class="surface-card interactive-lift p-6">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Your Referral Code</h2>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-xl bg-primary/10 border border-primary/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted dark:text-muted-dark mb-1">Share this code with friends:</p>
                                <p class="text-2xl font-bold text-primary font-mono">{{ $referralCode }}</p>
                            </div>
                            <button onclick="copyToClipboard('{{ $referralCode }}')" class="btn-brand text-sm">
                                Copy Code
                            </button>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-muted/10">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted dark:text-muted-dark mb-1">Or share this link:</p>
                                <p class="text-sm text-foreground dark:text-foreground-dark font-mono truncate">{{ $referralUrl }}</p>
                            </div>
                            <button onclick="copyToClipboard('{{ $referralUrl }}')" class="btn-brand-muted text-sm">
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Share Buttons -->
                <div class="mt-6">
                    <p class="text-sm font-medium text-foreground dark:text-foreground-dark mb-3">Share via:</p>
                    <div class="flex gap-3">
                        <button onclick="shareOnTwitter()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500 text-white text-sm hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 00-2.143-2.727c-.829.14-1.751.48-2.714 1.013a9.362 9.362 0 00-6.348-2.334c-.022 0-.033 0-.033.001a9.564 9.564 0 005.84 8.25c-.2.037-.4.074-.602.11a4.968 4.968 0 00-3.748-1.665c-.052 0-.087.002-.136.002a4.945 4.945 0 00-2.484 1.307 4.928 4.928 0 00-2.16-3.37 4.935 4.935 0 00-1.777-6.477 4.935 4.935 0 001.777-6.477 4.935 4.935 0 002.16-3.37 4.945 4.945 0 002.484 1.307c.049 0 .084-.002.136-.002a4.928 4.928 0 003.748-1.665 4.963 4.963 0 00.602.11 9.56 9.56 0 005.84-8.25c.001 0 .001-.001.001-.001a9.362 9.362 0 006.348-2.334 4.958 4.958 0 002.143 2.727 10 10 0 012.825-.775 4.936 4.936 0 01-2.24 2.398 4.935 4.935 0 001.967 2.92 9.9 9.9 0 01-6.118 2.107c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.01-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </button>
                        
                        <button onclick="shareOnLinkedIn()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-700 text-white text-sm hover:bg-blue-800 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </button>
                        
                        <button onclick="shareOnEmail()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-muted/20 text-foreground dark:text-foreground-dark text-sm hover:bg-muted/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Referrals -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Recent Referrals</h2>
                </div>
                
                <div class="p-6">
                    @if($referrals->count() > 0)
                        <div class="space-y-4">
                            @foreach($referrals as $referral)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-muted/10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-sm">
                                            @if($referral->referredUser)
                                                {{ $referral->referredUser->name->charAt(0) }}
                                            @else
                                                ?
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground dark:text-foreground-dark">
                                                @if($referral->referredUser)
                                                    {{ $referral->referredUser->name }}
                                                @else
                                                    {{ $referral->referred_email }}
                                                @endif
                                            </p>
                                            <p class="text-sm text-muted dark:text-muted-dark">
                                                {{ $referral->getStatusLabel() }} • {{ $referral->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex px-2 py-1 rounded-full bg-{{ $referral->getStatusColor() }}/10 text-{{ $referral->getStatusColor() }} text-xs font-medium">
                                            {{ $referral->getStatusLabel() }}
                                        </span>
                                        
                                        @if($referral->isPending())
                                            <button onclick="resendInvitation({{ $referral->id }})" class="text-xs text-primary hover:text-primary/80">
                                                Resend
                                            </button>
                                            <button onclick="cancelReferral({{ $referral->id }})" class="text-xs text-danger hover:text-danger/80">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $referrals->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-4">🎁</div>
                            <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Referrals Yet</h3>
                            <p class="text-sm text-muted dark:text-muted-dark mb-4">
                                Start sharing your referral code to earn rewards!
                            </p>
                            <button onclick="copyToClipboard('{{ $referralCode }}')" class="btn-brand text-sm">
                                Copy Referral Code
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- How It Works -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">How It Works</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold mt-0.5">
                            1
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Share Your Code</p>
                            <p class="text-xs text-muted dark:text-muted-dark">Share your unique referral code with friends</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold mt-0.5">
                            2
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Friends Sign Up</p>
                            <p class="text-xs text-muted dark:text-muted-dark">They register using your referral code</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold mt-0.5">
                            3
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">They Convert</p>
                            <p class="text-xs text-muted dark:text-muted-dark">When they upgrade, you earn rewards</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-success/10 flex items-center justify-center text-success text-xs font-bold mt-0.5">
                            4
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Get Rewarded</p>
                            <p class="text-xs text-muted dark:text-muted-dark">Earn credits, discounts, or upgrades</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reward Structure -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Reward Structure</h2>
                </div>
                
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-success/10 border border-success/20">
                        <div class="flex items-center gap-2">
                            <span class="text-success">💰</span>
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Standard Reward</span>
                        </div>
                        <span class="text-sm text-success font-medium">$10 Credit</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-xl bg-warning/10 border border-warning/20">
                        <div class="flex items-center gap-2">
                            <span class="text-warning">🎯</span>
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">5 Referrals</span>
                        </div>
                        <span class="text-sm text-warning font-medium">$15 Credit</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-xl bg-info/10 border border-info/20">
                        <div class="flex items-center gap-2">
                            <span class="text-info">🏆</span>
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">10 Referrals</span>
                        </div>
                        <span class="text-sm text-info font-medium">$20 Credit</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-xl bg-primary/10 border border-primary/20">
                        <div class="flex items-center gap-2">
                            <span class="text-primary">👑</span>
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">25 Referrals</span>
                        </div>
                        <span class="text-sm text-primary font-medium">Free Upgrade</span>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Top Referrers</h2>
                </div>
                
                <div class="p-6">
                    <div id="leaderboard" class="space-y-3">
                        <!-- Leaderboard will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadLeaderboard();
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Copied to clipboard!');
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}

function shareOnTwitter() {
    const text = 'Check out Smart Project Hub - the best project management platform!';
    const url = '{{ $referralUrl }}';
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
}

function shareOnLinkedIn() {
    const url = '{{ $referralUrl }}';
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
}

function shareOnEmail() {
    const subject = 'Join me on Smart Project Hub';
    const body = `Hi! I've been using Smart Project Hub and thought you might find it useful too. You can sign up using my referral code: {{ $referralCode }}\n\n${{ $referralUrl }}`;
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-success text-white px-4 py-2 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

async function resendInvitation(referralId) {
    try {
        const response = await fetch(`/referrals/${referralId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Invitation resent successfully!');
        } else {
            alert(result.message || 'Failed to resend invitation.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while resending the invitation.');
    }
}

async function cancelReferral(referralId) {
    if (!confirm('Are you sure you want to cancel this referral?')) {
        return;
    }
    
    try {
        const response = await fetch(`/referrals/${referralId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Failed to cancel referral.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the referral.');
    }
}

async function loadLeaderboard() {
    try {
        const response = await fetch('/referrals/leaderboard');
        const result = await response.json();
        
        if (result.success) {
            const leaderboard = document.getElementById('leaderboard');
            leaderboard.innerHTML = result.leaderboard.map((user, index) => `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-${index === 0 ? 'warning' : (index === 1 ? 'muted' : 'info')}/10 flex items-center justify-center text-${index === 0 ? 'warning' : (index === 1 ? 'muted' : 'info')} text-xs font-bold">
                            ${index + 1}
                        </div>
                        <span class="text-sm text-foreground dark:text-foreground-dark">${user.name}</span>
                    </div>
                    <span class="text-sm text-muted dark:text-muted-dark">${user.referrals_count} referrals</span>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading leaderboard:', error);
    }
}
</script>
@endsection
