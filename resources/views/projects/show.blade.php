@extends('layouts.app')

@section('content')
@php
    $totalTasks = (int) ($project->tasks_count ?? $project->tasks->count());
    $completedTasks = (int) ($project->completed_tasks_count ?? $project->tasks->where('status', 'done')->count());
    $formattedBudget = $project->estimated_budget ? '$' . number_format($project->estimated_budget, 2) : null;
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'high' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];
@endphp
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-8 space-y-6">
        <div class="rounded-[2rem] bg-white dark:bg-background-dark border border-muted/10 p-6 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full bg-muted/10 px-4 py-2 text-sm font-medium text-muted dark:bg-muted-dark/20 dark:text-muted-dark">
                        {{ ucfirst($project->category) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground dark:text-foreground-dark">{{ $project->title }}</h1>
                        <p class="mt-2 text-sm text-muted">{{ $totalTasks }} tasks - {{ $formattedBudget ?? 'Budget not set' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$project->status" />
                    @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center gap-2 rounded-xl border border-muted/20 bg-background px-4 py-2 text-sm font-medium text-foreground dark:bg-background-dark dark:text-foreground-dark hover:bg-muted/10 transition">
                        Edit Project
                    </a>
                    @endcan
                    
                    @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Are you sure you want to delete this project?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Project
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                    <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Progress</p>
                    <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $project->progress }}%</p>
                </div>
                <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                    <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Deadline</p>
                    <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">
    @if($project->deadline)
        {{ $project->deadline->format('M j, Y') }}
    @else
        No deadline
    @endif
</p>
                </div>
                <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                    <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Budget</p>
                    <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $formattedBudget ?? 'Not set' }}</p>
                </div>
                <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                    <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Tasks</p>
                    <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $totalTasks }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white dark:bg-background-dark border border-muted/10 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Add New Task</h2>
                <p class="text-sm text-muted mt-1">Keep project progress moving by adding tasks and due dates.</p>
                <form action="{{ url('/projects/'.$project->id.'/tasks') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <label class="sr-only" for="title">Task title</label>
                            <input
                                id="title"
                                name="title"
                                type="text"
                                placeholder="Task title..."
                                required
                                class="w-full rounded-2xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10"
                            />
                        </div>
                        <div>
                            <label class="sr-only" for="priority">Priority</label>
                            <select id="priority" name="priority" class="w-full rounded-2xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                        </div>
                        <div>
                            <label class="sr-only" for="due_date">Due date</label>
                            <input id="due_date" type="date" name="due_date" class="w-full rounded-2xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                            Add Task
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-[2rem] bg-white dark:bg-background-dark border border-muted/10 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Task Overview</h2>
                        <p class="text-sm text-muted mt-1">Track work, mark completed tasks, and adjust priorities.</p>
                    </div>
                    <span class="text-xs uppercase tracking-[.35em] text-muted dark:text-muted-dark">{{ ucfirst($project->status) }}</span>
                </div>

                @if ($project->tasks->isNotEmpty())
                    <div class="mt-6 overflow-x-auto">
                        <x-table-wrapper>
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-muted/10 bg-muted/5 dark:bg-muted-dark/5">
                                        <th class="px-5 py-4 text-left w-10"></th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Task</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Priority</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Status</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Due Date</th>
                                        <th class="px-5 py-4 text-right text-xs font-semibold text-muted uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-muted/10">
                                    @foreach ($project->tasks as $task)
                                        <tr class="hover:bg-muted/5 dark:hover:bg-muted-dark/5 transition-colors">
                                            <td class="px-5 py-4">
                                                <form action="{{ route('tasks.status.update', $task) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input
                                                        type="checkbox"
                                                        onchange="this.form.submit()"
                                                        {{ $task->status === 'done' ? 'checked' : '' }}
                                                        class="w-5 h-5 rounded border-2 border-muted/30 text-primary focus:ring-primary focus:ring-offset-0 cursor-pointer"
                                                    />
                                                    <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'pending' : 'done' }}" />
                                                </form>
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="{{ $task->status === 'done' ? 'line-through text-muted' : 'text-foreground dark:text-foreground-dark' }}">{{ $task->title }}</span>
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $priorityColors[$task->priority] }}">{{ ucfirst($task->priority) }}</span>
                                            </td>
                                            <td class="px-5 py-4">
                                                <x-status-badge :status="$task->status" />
                                            </td>
                                            <td class="px-5 py-4 text-sm text-muted">{{ $task->due_date?->format('M j, Y') ?? '-' }}</td>
                                            <td class="px-5 py-4 text-right">
                                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-table-wrapper>
                    </div>
                @else
                    <div class="mt-6 rounded-[2rem] border border-dashed border-muted/20 bg-background-secondary/60 p-8 text-center dark:border-muted-dark/20 dark:bg-background-secondary-dark/60">
                        <p class="text-sm text-muted">No tasks yet. Use the form to add the first item to this project.</p>
                    </div>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-[2rem] bg-white dark:bg-background-dark border border-muted/10 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Project Summary</h2>
                <p class="text-sm text-muted mt-1">Quick facts and current status for this project.</p>

                <div class="mt-6 grid gap-4">
                    <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                        <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Current Progress</p>
                        <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $project->progress }}%</p>
                    </div>
                    <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                        <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Deadline</p>
                        <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $project->deadline ? $project->deadline->format('M j, Y') : 'Not scheduled' }}</p>
                    </div>
                    <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                        <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Estimated Budget</p>
                        <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $formattedBudget ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-3xl bg-muted/5 p-4 dark:bg-muted-dark/5">
                        <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Task Status</p>
                        <p class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">{{ $completedTasks }} completed</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] bg-white dark:bg-background-dark border border-muted/10 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Next Step</h3>
                <p class="text-sm text-muted mt-2">Keep your project moving by regularly reviewing upcoming tasks and updating deadlines.</p>
                <div class="mt-5 rounded-3xl bg-muted/10 p-4 dark:bg-muted-dark/10">
                    <p class="text-sm text-foreground dark:text-foreground-dark">Focus on the highest impact tasks first, then refine scope weekly.</p>
                </div>
            </div>
        </aside>
    </div>
</div>
<!-- Create New Project Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Project
            </a>
        </div>
    </div>
@endsection
