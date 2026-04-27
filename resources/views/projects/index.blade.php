@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-foreground dark:text-foreground-dark">Projects</h1>
            <p class="text-muted mt-1">Manage all your projects</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-medium hover:bg-primary/90 transition focus:ring-4 focus:ring-primary/20 shadow-lg shadow-primary/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Project
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-background-dark rounded-2xl border border-muted/10 p-4 mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <form action="{{ route('projects.index') }}" method="GET">
                <div class="relative">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        name="search"
                        placeholder="Search projects..."
                        value="{{ request('search') }}"
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-muted/20 bg-background dark:bg-background-dark text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10"
                    />
                </div>
            </form>
        </div>
        <div class="flex gap-3">
            <select name="status" onchange="window.location.href = `{{ route('projects.index') }}?status=${this.value}`" class="px-4 py-3 rounded-xl border border-muted/20 bg-background dark:bg-background-dark text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10">
                <option value="">All Status</option>
                <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="on-hold" {{ request('status') == 'on-hold' ? 'selected' : '' }}>On Hold</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
    </div>

    @if ($projects->isNotEmpty())
        <!-- Desktop Table -->
        <div class="hidden md:block bg-white dark:bg-background-dark rounded-[2rem] border border-muted/10 overflow-hidden shadow-sm">
            <x-table-wrapper>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-muted/10 bg-muted/5 dark:bg-muted-dark/5">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Budget</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-muted/10">
                        @foreach ($projects as $project)
                            <tr class="hover:bg-muted/5 dark:hover:bg-muted-dark/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-foreground dark:text-foreground-dark">{{ $project->title }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted">{{ $project->category }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-muted/20 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full" style="width: {{ $project->progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-muted w-12 text-right">{{ $project->progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-foreground dark:text-foreground-dark">${{ number_format($project->estimated_budget, 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :status="$project->status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-muted">{{ $project->deadline?->format('M j, Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('projects.show', $project) }}" class="p-2 text-muted hover:text-foreground hover:bg-muted/10 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('projects.edit', $project) }}" class="p-2 text-muted hover:text-primary hover:bg-primary/10 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-wrapper>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-muted/10">
                {{ $projects->links() }}
            </div>
        </div>

        <!-- Mobile Card Fallback -->
        <div class="md:hidden space-y-4">
            @foreach ($projects as $project)
                <div class="bg-white dark:bg-background-dark rounded-[2rem] border border-muted/10 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $project->title }}</h3>
                            <p class="text-sm text-muted mt-1">{{ $project->category }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-status-badge :status="$project->status" />
                            <span class="text-sm font-medium text-muted">{{ $project->progress }}%</span>
                        </div>
                    </div>

                    <div class="h-2 bg-muted/20 rounded-full overflow-hidden mb-4">
                        <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $project->progress }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm text-muted">
                        <div>${{ number_format($project->estimated_budget, 2) }}</div>
                        <div class="text-right">{{ $project->deadline?->format('M j, Y') ?? 'No deadline' }}</div>
                    </div>

                    <div class="flex items-center gap-3 mt-5 pt-5 border-t border-muted/10">
                        <a href="{{ route('projects.show', $project) }}" class="flex-1 text-center py-2 text-sm font-medium text-muted hover:text-foreground transition">View</a>
                        <a href="{{ route('projects.edit', $project) }}" class="flex-1 text-center py-2 text-sm font-medium text-primary hover:text-primary/80 transition">Edit</a>
                    </div>
                </div>
            @endforeach

            <div class="mt-6">
                {{ $projects->links() }}
            </div>
        </div>
    @else
        <div class="mt-8">
            <x-empty-state
                icon="📁"
                title="No projects yet"
                message="Create your first project and build a stronger roadmap."
                action-text="Create Project"
                :action-href="route('projects.create')"
            />
        </div>
    @endif
</div>
@endsection