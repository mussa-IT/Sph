@props([
    'isOpen' => false,
    'transactionHash' => '',
    'network' => 'Base Sepolia',
    'title' => 'Transaction Successful',
    'message' => 'Your transaction has been confirmed on the blockchain.',
])

<div id="transaction-success-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 {{ $isOpen ? 'flex' : 'hidden' }} items-center justify-center p-4">
    <div class="surface-card max-w-md w-full text-center">
        <div class="p-8">
            <!-- Success Icon -->
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-success/10 flex items-center justify-center">
                <svg class="w-10 h-10 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-2">
                {{ $title }}
            </h2>

            <!-- Message -->
            <p class="text-muted dark:text-muted-dark mb-6">
                {{ $message }}
            </p>

            <!-- Transaction Hash -->
            @if($transactionHash)
                <div class="mb-6 p-4 rounded-xl bg-muted/10 dark:bg-muted-dark/20">
                    <p class="text-xs text-muted dark:text-muted-dark mb-2">Transaction Hash</p>
                    <div class="flex items-center justify-center gap-2">
                        <code class="text-sm font-mono text-foreground dark:text-foreground-dark break-all">
                            {{ substr($transactionHash, 0, 10) }}...{{ substr($transactionHash, -8) }}
                        </code>
                        <button 
                            onclick="navigator.clipboard.writeText('{{ $transactionHash }}')"
                            class="text-xs text-primary hover:text-primary/80"
                            title="Copy"
                        >
                            📋
                        </button>
                    </div>
                </div>
            @endif

            <!-- Network Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-info/10 text-info text-sm mb-6">
                <span class="w-2 h-2 rounded-full bg-info"></span>
                {{ $network }}
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if($transactionHash)
                    <a 
                        href="https://sepolia.basescan.org/tx/{{ $transactionHash }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-brand w-full flex items-center justify-center gap-2"
                    >
                        <span>🔍</span>
                        View on BaseScan
                    </a>
                @endif

                <button 
                    onclick="shareAchievement()"
                    class="btn-brand-muted w-full flex items-center justify-center gap-2"
                >
                    <span>📤</span>
                    Share Achievement
                </button>

                <button 
                    onclick="closeModal()"
                    class="text-sm text-muted dark:text-muted-dark hover:text-foreground dark:hover:text-foreground-dark"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('transaction-success-modal').classList.add('hidden');
    document.getElementById('transaction-success-modal').classList.remove('flex');
}

function shareAchievement() {
    const text = 'I just completed a transaction on Smart Project Hub! 🚀 #Web3 #BaseSepolia';
    
    if (navigator.share) {
        navigator.share({
            title: 'Smart Project Hub Achievement',
            text: text,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(text + ' ' + window.location.href);
        alert('Copied to clipboard!');
    }
}

// Auto-close after 10 seconds if user doesn't interact
setTimeout(() => {
    const modal = document.getElementById('transaction-success-modal');
    if (modal && !modal.classList.contains('hidden')) {
        closeModal();
    }
}, 10000);
</script>
