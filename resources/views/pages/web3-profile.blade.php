@extends('layouts.app')

@section('title', 'Web3 Profile')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Web3 Profile</h1>
        <p class="text-muted mt-2 max-w-2xl">Your onchain identity, achievements, and verified projects</p>
    </div>

    <!-- Wallet Status Card -->
    <div class="grid gap-6 grid-cols-1 lg:grid-cols-3 mb-8">
        <div class="lg:col-span-2">
            <div class="surface-card interactive-lift bg-background-secondary dark:bg-background-secondary-dark">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Wallet Connection</h2>
                        <p class="text-sm text-muted dark:text-muted-dark mt-1">Your onchain identity status</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($walletStats['wallet_connected'])
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                <span class="w-2 h-2 rounded-full bg-success"></span>
                                Connected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium">
                                <span class="w-2 h-2 rounded-full bg-warning"></span>
                                Not Connected
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark mb-1">Network</p>
                        <p class="text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $walletStats['network'] ?? 'Base Sepolia' }}</p>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark mb-1">Onchain Projects</p>
                        <p class="text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $walletStats['onchain_projects'] }}</p>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10 md:col-span-2">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark mb-1">Wallet Address</p>
                        @if($walletStats['wallet_address'])
                            <div class="flex items-center gap-2">
                                <code class="text-sm font-mono text-foreground dark:text-foreground-dark">{{ $walletStats['wallet_address'] }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ $walletStats['wallet_address'] }}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                                @if($walletStats['explorer_link'])
                                    <a href="{{ $walletStats['explorer_link'] }}" target="_blank" class="text-xs text-primary hover:text-primary/80">View on Explorer</a>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-muted dark:text-muted-dark">Connect your wallet to display address</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="surface-card interactive-lift bg-background-secondary dark:bg-background-secondary-dark">
            <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Onchain Stats</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted dark:text-muted-dark">Total Transactions</span>
                    <span class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $walletStats['total_transactions'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted dark:text-muted-dark">Badges Earned</span>
                    <span class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ count($badges) }}</span>
                </div>
                @if($walletStats['first_transaction'])
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted dark:text-muted-dark">First Transaction</span>
                        <span class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ \Carbon\Carbon::parse($walletStats['first_transaction'])->format('M j, Y') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Badges Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">Earned Badges</h2>
        @if(count($badges) > 0)
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($badges as $badge)
                    <div class="surface-card interactive-lift p-6 text-center">
                        <div class="text-4xl mb-3">{{ $badge['icon'] }}</div>
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-1">{{ $badge['name'] }}</h3>
                        <p class="text-sm text-muted dark:text-muted-dark mb-3">{{ $badge['description'] }}</p>
                        <div class="flex items-center justify-center gap-2">
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                @if($badge['rarity'] === 'rare') bg-warning/10 text-warning
                                @elseif($badge['rarity'] === 'epic') bg-danger/10 text-danger
                                @else bg-info/10 text-info
                                ">
                                {{ ucfirst($badge['rarity']) }}
                            </span>
                            @if($badge['earned_at'])
                                <span class="text-xs text-muted dark:text-muted-dark">
                                    Earned {{ \Carbon\Carbon::parse($badge['earned_at'])->format('M j, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="surface-card p-8 text-center">
                <div class="text-4xl mb-4">🏆</div>
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Badges Yet</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Complete projects and achieve milestones to earn badges</p>
            </div>
        @endif
    </div>

    <!-- Onchain Projects -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark">Verified Projects</h2>
            <span class="text-sm text-muted dark:text-muted-dark">{{ $onchainProjects->count() }} projects</span>
        </div>
        
        @if($onchainProjects->count() > 0)
            <div class="grid gap-4">
                @foreach($onchainProjects as $project)
                    <div class="surface-card interactive-lift p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $project->title }}</h3>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                        <span class="w-2 h-2 rounded-full bg-success"></span>
                                        Verified
                                    </span>
                                </div>
                                <p class="text-sm text-muted dark:text-muted-dark mb-3">{{ $project->description }}</p>
                                
                                <div class="flex items-center gap-4 text-xs text-muted dark:text-muted-dark">
                                    <span>{{ $project->tasks_count ?? 0 }} tasks</span>
                                    <span>{{ $project->budgets_count ?? 0 }} budgets</span>
                                    <span>Verified {{ $project->blockchain_verified_at->format('M j, Y') }}</span>
                                </div>

                                @if($project->transaction_hash)
                                    <div class="mt-3 flex items-center gap-2">
                                        <a href="https://sepolia.basescan.org/tx/{{ $project->transaction_hash }}" 
                                           target="_blank" 
                                           class="text-xs text-primary hover:text-primary/80">
                                            View Transaction
                                        </a>
                                        <span class="text-xs text-muted dark:text-muted-dark">
                                            {{ substr($project->transaction_hash, 0, 10) }}...{{ substr($project->transaction_hash, -8) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="ml-4">
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="btn-brand-muted text-sm">
                                    View Project
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="surface-card p-8 text-center">
                <div class="text-4xl mb-4">🔗</div>
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Onchain Projects Yet</h3>
                <p class="text-sm text-muted dark:text-muted-dark mb-4">Publish your projects onchain to establish verifiable ownership</p>
                <a href="{{ route('projects.index') }}" class="btn-brand">Create Project</a>
            </div>
        @endif
    </div>
</div>
@endsection
