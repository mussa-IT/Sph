@props([
    'title' => '',
    'subtitle' => '',
    'class' => '',
    'hover' => true
])

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-muted/20 bg-background-secondary p-4 sm:p-6 shadow-card transition-all duration-200 ease-out ' .
               ($hover ? 'hover:shadow-lg hover:-translate-y-1 hover:scale-[1.02] hover:border-muted/30 ' : '') .
               'dark:border-muted-dark/20 dark:bg-background-secondary-dark ' .
               ($hover ? 'dark:hover:border-muted-dark/30 ' : '') .
               'animate-fade-in-up ' .
               $class
]) }}>
    <div class="flex items-center justify-between gap-3">
        <div class="flex-1 min-w-0">
            @if($title)
                <h3 class="mb-2 truncate text-sm font-semibold text-muted dark:text-muted-dark">{{ $title }}</h3>
            @endif

            @if($slot->isNotEmpty())
                <div class="flex items-baseline gap-2 mb-1">
                    <div class="text-2xl sm:text-3xl font-bold text-foreground dark:text-foreground-dark truncate">
                        {{ $slot }}
                    </div>
                </div>
            @endif

            @if($subtitle)
                <p class="text-xs text-muted dark:text-muted-dark truncate">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($icon))
            <div class="flex-shrink-0">
                <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-primary/10 flex items-center justify-center text-xl sm:text-2xl dark:bg-primary/20">
                    {{ $icon }}
                </div>
            </div>
        @endif
    </div>
</div>
