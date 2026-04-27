@props([
    'compact' => false,
    'badgeSize' => 'h-10 w-10',
    'badgeTextSize' => 'text-sm',
    'nameSize' => 'text-base',
    'subtitle' => null,
    'subtitleSize' => 'text-xs',
    'showSubtitle' => true,
])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="inline-flex {{ $badgeSize }} items-center justify-center rounded-xl bg-gradient-to-br from-primary to-secondary {{ $badgeTextSize }} font-bold text-primary-foreground shadow-lg shadow-primary/25">
        SPH
    </span>

    @unless($compact)
        <span class="flex flex-col leading-tight">
            <span class="{{ $nameSize }} font-bold tracking-tight text-foreground dark:text-foreground-dark">Smart Project Hub</span>
            @if($showSubtitle)
                <span class="{{ $subtitleSize }} text-muted dark:text-muted-dark">{{ $subtitle ?? 'AI Project Workspace' }}</span>
            @endif
        </span>
    @endunless
</span>
