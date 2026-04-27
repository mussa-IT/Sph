@extends('layouts.app')

@section('title', 'Billing History')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Billing History</h1>
        <p class="text-muted mt-2 max-w-2xl">View your payment history, download invoices, and manage your subscription.</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Current Subscription -->
        <div class="lg:col-span-1">
            @if($subscription)
                <div class="surface-card interactive-lift">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Current Plan</h2>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-success"></span>
                            {{ $subscription->status === 'trial' ? 'Trial' : 'Active' }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted dark:text-muted-dark mb-1">Plan</p>
                            <p class="text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $subscription->plan->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted dark:text-muted-dark mb-1">Billing Cycle</p>
                            <p class="text-foreground dark:text-foreground-dark">{{ $subscription->getFormattedBillingCycle() }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted dark:text-muted-dark mb-1">Price</p>
                            <p class="text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $subscription->getFormattedPrice() }}</p>
                        </div>

                        @if($subscription->isOnTrial())
                            <div class="p-3 rounded-xl bg-warning/10 border border-warning/20">
                                <div class="flex items-center gap-2">
                                    <span class="text-warning">⏰</span>
                                    <div>
                                        <p class="text-sm font-medium text-warning">Trial Active</p>
                                        <p class="text-xs text-warning">{{ $subscription->getTrialDaysRemaining() }} days remaining</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm text-muted dark:text-muted-dark mb-1">Next Billing</p>
                            <p class="text-foreground dark:text-foreground-dark">
                                {{ $subscription->getNextBillingDate()?->format('M j, Y') ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-muted/10 space-y-3">
                            @if($subscription->auto_renew)
                                <button onclick="cancelSubscription()" class="w-full btn-brand-muted text-sm">
                                    Cancel Subscription
                                </button>
                            @else
                                <button onclick="resumeSubscription()" class="w-full btn-brand text-sm">
                                    Resume Subscription
                                </button>
                            @endif
                            
                            <a href="{{ route('pricing') }}" class="w-full btn-brand-muted text-sm text-center">
                                Change Plan
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="surface-card interactive-lift">
                    <div class="text-center">
                        <div class="text-4xl mb-4">💳</div>
                        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Active Subscription</h3>
                        <p class="text-sm text-muted dark:text-muted-dark mb-4">You're currently on the Free plan</p>
                        <a href="{{ route('pricing') }}" class="btn-brand text-sm">
                            Upgrade Now
                        </a>
                    </div>
                </div>
            @endif

            <!-- Payment Methods -->
            <div class="surface-card interactive-lift mt-6">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Payment Method</h2>
                <div class="space-y-3">
                    @if($subscription && $subscription->payment_gateway)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-muted/10">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-400 rounded flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">
                                        {{ strtoupper(substr($subscription->payment_gateway, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                        {{ ucfirst($subscription->payment_gateway) }}
                                    </p>
                                    <p class="text-xs text-muted dark:text-muted-dark">
                                        {{ $subscription->gateway_customer_id ? '•••• ' . substr($subscription->gateway_customer_id, -4) : 'Connected' }}
                                    </p>
                                </div>
                            </div>
                            <button class="text-xs text-primary hover:text-primary/80">
                                Update
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-muted dark:text-muted-dark">No payment method on file</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Billing History -->
        <div class="lg:col-span-2">
            <div class="surface-card interactive-lift">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Payment History</h2>
                    <div class="flex items-center gap-2">
                        <select class="input-brand text-sm" id="filter-type">
                            <option value="all">All Types</option>
                            <option value="payment">Payments</option>
                            <option value="refund">Refunds</option>
                            <option value="credit">Credits</option>
                        </select>
                        <select class="input-brand text-sm" id="filter-status">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                @if($billingHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-muted/10">
                                    <th class="text-left py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Date</th>
                                    <th class="text-left py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Description</th>
                                    <th class="text-left py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Type</th>
                                    <th class="text-left py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Status</th>
                                    <th class="text-right py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Amount</th>
                                    <th class="text-center py-3 px-4 text-sm font-medium text-muted dark:text-muted-dark">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($billingHistory as $item)
                                    <tr class="border-b border-muted/5 hover:bg-muted/5 transition-colors">
                                        <td class="py-4 px-4">
                                            <div>
                                                <p class="text-sm text-foreground dark:text-foreground-dark">
                                                    {{ $item->getFormattedDate() }}
                                                </p>
                                                @if($item->processed_at && $item->processed_at->format('Y-m-d') !== $item->created_at->format('Y-m-d'))
                                                    <p class="text-xs text-muted dark:text-muted-dark">
                                                        Processed {{ $item->processed_at->format('M j, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div>
                                                <p class="text-sm text-foreground dark:text-foreground-dark">
                                                    {{ $item->description }}
                                                </p>
                                                @if($item->subscription)
                                                    <p class="text-xs text-muted dark:text-muted-dark">
                                                {{ $item->subscription->plan->name }} Plan
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium
                                                @if($item->isPayment()) bg-success/10 text-success
                                                @elseif($item->isRefund()) bg-danger/10 text-danger
                                                @elseif($item->isCredit()) bg-info/10 text-info
                                                @else bg-muted/10 text-muted
                                                @endif">
                                                {{ $item->getTypeLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-{{ $item->getStatusColor() }}/10 text-{{ $item->getStatusColor() }}">
                                                {{ $item->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                                {{ $item->getFormattedAmount() }}
                                            </p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($item->invoice_number)
                                                    <a href="{{ route('subscription.download_invoice', $item) }}" 
                                                       class="text-xs text-primary hover:text-primary/80" 
                                                       title="Download Invoice">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if($item->receipt_url)
                                                    <a href="{{ $item->receipt_url }}" target="_blank" 
                                                       class="text-xs text-primary hover:text-primary/80" 
                                                       title="View Receipt">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex items-center justify-between">
                        <p class="text-sm text-muted dark:text-muted-dark">
                            Showing {{ $billingHistory->firstItem() }} to {{ $billingHistory->lastItem() }} of {{ $billingHistory->total() }} results
                        </p>
                        {{ $billingHistory->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-4xl mb-4">📄</div>
                        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No billing history</h3>
                        <p class="text-sm text-muted dark:text-muted-dark">
                            Your payment history will appear here once you make your first purchase.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div id="cancel-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Cancel Subscription?</h3>
        <p class="text-sm text-muted dark:text-muted-dark mb-6">
            Are you sure you want to cancel your subscription? You'll continue to have access until the end of your current billing period.
        </p>
        <div class="flex gap-3">
            <button onclick="closeCancelModal()" class="flex-1 btn-brand-muted text-sm">
                Keep Subscription
            </button>
            <button onclick="confirmCancel()" class="flex-1 btn-brand text-sm">
                Cancel Anyway
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterType = document.getElementById('filter-type');
    const filterStatus = document.getElementById('filter-status');
    
    if (filterType && filterStatus) {
        const applyFilters = () => {
            const type = filterType.value;
            const status = filterStatus.value;
            const url = new URL(window.location);
            
            if (type !== 'all') url.searchParams.set('type', type);
            else url.searchParams.delete('type');
            
            if (status !== 'all') url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            
            window.location.href = url.toString();
        };
        
        filterType.addEventListener('change', applyFilters);
        filterStatus.addEventListener('change', applyFilters);
    }
});

function cancelSubscription() {
    document.getElementById('cancel-modal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancel-modal').classList.add('hidden');
}

function confirmCancel() {
    const subscriptionId = {{ $subscription?->id ?? 'null' }};
    
    if (!subscriptionId) {
        alert('No active subscription found.');
        return;
    }

    fetch(`/subscription/cancel/${subscriptionId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCancelModal();
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel subscription.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while cancelling your subscription.');
    });
}

function resumeSubscription() {
    const subscriptionId = {{ $subscription?->id ?? 'null' }};
    
    if (!subscriptionId) {
        alert('No active subscription found.');
        return;
    }

    fetch(`/subscription/resume/${subscriptionId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to resume subscription.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while resuming your subscription.');
    });
}
</script>
@endsection
