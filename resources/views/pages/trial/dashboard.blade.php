@extends('layouts.app')

@section('title', 'Trial Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Trial Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">
                    Your Free Trial
                </h1>
                <p class="text-muted mt-2">
                    {{ $trialStatus['plan_name'] }} • {{ $trialStatus['days_remaining'] }} days remaining
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                @if($trialStatus['can_convert'])
                    <button onclick="convertTrial()" class="btn-brand">
                        Upgrade Now
                    </button>
                @endif
                
                <button onclick="cancelTrial()" class="btn-brand-muted text-danger">
                    Cancel Trial
                </button>
            </div>
        </div>

        <!-- Trial Progress -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Trial Progress</h2>
                <span class="text-sm text-muted dark:text-muted-dark">
                    {{ $trialStatus['days_remaining'] }} of 14 days
                </span>
            </div>
            
            <div class="w-full bg-muted/20 rounded-full h-3 mb-4">
                <div class="bg-gradient-to-r from-primary to-secondary h-3 rounded-full transition-all duration-300" 
                     style="width: {{ (14 - $trialStatus['days_remaining']) / 14 * 100 }}%"></div>
            </div>
            
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted dark:text-muted-dark">
                    Started {{ now()->diffInDays($trialStatus['ends_at']) }} days ago
                </span>
                <span class="text-{{ $trialStatus['days_remaining'] <= 3 ? 'danger' : 'primary' }}">
                    {{ $trialStatus['days_remaining'] <= 3 ? 'Trial ending soon!' : 'Enjoy your trial!' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Trial Benefits -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Your Trial Benefits</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($benefits as $benefit)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center text-success">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-foreground dark:text-foreground-dark">{{ $benefit }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Upgrade Incentives -->
            @if($trialStatus['can_convert'])
                <div class="surface-card interactive-lift">
                    <div class="p-6 border-b border-muted/10">
                        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Upgrade Special Offer</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($incentives as $incentive)
                                <div class="flex items-center justify-between p-4 rounded-xl bg-warning/10 border border-warning/20">
                                    <span class="text-sm text-foreground dark:text-foreground-dark">{{ $incentive }}</span>
                                    <span class="text-xs text-warning font-medium">Limited Time</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 text-center">
                            <button onclick="convertTrial()" class="btn-brand">
                                Upgrade & Save
                            </button>
                            <p class="text-xs text-muted dark:text-muted-dark mt-2">
                                Offer expires when your trial ends
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Getting Started -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Getting Started</h2>
                </div>
                
                <div class="p-6">
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm font-bold">
                                1
                            </div>
                            <div>
                                <h3 class="font-medium text-foreground dark:text-foreground-dark mb-1">Create Your First Project</h3>
                                <p class="text-sm text-muted dark:text-muted-dark">
                                    Start by creating a project to explore all the advanced features available in your trial.
                                </p>
                                <a href="{{ route('projects.create') }}" class="btn-brand-muted text-sm mt-2 inline-block">
                                    Create Project
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm font-bold">
                                2
                            </div>
                            <div>
                                <h3 class="font-medium text-foreground dark:text-foreground-dark mb-1">Invite Team Members</h3>
                                <p class="text-sm text-muted dark:text-muted-dark">
                                    Collaborate with your team by inviting them to your projects and teams.
                                </p>
                                <a href="{{ route('teams.index') }}" class="btn-brand-muted text-sm mt-2 inline-block">
                                    Manage Teams
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm font-bold">
                                3
                            </div>
                            <div>
                                <h3 class="font-medium text-foreground dark:text-foreground-dark mb-1">Explore AI Features</h3>
                                <p class="text-sm text-muted dark:text-muted-dark">
                                    Try our AI-powered project generation and smart recommendations.
                                </p>
                                <a href="{{ route('dashboard') }}" class="btn-brand-muted text-sm mt-2 inline-block">
                                    Try AI Features
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Trial Stats -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Trial Stats</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Days Remaining</span>
                        <span class="text-sm font-medium text-{{ $trialStatus['days_remaining'] <= 3 ? 'danger' : 'primary' }}">
                            {{ $trialStatus['days_remaining'] }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Trial Plan</span>
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">
                            {{ $trialStatus['plan_name'] }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Auto-renew</span>
                        <span class="text-sm font-medium text-{{ $trialStatus['auto_renew'] ? 'success' : 'muted' }}">
                            {{ $trialStatus['auto_renew'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">Status</span>
                        <span class="inline-flex px-2 py-1 rounded-full bg-{{ $trialStatus['is_active'] ? 'success' : 'danger' }}/10 text-{{ $trialStatus['is_active'] ? 'success' : 'danger' }} text-xs font-medium">
                            {{ $trialStatus['is_active'] ? 'Active' : 'Expired' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Quick Actions</h2>
                </div>
                
                <div class="p-6 space-y-3">
                    <a href="{{ route('projects.create') }}" class="w-full btn-brand-muted text-sm text-center block">
                        Create Project
                    </a>
                    
                    <a href="{{ route('teams.create') }}" class="w-full btn-brand-muted text-sm text-center block">
                        Create Team
                    </a>
                    
                    <a href="{{ route('pricing') }}" class="w-full btn-brand text-sm text-center block">
                        View Plans
                    </a>
                    
                    <button onclick="showSupportModal()" class="w-full btn-brand-muted text-sm text-center">
                        Get Help
                    </button>
                </div>
            </div>

            <!-- Support -->
            <div class="surface-card interactive-lift">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Need Help?</h3>
                    <p class="text-sm text-muted dark:text-muted-dark mb-4">
                        Our support team is here to help you make the most of your trial.
                    </p>
                    <div class="space-y-2">
                        <a href="#" class="text-sm text-primary hover:underline block">
                            📚 Documentation
                        </a>
                        <a href="#" class="text-sm text-primary hover:underline block">
                            💬 Live Chat
                        </a>
                        <a href="#" class="text-sm text-primary hover:underline block">
                            📧 Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Trial Modal -->
<div id="cancel-trial-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Cancel Trial?</h3>
        <p class="text-sm text-muted dark:text-muted-dark mb-6">
            Are you sure you want to cancel your trial? You'll lose access to all Pro features immediately.
        </p>
        <div class="flex gap-3">
            <button onclick="closeCancelModal()" class="flex-1 btn-brand-muted text-sm">
                Keep Trial
            </button>
            <button onclick="confirmCancelTrial()" class="flex-1 btn-brand text-sm text-danger">
                Cancel Anyway
            </button>
        </div>
    </div>
</div>

<script>
async function convertTrial() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Converting...';
    
    try {
        const response = await fetch('/trial/convert', {
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
            alert(result.message || 'Failed to convert trial.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while converting your trial.');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

function cancelTrial() {
    document.getElementById('cancel-trial-modal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancel-trial-modal').classList.add('hidden');
}

async function confirmCancelTrial() {
    try {
        const response = await fetch('/trial/cancel', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Failed to cancel trial.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling your trial.');
    }
}

function showSupportModal() {
    // TODO: Implement support modal
    alert('Support modal coming soon!');
}
</script>
@endsection
