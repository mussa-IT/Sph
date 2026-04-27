@props([
    'icon' => '📭',
    'title' => 'Nothing here yet',
    'message' => 'Get started by creating your first item.',
    'actionText' => 'Create new',
    'actionHref' => '#',
    'showAction' => true
])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-muted/30 bg-background-secondary/50 p-12 text-center dark:border-muted-dark/30 dark:bg-background-secondary-dark/50 animate-fade-in-up">
    <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-muted/10 text-4xl dark:bg-muted-dark/10">
        {{ $icon }}
    </div>

    <h3 class="mb-2 text-xl font-semibold text-foreground dark:text-foreground-dark">
        {{ $title }}
    </h3>

    <p class="mb-8 max-w-sm text-sm text-muted dark:text-muted-dark">
        {{ $message }}
    </p>

    @if($showAction)
        <a href="{{ $actionHref }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 ease-out hover:bg-primary/90 hover:scale-[1.03] active:scale-[0.97] shadow-soft">
            <span class="mr-2 text-base">➕</span>
            {{ $actionText }}
        </a>
    @endif

    {{ $slot }}
</div>
