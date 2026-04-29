@extends('layouts.app')

@section('title', 'Integrations')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Integrations</h1>
        <p class="text-muted mt-2">Connect your favorite tools and services</p>
    </div>

    <!-- Available Integrations -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
        <!-- GitHub -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">GitHub</h3>
                    <p class="text-sm text-muted">Code repository integration</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Sync your projects with GitHub repositories, track commits, and manage issues.</p>
            <button onclick="connectIntegration('github')" class="w-full btn-brand text-sm">Connect</button>
        </div>

        <!-- Google Drive -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7.71 3.5L1.15 15l4.58 7.5h13.54l4.58-7.5L16.29 3.5H7.71zm6.42 0L18.58 15l-4.45 7.5H9.87L5.42 15l4.45-7.5h3.73z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">Google Drive</h3>
                    <p class="text-sm text-muted">Cloud storage integration</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Store project files, documents, and assets in Google Drive.</p>
            <button onclick="connectIntegration('google_drive')" class="w-full btn-brand text-sm">Connect</button>
        </div>

        <!-- Trello -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9.5 4.5v15h-5v-15h5zm10 0v15h-5v-15h5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">Trello</h3>
                    <p class="text-sm text-muted">Project management</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Sync tasks and boards with Trello for visual project tracking.</p>
            <button onclick="connectIntegration('trello')" class="w-full btn-brand text-sm">Connect</button>
        </div>

        <!-- Slack -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 17.688a2.528 2.528 0 0 1 2.522-2.523h2.52v2.523zm1.271 0a2.527 2.527 0 0 1 2.521-2.523 2.527 2.527 0 0 1 2.521 2.523v6.358A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.523v-6.358zM2.521 8.835a2.528 2.528 0 0 1-2.52-2.523A2.528 2.528 0 0 1 2.522 3.789h6.358a2.528 2.528 0 0 1 2.521 2.523 2.528 2.528 0 0 1-2.521 2.523H2.521zm1.271-1.271a2.527 2.527 0 0 1 2.521-2.523 2.527 2.527 0 0 1 2.521 2.523v6.358a2.528 2.528 0 0 1-2.521 2.523 2.528 2.528 0 0 1-2.521-2.523V7.564zm5.042 7.564a2.528 2.528 0 0 1 2.52-2.523 2.528 2.528 0 0 1 2.522 2.523v6.358a2.528 2.528 0 0 1-2.522 2.523 2.528 2.528 0 0 1-2.52-2.523v-6.358zm1.271-1.271a2.527 2.527 0 0 1 2.521-2.523 2.527 2.527 0 0 1 2.521 2.523v2.523h2.521a2.528 2.528 0 0 1 2.522 2.523 2.528 2.528 0 0 1-2.522 2.523h-2.521v2.523a2.528 2.528 0 0 1-2.521 2.523 2.528 2.528 0 0 1-2.521-2.523v-2.523h-2.521a2.528 2.528 0 0 1-2.522-2.523 2.528 2.528 0 0 1 2.522-2.523h2.521v-2.523z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">Slack</h3>
                    <p class="text-sm text-muted">Team communication</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Get project updates and notifications in Slack channels.</p>
            <button onclick="connectIntegration('slack')" class="w-full btn-brand text-sm">Connect</button>
        </div>

        <!-- Notion -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.459 4.208c.746.606 1.026.56 2.428.466l13.215-.793c.28 0 .047-.28-.046-.326L17.86 1.968c-.42-.326-.981-.7-2.055-.607L3.01 2.295c-.466.046-.56.28-.374.466zm.793 3.08v13.904c0 .747.373 1.027 1.214.98l13.354-.793c.841-.047.935-.56.935-1.167V6.354c0-.606-.233-.933-.748-.887l-14.043.84c-.56.047-.747.327-.747.933zm13.074 7.654c.093.42 0 .84-.42.887l-.98.093c-.093 0-.233-.046-.233-.186l-.7-8.727c-.093-.42.047-.84.42-.887l.886-.093c.094 0 .28.046.28.186l.793 8.727zM9.74 11.263c.093.42 0 .84-.42.887l-.98.093c-.093 0-.233-.046-.233-.186l-.7-8.727c-.093-.42.047-.84.42-.887l.886-.093c.094 0 .28.046.28.186l.793 8.727z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">Notion</h3>
                    <p class="text-sm text-muted">Documentation & notes</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Sync project documentation and notes with Notion pages.</p>
            <button onclick="connectIntegration('notion')" class="w-full btn-brand text-sm">Connect</button>
        </div>

        <!-- Figma -->
        <div class="surface-card interactive-lift p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15.852 8.981h-4.588V0h4.588c2.476 0 4.49 2.014 4.49 4.49s-2.014 4.491-4.49 4.491zM8.148 24h-4.588C1.084 24 0 22.916 0 21.44v-4.588C0 15.374 1.084 14.29 2.56 14.29h5.588v9.71zm0-11.732H2.56C1.084 12.268 0 11.184 0 9.708V4.49C0 2.014 1.084 0 2.56 0h5.588v12.268z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">Figma</h3>
                    <p class="text-sm text-muted">Design collaboration</p>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">Link Figma designs to projects and track design updates.</p>
            <button onclick="connectIntegration('figma')" class="w-full btn-brand text-sm">Connect</button>
        </div>
    </div>

    <!-- Webhooks Section -->
    <div class="surface-card interactive-lift p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Webhooks</h2>
            <button onclick="createWebhook()" class="btn-brand text-sm">Create Webhook</button>
        </div>
        <p class="text-sm text-muted mb-4">Configure webhooks to receive real-time notifications about project events.</p>
        <div class="space-y-3">
            <div class="p-4 bg-muted/10 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Project Created</p>
                        <code class="text-xs text-muted">Triggers when a new project is created</code>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
            <div class="p-4 bg-muted/10 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Task Completed</p>
                        <code class="text-xs text-muted">Triggers when a task is marked complete</code>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
            <div class="p-4 bg-muted/10 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Budget Alert</p>
                        <code class="text-xs text-muted">Triggers when budget exceeds threshold</code>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function connectIntegration(integration) {
    try {
        const response = await fetch(`/integrations/${integration}/connect`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        const result = await response.json();
        if (result.success) {
            alert(`${integration} connected successfully!`);
        } else {
            alert(result.message || 'Failed to connect integration');
        }
    } catch (error) {
        console.error('Error connecting integration:', error);
        alert('An error occurred while connecting the integration');
    }
}

function createWebhook() {
    alert('Webhook creation modal would open here');
}
</script>
@endsection
