@extends('layouts.app')

@section('title', 'Blueprints')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Blueprints</h1>
        <p class="text-muted mt-2">Upload and verify your project blueprints with blockchain anchoring</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Upload Section -->
        <div class="lg:col-span-1">
            <div class="surface-card interactive-lift p-6">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Upload Blueprint</h2>
                <p class="text-sm text-muted dark:text-muted-dark mb-6">
                    Upload PDF or image files. The system will calculate the SHA256 hash and optionally anchor it on Base Sepolia.
                </p>

                <form id="blueprint-upload-form" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                            Title
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            placeholder="Blueprint title"
                            class="input-brand w-full"
                            required
                        >
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                            Description (optional)
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Describe this blueprint"
                            class="input-brand w-full"
                        ></textarea>
                    </div>

                    <div>
                        <label for="project_id" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                            Associated Project (optional)
                        </label>
                        <select id="project_id" name="project_id" class="input-brand w-full">
                            <option value="">No project</option>
                            @foreach(auth()->user()->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-2">
                            File (PDF, PNG, JPG)
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
                            Max file size: 10MB
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="anchor_onchain"
                            name="anchor_onchain"
                            class="w-4 h-4 rounded border-muted"
                        >
                        <label for="anchor_onchain" class="text-sm text-foreground dark:text-foreground-dark">
                            Anchor hash on Base Sepolia blockchain
                        </label>
                    </div>

                    <button type="submit" class="btn-brand w-full" id="upload-btn">
                        <span id="upload-btn-text">Upload Blueprint</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Blueprints List -->
        <div class="lg:col-span-2">
            <div class="surface-card interactive-lift p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Your Blueprints</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted dark:text-muted-dark">
                            {{ $blueprints->count() }} total
                        </span>
                    </div>
                </div>

                @if($blueprints->count() > 0)
                    <div class="space-y-4">
                        @foreach($blueprints as $blueprint)
                            <div class="p-4 rounded-xl border border-muted/10 dark:border-muted-dark/10">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 class="font-semibold text-foreground dark:text-foreground-dark">
                                                {{ $blueprint->title }}
                                            </h3>
                                            @if($blueprint->is_anchored)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                                    <span class="w-2 h-2 rounded-full bg-success"></span>
                                                    Anchored
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($blueprint->description)
                                            <p class="text-sm text-muted dark:text-muted-dark mb-3">
                                                {{ $blueprint->description }}
                                            </p>
                                        @endif

                                        <div class="flex items-center gap-4 text-xs text-muted dark:text-muted-dark mb-3">
                                            <span>{{ $blueprint->file_name }}</span>
                                            <span>{{ $this->formatFileSize($blueprint->file_size) }}</span>
                                            <span>Uploaded {{ $blueprint->created_at->format('M j, Y') }}</span>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-muted dark:text-muted-dark">File Hash (SHA256):</span>
                                                <button onclick="navigator.clipboard.writeText('{{ $blueprint->file_hash }}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                                            </div>
                                            <code class="block text-xs p-2 rounded-lg bg-muted/10 dark:bg-muted-dark/20 break-all">
                                                {{ $blueprint->file_hash }}
                                            </code>
                                        </div>

                                        @if($blueprint->blockchain_hash)
                                            <div class="mt-3 space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-muted dark:text-muted-dark">Blockchain Hash:</span>
                                                    <button onclick="navigator.clipboard.writeText('{{ $blueprint->blockchain_hash }}')" class="text-xs text-primary hover:text-primary/80">Copy</button>
                                                </div>
                                                <code class="block text-xs p-2 rounded-lg bg-success/10 dark:bg-success-dark/20 break-all">
                                                    {{ $blueprint->blockchain_hash }}
                                                </code>
                                                @if($blueprint->transaction_hash)
                                                    <a href="https://sepolia.basescan.org/tx/{{ $blueprint->transaction_hash }}" 
                                                       target="_blank" 
                                                       class="text-xs text-primary hover:text-primary/80 mt-1 inline-block">
                                                        View Transaction
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4 flex flex-col gap-2">
                                        <a href="{{ Storage::url($blueprint->file_path) }}" 
                                           target="_blank" 
                                           class="btn-brand-muted text-sm">
                                            View
                                        </a>
                                        @if(!$blueprint->is_anchored)
                                            <button onclick="anchorOnchain({{ $blueprint->id }})" class="btn-brand text-sm">
                                                Anchor Onchain
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-4xl mb-4">📄</div>
                        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Blueprints Yet</h3>
                        <p class="text-sm text-muted dark:text-muted-dark">Upload your first blueprint to get started</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('blueprint-upload-form');
    const uploadBtn = document.getElementById('upload-btn');
    const uploadBtnText = document.getElementById('upload-btn-text');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Show loading state
        setLoadingState(true);
        
        try {
            const response = await fetch('{{ route('blueprints.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Failed to upload blueprint');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Failed to upload blueprint. Please try again.');
        } finally {
            setLoadingState(false);
        }
    });

    function setLoadingState(isLoading) {
        uploadBtn.disabled = isLoading;
        if (isLoading) {
            uploadBtnText.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block"></span> Uploading...';
        } else {
            uploadBtnText.textContent = 'Upload Blueprint';
        }
    }
});

async function anchorOnchain(blueprintId) {
    if (!confirm('Anchor this blueprint on Base Sepolia blockchain? This will require a wallet connection and gas fees.')) {
        return;
    }

    try {
        const response = await fetch(`/blueprints/${blueprintId}/anchor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const result = await response.json();
        
        if (result.success) {
            alert('Blueprint anchored on blockchain!');
            window.location.reload();
        } else {
            alert(result.message || 'Failed to anchor blueprint');
        }
    } catch (error) {
        console.error('Anchor error:', error);
        alert('Failed to anchor blueprint. Please try again.');
    }
}
</script>
@endsection
