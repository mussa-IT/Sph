@extends('layouts.app')

@section('title', 'Projects')

@php
    $pageTitle = 'Projects';
    $pageHeading = 'Projects';
@endphp

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Projects</h1>
        <p class="text-muted mt-2">Manage your projects and publish them onchain for verifiable ownership</p>
    </div>

    @if(auth()->user()->projects->count() > 0)
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach(auth()->user()->projects as $project)
                <div class="surface-card interactive-lift">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $project->title }}</h3>
                                <p class="text-sm text-muted dark:text-muted-dark mt-1">{{ $project->category }}</p>
                            </div>
                            @if($project->transaction_hash)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-medium">
                                    <span class="w-2 h-2 rounded-full bg-success"></span>
                                    Onchain
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-muted dark:text-muted-dark mb-4 line-clamp-2">
                            {{ $project->description ?? 'No description' }}
                        </p>

                        <div class="flex items-center gap-4 text-xs text-muted dark:text-muted-dark mb-4">
                            <span>{{ $project->tasks->count() ?? 0 }} tasks</span>
                            <span>{{ $project->status }}</span>
                            <span>{{ $project->progress }}% complete</span>
                        </div>

                        @if($project->transaction_hash)
                            <div class="p-3 rounded-lg bg-success/5 border border-success/20 mb-4">
                                <p class="text-xs text-success font-medium">Verified on Base Sepolia</p>
                                <a href="https://sepolia.basescan.org/tx/{{ $project->transaction_hash }}" 
                                   target="_blank" 
                                   class="text-xs text-primary hover:text-primary/80">
                                    View Transaction
                                </a>
                            </div>
                        @else
                            <div id="project-publisher-{{ $project->id }}"></div>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="btn-brand-muted text-sm flex-1 text-center">
                                View
                            </a>
                            <a href="{{ route('projects.edit', $project) }}" class="btn-brand-muted text-sm flex-1 text-center">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state
            icon="📁"
            title="No projects yet"
            message="Start organizing your work by creating your first project. You can add tasks, budgets, and team members to each project."
            actionText="Create project"
            actionHref="{{ route('projects.create') }}"
        />
    @endif
</div>

@stack('scripts')
<script>
// Initialize ProjectPublisher components for unpublished projects
document.addEventListener('DOMContentLoaded', function() {
    @foreach(auth()->user()->projects as $project)
        @if(!$project->transaction_hash)
            const container{{ $project->id }} = document.getElementById('project-publisher-{{ $project->id }}');
            if (container{{ $project->id }} && window.React && window.ReactDOM) {
                const ProjectPublisher = window.ProjectPublisher;
                if (ProjectPublisher) {
                    const root = ReactDOM.createRoot(container{{ $project->id }});
                    root.render(React.createElement(ProjectPublisher, {
                        project: @json($project),
                        onSuccess: function(data) {
                            window.location.reload();
                        }
                    }));
                }
            }
        @endif
    @endforeach
});
</script>
@endsection
