@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">API Documentation</h1>
        <p class="text-muted mt-2">Build powerful integrations with our REST API</p>
    </div>

    <!-- API Key Section -->
    <div class="surface-card interactive-lift p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Your API Keys</h2>
            <button onclick="generateApiKey()" class="btn-brand text-sm">Generate New Key</button>
        </div>
        <div class="space-y-3">
            @if(auth()->user()->api_token)
                <div class="flex items-center justify-between p-3 bg-muted/10 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Production Key</p>
                        <code class="text-xs text-muted">{{ auth()->user()->api_token }}</code>
                    </div>
                    <button onclick="copyToClipboard('{{ auth()->user()->api_token }}')" class="text-sm text-primary hover:underline">Copy</button>
                </div>
            @else
                <p class="text-sm text-muted">No API key generated yet. Click "Generate New Key" to get started.</p>
            @endif
        </div>
    </div>

    <!-- Quick Start -->
    <div class="surface-card interactive-lift p-6 mb-8">
        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Quick Start</h2>
        <div class="space-y-4">
            <div class="p-4 bg-muted/10 rounded-lg">
                <p class="text-sm font-medium mb-2">1. Authenticate</p>
                <pre class="text-xs bg-black/20 p-3 rounded overflow-x-auto"><code>curl -X POST {{ route('login') }} \
  -H "Content-Type: application/json" \
  -d '{"email": "your@email.com", "password": "password"}'</code></pre>
            </div>
            <div class="p-4 bg-muted/10 rounded-lg">
                <p class="text-sm font-medium mb-2">2. Use your token</p>
                <pre class="text-xs bg-black/20 p-3 rounded overflow-x-auto"><code>curl -X GET {{ url('/api/v1/projects') }} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"</code></pre>
            </div>
        </div>
    </div>

    <!-- Endpoints -->
    <div class="space-y-6">
        <!-- Projects -->
        <div class="surface-card interactive-lift">
            <div class="p-6 border-b border-muted/10">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Projects</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/projects</code>
                    </div>
                    <p class="text-sm text-muted">List all projects for authenticated user</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-blue-500/20 text-blue-600 text-xs font-medium rounded">POST</span>
                        <code class="text-sm">/api/v1/projects</code>
                    </div>
                    <p class="text-sm text-muted">Create a new project</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/projects/{id}</code>
                    </div>
                    <p class="text-sm text-muted">Get a specific project</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-600 text-xs font-medium rounded">PUT</span>
                        <code class="text-sm">/api/v1/projects/{id}</code>
                    </div>
                    <p class="text-sm text-muted">Update a project</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-red-500/20 text-red-600 text-xs font-medium rounded">DELETE</span>
                        <code class="text-sm">/api/v1/projects/{id}</code>
                    </div>
                    <p class="text-sm text-muted">Delete a project</p>
                </div>
            </div>
        </div>

        <!-- Teams -->
        <div class="surface-card interactive-lift">
            <div class="p-6 border-b border-muted/10">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Teams</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/teams</code>
                    </div>
                    <p class="text-sm text-muted">List all teams</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-blue-500/20 text-blue-600 text-xs font-medium rounded">POST</span>
                        <code class="text-sm">/api/v1/teams</code>
                    </div>
                    <p class="text-sm text-muted">Create a new team</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/teams/{id}/members</code>
                    </div>
                    <p class="text-sm text-muted">List team members</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-blue-500/20 text-blue-600 text-xs font-medium rounded">POST</span>
                        <code class="text-sm">/api/v1/teams/{id}/invite</code>
                    </div>
                    <p class="text-sm text-muted">Invite a member to team</p>
                </div>
            </div>
        </div>

        <!-- User -->
        <div class="surface-card interactive-lift">
            <div class="p-6 border-b border-muted/10">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">User</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/user/profile</code>
                    </div>
                    <p class="text-sm text-muted">Get user profile</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-600 text-xs font-medium rounded">PUT</span>
                        <code class="text-sm">/api/v1/user/profile</code>
                    </div>
                    <p class="text-sm text-muted">Update user profile</p>
                </div>
                <div class="p-4 bg-muted/10 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-1 bg-green-500/20 text-green-600 text-xs font-medium rounded">GET</span>
                        <code class="text-sm">/api/v1/user/stats</code>
                    </div>
                    <p class="text-sm text-muted">Get user statistics</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
}

async function generateApiKey() {
    try {
        const response = await fetch('/api/generate-key', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error generating API key:', error);
    }
}
</script>
@endsection
