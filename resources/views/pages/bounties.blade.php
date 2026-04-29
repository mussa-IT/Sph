@extends('layouts.app')

@section('title', 'Bounty Marketplace')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Bounty Marketplace</h1>
        <p class="text-muted mt-2 max-w-2xl">Find and complete bounties, or create your own to get help with your projects</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-4 mb-8">
        <!-- Create Bounty Button -->
        <div class="lg:col-span-1">
            <button onclick="openCreateBountyModal()" class="btn-brand w-full">
                <span class="flex items-center justify-center gap-2">
                    <span>+</span>
                    Create Bounty
                </span>
            </button>
        </div>

        <!-- Filters -->
        <div class="lg:col-span-3">
            <div class="surface-card interactive-lift p-4">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            placeholder="Search bounties..."
                            class="input-brand w-full"
                        >
                    </div>
                    <select class="input-brand">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="assigned">Assigned</option>
                        <option value="completed">Completed</option>
                    </select>
                    <select class="input-brand">
                        <option value="">Sort by</option>
                        <option value="newest">Newest</option>
                        <option value="reward_highest">Highest Reward</option>
                        <option value="deadline_soonest">Deadline Soonest</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bounties Grid -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Sample Bounty Cards -->
        <div class="surface-card interactive-lift">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                <span class="w-2 h-2 rounded-full bg-success"></span>
                                Open
                            </span>
                            <span class="text-xs text-muted dark:text-muted-dark">
                                Due in 7 days
                            </span>
                        </div>
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                            Build React Component Library
                        </h3>
                        <p class="text-sm text-muted dark:text-muted-dark line-clamp-2">
                            Create a reusable React component library with 20+ components, TypeScript support, and Storybook documentation.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Reward</p>
                        <p class="text-lg font-bold text-foreground dark:text-foreground-dark">500 USDC</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Creator</p>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">0x1234...5678</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="btn-brand flex-1 text-sm" onclick="submitSolution()">
                        Submit Solution
                    </button>
                    <button class="btn-brand-muted flex-1 text-sm">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <div class="surface-card interactive-lift">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium">
                                <span class="w-2 h-2 rounded-full bg-warning"></span>
                                Assigned
                            </span>
                            <span class="text-xs text-muted dark:text-muted-dark">
                                Due in 3 days
                            </span>
                        </div>
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                            Smart Contract Audit
                        </h3>
                        <p class="text-sm text-muted dark:text-muted-dark line-clamp-2">
                            Audit a DeFi smart contract for security vulnerabilities and gas optimization opportunities.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Reward</p>
                        <p class="text-lg font-bold text-foreground dark:text-foreground-dark">1000 USDC</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Creator</p>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">0xabcd...efgh</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="btn-brand-muted flex-1 text-sm" disabled>
                        Already Assigned
                    </button>
                    <button class="btn-brand-muted flex-1 text-sm">
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <div class="surface-card interactive-lift">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                <span class="w-2 h-2 rounded-full bg-success"></span>
                                Open
                            </span>
                            <span class="text-xs text-muted dark:text-muted-dark">
                                Due in 14 days
                            </span>
                        </div>
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">
                            UI/UX Design for Dashboard
                        </h3>
                        <p class="text-sm text-muted dark:text-muted-dark line-clamp-2">
                            Design a modern, responsive dashboard UI with dark mode support and accessibility features.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Reward</p>
                        <p class="text-lg font-bold text-foreground dark:text-foreground-dark">300 USDC</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-muted dark:text-muted-dark mb-1">Creator</p>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">0x9876...5432</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="btn-brand flex-1 text-sm" onclick="submitSolution()">
                        Submit Solution
                    </button>
                    <button class="btn-brand-muted flex-1 text-sm">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- My Bounties Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">My Bounties</h2>
        <div class="grid gap-4">
            <div class="surface-card interactive-lift p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark">API Integration for Payment Gateway</h3>
                        <p class="text-sm text-muted dark:text-muted-dark mt-1">Created by you • Reward: 750 USDC</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                            Open
                        </span>
                        <button class="btn-brand-muted text-sm">Manage</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Bounty Modal -->
<div id="create-bounty-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="surface-card max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-muted/10 dark:border-muted-dark/10">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Create Bounty</h2>
                <button onclick="closeCreateBountyModal()" class="text-muted hover:text-foreground dark:hover:text-foreground-dark">
                    ✕
                </button>
            </div>
        </div>

        <form id="create-bounty-form" class="p-6 space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                    Title
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Brief description of the bounty"
                    class="input-brand w-full"
                    required
                >
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Detailed description of requirements and deliverables"
                    class="input-brand w-full"
                    required
                ></textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="amount" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                        Reward Amount (USDC)
                    </label>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        placeholder="100"
                        min="1"
                        class="input-brand w-full"
                        required
                    >
                </div>

                <div>
                    <label for="deadline" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                        Deadline
                    </label>
                    <input
                        type="date"
                        id="deadline"
                        name="deadline"
                        class="input-brand w-full"
                        required
                    >
                </div>
            </div>

            <div class="p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
                <p class="text-sm text-muted dark:text-muted-dark mb-2">
                    <span class="font-medium">Note:</span> You'll need to approve USDC transfer and sign the transaction to create this bounty.
                </p>
                <p class="text-xs text-muted dark:text-muted-dark">
                    Make sure you have sufficient USDC on Base Sepolia testnet.
                </p>
            </div>

            <button type="submit" class="btn-brand w-full" id="create-bounty-btn">
                <span id="create-bounty-btn-text">Create Bounty</span>
            </button>
        </form>
    </div>
</div>

<script>
function openCreateBountyModal() {
    document.getElementById('create-bounty-modal').classList.remove('hidden');
}

function closeCreateBountyModal() {
    document.getElementById('create-bounty-modal').classList.add('hidden');
}

function submitSolution() {
    alert('Solution submission form would open here. Requires wallet connection.');
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-bounty-form');
    const createBtn = document.getElementById('create-bounty-btn');
    const createBtnText = document.getElementById('create-bounty-btn-text');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Check if wallet is connected
        if (!window.ethereum || !window.ethereum.selectedAddress) {
            alert('Please connect your wallet first');
            return;
        }

        // Show loading state
        createBtn.disabled = true;
        createBtnText.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block"></span> Creating...';

        try {
            // In production, this would interact with the BountyEscrow smart contract
            // For now, simulate the creation
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            alert('Bounty created successfully! (Demo mode - smart contract integration required)');
            closeCreateBountyModal();
            window.location.reload();
        } catch (error) {
            console.error('Create bounty error:', error);
            alert('Failed to create bounty. Please try again.');
        } finally {
            createBtn.disabled = false;
            createBtnText.textContent = 'Create Bounty';
        }
    });
});
</script>
@endsection
