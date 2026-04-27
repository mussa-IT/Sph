@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-foreground dark:text-foreground-dark">Create New Project</h1>
        <p class="text-muted mt-1">Fill in the details below to create your project</p>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-3xl border border-muted/10 shadow-xl shadow-primary/5 p-6 md:p-8">
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Project Title -->
            <x-form-input
                name="title"
                label="Project Title"
                placeholder="Enter project name"
                required
                autofocus
            />

            <!-- Category Dropdown -->
            <div class="space-y-2">
                <label for="category" class="block text-sm font-medium text-foreground dark:text-foreground-dark">Category</label>
                <select
                    id="category"
                    name="category"
                    required
                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                >
                    <option value="" selected disabled>Select category</option>
                    <option value="web" {{ old('category') == 'web' ? 'selected' : '' }}>Website / Web App</option>
                    <option value="mobile" {{ old('category') == 'mobile' ? 'selected' : '' }}>Mobile Application</option>
                    <option value="desktop" {{ old('category') == 'desktop' ? 'selected' : '' }}>Desktop Software</option>
                    <option value="design" {{ old('category') == 'design' ? 'selected' : '' }}>UI/UX Design</option>
                    <option value="marketing" {{ old('category') == 'marketing' ? 'selected' : '' }}>Marketing Campaign</option>
                    <option value="research" {{ old('category') == 'research' ? 'selected' : '' }}>Research & Analysis</option>
                    <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <!-- Description Textarea -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-medium text-foreground dark:text-foreground-dark">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Describe what this project is about..."
                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition duration-200 placeholder:text-muted focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:placeholder:text-muted-dark resize-none"
                >{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Status Select -->
                <div class="space-y-2">
                    <label for="status" class="block text-sm font-medium text-foreground dark:text-foreground-dark">Status</label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                    >
                        <option value="planning" {{ old('status', 'planning') == 'planning' ? 'selected' : '' }}>📋 Planning</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🚀 Active</option>
                        <option value="on-hold" {{ old('status') == 'on-hold' ? 'selected' : '' }}>⏸️ On Hold</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                    </select>
                </div>

                <!-- Budget Input -->
                <x-form-input
                    type="number"
                    name="estimated_budget"
                    label="Estimated Budget"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                />
            </div>

            <!-- Deadline Picker -->
            <x-form-input
                type="date"
                name="deadline"
                label="Deadline"
            />

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-2xl p-4 mt-4">
                    <div class="flex items-center gap-3 text-red-700 dark:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">Please fix the following errors:</span>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-600 dark:text-red-300 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-muted/10">
                <a href="{{ route('projects.index') }}" class="px-5 py-2.5 font-medium text-muted hover:text-foreground transition dark:hover:text-foreground-dark">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-medium hover:bg-primary/90 transition focus:ring-4 focus:ring-primary/20 shadow-lg shadow-primary/20">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>
@endsection