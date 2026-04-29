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
    {{-- Premium Logo Icon --}}
    @if($compact)
        <img src="{{ asset('images/logo-compact.svg') }}" 
             alt="Smart Project Hub" 
             class="{{ $badgeSize }} transition-transform duration-200 hover:scale-105">
    @else
        <img src="{{ asset('images/logo.svg') }}" 
             alt="Smart Project Hub" 
             class="h-10 w-auto transition-transform duration-200 hover:scale-105">
    @endif

    @unless($compact)
        <span class="flex flex-col leading-tight">
            <span class="{{ $nameSize }} font-bold tracking-tight text-foreground dark:text-foreground-dark">Smart Project Hub</span>
            @if($showSubtitle)
                <span class="{{ $subtitleSize }} text-muted dark:text-muted-dark">{{ $subtitle ?? 'AI Project Workspace' }}</span>
            @endif
        </span>
    @endunless
</span>
