@props([
    'projectsMinted' => 0,
    'badgesEarned' => 0,
    'walletConnectedUsers' => 0,
    'recentTransactions' => [],
])

<div class="surface-card interactive-lift p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Onchain Activity</h3>
        <span class="text-xs text-muted dark:text-muted-dark">Base Sepolia</span>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">🔗</span>
                <span class="text-xs text-muted dark:text-muted-dark">Projects Minted</span>
            </div>
            <p class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $projectsMinted }}</p>
        </div>

        <div class="p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">🏆</span>
                <span class="text-xs text-muted dark:text-muted-dark">Badges Earned</span>
            </div>
            <p class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $badgesEarned }}</p>
        </div>

        <div class="p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">👛</span>
                <span class="text-xs text-muted dark:text-muted-dark">Wallets Connected</span>
            </div>
            <p class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $walletConnectedUsers }}</p>
        </div>

        <div class="p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">📊</span>
                <span class="text-xs text-muted dark:text-muted-dark">Total Transactions</span>
            </div>
            <p class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ count($recentTransactions) }}</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div>
        <h4 class="text-sm font-semibold text-foreground dark:text-foreground-dark mb-3">Recent Transactions</h4>
        
        @if(count($recentTransactions) > 0)
            <div class="space-y-2">
                @foreach($recentTransactions as $tx)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/5 dark:bg-muted-dark/10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center">
                                <span class="text-sm">✓</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground dark:text-foreground-dark">{{ $tx['type'] }}</p>
                                <p class="text-xs text-muted dark:text-muted-dark">{{ $tx['description'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="{{ $tx['explorer_link'] }}" target="_blank" class="text-xs text-primary hover:text-primary/80">
                                View
                            </a>
                            <p class="text-xs text-muted dark:text-muted-dark mt-1">
                                {{ \Carbon\Carbon::parse($tx['timestamp'])->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4 text-muted dark:text-muted-dark text-sm">
                No recent transactions
            </div>
        @endif
    </div>

    <!-- Activity Chart Placeholder -->
    <div class="mt-6">
        <h4 class="text-sm font-semibold text-foreground dark:text-foreground-dark mb-3">Activity Trend (7 Days)</h4>
        <div class="h-32 rounded-lg bg-muted/10 dark:bg-muted-dark/20 flex items-end justify-around p-4">
            @for($i = 0; $i < 7; $i++)
                <div class="w-8 bg-primary/20 rounded-t" style="height: {{ rand(20, 100) }}%"></div>
            @endfor
        </div>
        <div class="flex justify-around mt-2 text-xs text-muted dark:text-muted-dark">
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
            <span>Sun</span>
        </div>
    </div>
</div>
