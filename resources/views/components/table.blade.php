@props([
    'class' => '',
    'search' => false,
    'pagination' => false,
])

<div {{ $attributes->merge(['class' => 'w-full rounded-3xl border border-muted/10 bg-background-secondary shadow-sm overflow-hidden ' . $class]) }}>
    @if($search)
        <div class="px-4 py-4 sm:px-5 sm:py-5 border-b border-muted/10 bg-background dark:bg-background-dark">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    {{ $search }}
                </div>
            </div>
        </div>
    @endif

    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full w-full border-separate border-spacing-0">
            <thead class="bg-muted/5 dark:bg-muted-dark/5 text-left text-xs uppercase tracking-wider text-muted">
                {{ $header }}
            </thead>
            <tbody class="divide-y divide-muted/10 bg-background-secondary">
                {{ $rows }}
            </tbody>
        </table>
    </div>

    @if(isset($cards) && $cards->isNotEmpty())
        <div class="md:hidden px-4 py-4 sm:px-5 sm:py-5 space-y-4 bg-background dark:bg-background-dark">
            {{ $cards }}
        </div>
    @endif

    @if($pagination)
        <div class="px-4 py-4 sm:px-5 sm:py-5 border-t border-muted/10 bg-background dark:bg-background-dark">
            {{ $pagination }}
        </div>
    @endif
</div>
