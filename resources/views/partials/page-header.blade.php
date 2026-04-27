@props([
    'title' => '',
    'heading' => '',
    'actions' => null
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm uppercase tracking-[.32em] text-secondary">{{ $heading }}</p>
        <h1 class="mt-2 text-3xl font-semibold text-foreground dark:text-foreground-dark">{{ $title }}</h1>
    </div>
    @if($actions)
        <div class="flex flex-wrap gap-2 sm:gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
