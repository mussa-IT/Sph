@extends('layouts.app')

@section('title', 'Blueprint Verification')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Blueprint Verification</h1>
        <p class="text-muted mt-2 max-w-2xl">Verify the authenticity of blueprints using blockchain-anchored hashes</p>
    </div>

    <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
        <!-- Verification Form -->
        <div class="surface-card interactive-lift">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Verify Blueprint</h2>
            <p class="text-sm text-muted dark:text-muted-dark mb-6">Upload a file to verify its authenticity against the stored blockchain hash</p>
            
            <form id="verification-form" class="space-y-4">
                <div>
                    <label for="blueprint_id" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                        Select Blueprint
                    </label>
                    <select id="blueprint_id" name="blueprint_id" class="input-brand w-full" required>
                        <option value="">Choose a blueprint...</option>
                        @foreach($blueprints as $blueprint)
                            <option value="{{ $blueprint->id }}">{{ $blueprint->title }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="file" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                        Upload File to Verify
                    </label>
                    <input
                        type="file"
                        id="file"
                        name="file"
                        accept=".pdf,.png,.jpg,.jpeg"
                        class="input-brand w-full"
                        required
                    >
                    <p class="text-xs text-muted dark:text-muted-dark mt-1">
                        Upload the same file type as the original blueprint
                    </p>
                </div>
                
                <button type="submit" class="btn-brand w-full" id="verify-btn">
                    <span id="verify-btn-text">Verify Authenticity</span>
                </button>
            </form>
        </div>

        <!-- Verification Results -->
        <div class="surface-card interactive-lift">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Verification Results</h2>
            <div id="verification-results" class="space-y-4">
                <div class="text-center py-8 text-muted dark:text-muted-dark">
                    <div class="text-3xl mb-3">🔍</div>
                    <p class="text-sm">Select a blueprint and upload a file to see verification results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">How Verification Works</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">📄</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">File Hashing</h3>
                <p class="text-sm text-muted dark:text-muted-dark">SHA256 hash is calculated for the uploaded file</p>
            </div>
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">🔗</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">Blockchain Comparison</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Hash is compared against the anchored blockchain hash</p>
            </div>
            <div class="surface-card p-6 text-center">
                <div class="text-3xl mb-3">✅</div>
                <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-2">Authenticity Proof</h3>
                <p class="text-sm text-muted dark:text-muted-dark">Cryptographic proof of file integrity and authenticity</p>
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
        
        const blueprintId = document.getElementById('blueprint_id').value;
        const fileInput = document.getElementById('file');
        
        if (!blueprintId || !fileInput.files.length) {
            alert('Please select a blueprint and upload a file');
            return;
        }

        const formData = new FormData();
        formData.append('blueprint_id', blueprintId);
        formData.append('file', fileInput.files[0]);

        // Show loading state
        setLoadingState(true);
        
        try {
            const response = await fetch('{{ route('blueprints.verify') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
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
            verifyBtnText.textContent = 'Verify Authenticity';
        }
    }

    function displayResults(data) {
        let html = '';
        
        if (data.is_authentic) {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-success"></span>
                            Authentic
                        </span>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-success/20 bg-success/5">
                        <p class="font-medium text-foreground dark:text-foreground-dark mb-2">✓ File is authentic and unmodified</p>
                        <p class="text-sm text-muted dark:text-muted-dark">
                            The file hash matches the blockchain-anchored hash, proving the file has not been tampered with.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Hash Match:</span>
                            <span class="text-sm text-success">✓ Verified</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Blockchain Anchored:</span>
                            <span class="text-sm text-success">✓ Yes</span>
                        </div>
                    </div>
                    
                    ${data.transaction_hash ? `
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Transaction:</span>
                                <a href="https://sepolia.basescan.org/tx/${data.transaction_hash}" 
                                   target="_blank" 
                                   class="text-xs text-primary hover:text-primary/80">
                                    View on BaseScan
                                </a>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        } else if (data.hash_matches && !data.is_anchored) {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-warning"></span>
                            Not Anchored
                        </span>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-warning/20 bg-warning/5">
                        <p class="font-medium text-foreground dark:text-foreground-dark mb-2">⚠ File matches but not blockchain-anchored</p>
                        <p class="text-sm text-muted dark:text-muted-dark">
                            The file hash matches the stored hash, but it has not been anchored on the blockchain for cryptographic proof.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Hash Match:</span>
                            <span class="text-sm text-success">✓ Yes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Blockchain Anchored:</span>
                            <span class="text-sm text-warning">✗ No</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            html = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-danger/10 text-danger text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-danger"></span>
                            Modified
                        </span>
                    </div>
                    
                    <div class="p-4 rounded-xl border border-danger/20 bg-danger/5">
                        <p class="font-medium text-foreground dark:text-foreground-dark mb-2">✗ File has been modified</p>
                        <p class="text-sm text-muted dark:text-muted-dark">
                            The file hash does not match the stored hash. This file may have been tampered with or is not the original file.
                        </p>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Hash Match:</span>
                            <span class="text-sm text-danger">✗ No</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Stored Hash:</span>
                            <button onclick="navigator.clipboard.writeText('${data.stored_hash}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                        </div>
                        <code class="block text-xs p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20 break-all">${data.stored_hash}</code>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Uploaded Hash:</span>
                            <button onclick="navigator.clipboard.writeText('${data.uploaded_hash}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                        </div>
                        <code class="block text-xs p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20 break-all">${data.uploaded_hash}</code>
                    </div>
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
