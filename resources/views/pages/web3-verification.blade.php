@extends('layouts.app')

@section('title', 'Onchain Verification')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Onchain Verification</h1>
        <p class="text-muted mt-2 max-w-2xl">Verify project authenticity and ownership using blockchain records</p>
    </div>

    <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
        <!-- Verification Form -->
        <div class="surface-card interactive-lift">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Verify Project or Wallet</h2>
            <p class="text-sm text-muted dark:text-muted-dark mb-6">Enter a project hash or wallet address to verify onchain records</p>
            
            <form id="verification-form" class="space-y-4">
                <div>
                    <label for="identifier" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                        Project Hash or Wallet Address
                    </label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        placeholder="0x..."
                        class="input-brand w-full"
                        required
                    >
                    <p class="text-xs text-muted dark:text-muted-dark mt-1">
                        Enter a 66-character project hash or 42-character wallet address
                    </p>
                </div>
                
                <button type="submit" class="btn-brand w-full" id="verify-btn">
                    <span id="verify-btn-text">Verify Onchain</span>
                </button>
            </form>
        </div>

        <!-- Verification Results -->
        <div class="surface-card interactive-lift">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Verification Results</h2>
            <div id="verification-results" class="space-y-4">
                <div class="text-center py-8 text-muted dark:text-muted-dark">
                    <div class="text-3xl mb-3">🔍</div>
                    <p class="text-sm">Enter a project hash or wallet address to see verification results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Verifications -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">How Verification Works</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">🔗</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">Immutable Records</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Project data is permanently stored on Base Sepolia blockchain</p>
            </div>
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">✅</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">Verified Ownership</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Cryptographic proof of project ownership and creation timestamp</p>
            </div>
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">🌐</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">Public Verification</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Anyone can verify authenticity using the public blockchain</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verification-form');
    const resultsContainer = document.getElementById('verification-results');
    const verifyBtn = document.getElementById('verify-btn');
    const verifyBtnText = document.getElementById('verify-btn-text');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const identifier = document.getElementById('identifier').value.trim();
        if (!identifier) return;

        // Show loading state
        setLoadingState(true);
        
        try {
            const response = await fetch('{{ route("web3.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ identifier })
            });

            const result = await response.json();
            
            if (result.success) {
                displayResults(result.data);
            } else {
                displayError(result.message);
            }
        } catch (error) {
            console.error('Verification error:', error);
            displayError('Failed to verify. Please try again later.');
        } finally {
            setLoadingState(false);
        }
    });

    function setLoadingState(isLoading) {
        verifyBtn.disabled = isLoading;
        if (isLoading) {
            verifyBtnText.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block"></span> Verifying...';
        } else {
            verifyBtnText.textContent = 'Verify Onchain';
        }
    }

    function displayResults(data) {
        let html = '';
        
        if (data.type === 'project') {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-success"></span>
                            Project Verified
                        </span>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10">
                        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">${data.project.title}</h3>
                        <p class="text-sm text-muted dark:text-muted-dark mb-3">${data.project.description}</p>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted dark:text-muted-dark">Owner:</span>
                                <span class="font-medium">${data.project.owner}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted dark:text-muted-dark">Wallet:</span>
                                <code class="text-xs">${data.project.wallet_address}</code>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted dark:text-muted-dark">Verified:</span>
                                <span>${new Date(data.project.verified_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Project Hash:</span>
                            <button onclick="navigator.clipboard.writeText('${data.project.hash}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                        </div>
                        <code class="block text-xs p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20 break-all">${data.project.hash}</code>
                    </div>
                    
                    <a href="${data.project.explorer_link}" target="_blank" class="btn-brand-muted text-sm w-full text-center">
                        View on BaseScan
                    </a>
                </div>
            `;
        } else if (data.type === 'wallet') {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-info/10 text-info text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-info"></span>
                            Wallet Verified
                        </span>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted dark:text-muted-dark">Owner:</span>
                                <span class="font-medium">${data.owner}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted dark:text-muted-dark">Projects:</span>
                                <span class="font-medium">${data.projects_count}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Wallet Address:</span>
                            <button onclick="navigator.clipboard.writeText('${data.wallet_address}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                        </div>
                        <code class="block text-xs p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20 break-all">${data.wallet_address}</code>
                    </div>
                    
                    ${data.projects.length > 0 ? `
                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-foreground dark:text-foreground-dark">Verified Projects:</h4>
                            <div class="space-y-2">
                                ${data.projects.map(project => `
                                    <div class="p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium">${project.title}</span>
                                            <a href="${project.explorer_link}" target="_blank" class="text-xs text-primary hover:text-primary/80">View Tx</a>
                                        </div>
                                        <div class="text-xs text-muted dark:text-muted-dark mt-1">
                                            Verified ${new Date(project.verified_at).toLocaleDateString()}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : '<p class="text-sm text-muted dark:text-muted-dark">No verified projects found</p>'}
                    
                    <a href="https://sepolia.basescan.org/address/${data.wallet_address}" target="_blank" class="btn-brand-muted text-sm w-full text-center">
                        View Wallet on BaseScan
                    </a>
                </div>
            `;
        }
        
        resultsContainer.innerHTML = html;
    }

    function displayError(message) {
        resultsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="text-3xl mb-3 text-danger">❌</div>
                <p class="text-sm text-danger font-medium">${message}</p>
            </div>
        `;
    }
});
</script>
@endsection
