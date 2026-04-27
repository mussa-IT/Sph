@props([
    'icon' => '📊',
    'title' => 'Statistic',
    'value' => '0',
    'change' => null,
    'changeType' => 'neutral',
    'color' => 'primary',
])

@php
$themes = [
    'primary'   => ['bg' => 'bg-primary/10', 'bgDark' => 'dark:bg-primary/20', 'text' => 'text-primary', 'border' => 'border-primary/30', 'gradient' => 'from-primary to-secondary'],
    'blue'      => ['bg' => 'bg-blue-500/10', 'bgDark' => 'dark:bg-blue-500/20', 'text' => 'text-blue-600', 'border' => 'border-blue-500/30', 'gradient' => 'from-blue-500 to-cyan-500'],
    'emerald'   => ['bg' => 'bg-emerald-500/10', 'bgDark' => 'dark:bg-emerald-500/20', 'text' => 'text-emerald-600', 'border' => 'border-emerald-500/30', 'gradient' => 'from-emerald-500 to-green-500'],
    'amber'     => ['bg' => 'bg-amber-500/10', 'bgDark' => 'dark:bg-amber-500/20', 'text' => 'text-amber-600', 'border' => 'border-amber-500/30', 'gradient' => 'from-amber-500 to-orange-500'],
    'violet'    => ['bg' => 'bg-violet-500/10', 'bgDark' => 'dark:bg-violet-500/20', 'text' => 'text-violet-600', 'border' => 'border-violet-500/30', 'gradient' => 'from-violet-500 to-purple-500'],
    'rose'      => ['bg' => 'bg-rose-500/10', 'bgDark' => 'dark:bg-rose-500/20', 'text' => 'text-rose-600', 'border' => 'border-rose-500/30', 'gradient' => 'from-rose-500 to-pink-500'],
];
$theme = $themes[$color] ?? $themes['primary'];
$changeColors = [
    'positive'  => 'text-emerald-600 dark:text-emerald-400',
    'negative'  => 'text-rose-600 dark:text-rose-400',
    'neutral'   => 'text-muted dark:text-muted-dark',
];
$changeIcon = [
    'positive'  => '↑',
    'negative'  => '↓',
    'neutral'   => '•',
];
@endphp

<div class="group relative rounded-2xl border border-muted/10 bg-background p-6 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-muted-dark/10 dark:bg-background-dark">
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-muted dark:text-muted-dark">{{ $title }}</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-foreground dark:text-foreground-dark">{{ $value }}</p>
            @if($change)
                <p class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold {{ $changeColors[$changeType] }}">
                    <span>{{ $changeIcon[$changeType] }}</span>
                    {{ $change }}
                </p>
            @endif
        </div>
        <div class="ml-4 inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $theme['bg'] }} {{ $theme['bgDark'] }} text-2xl transition-transform duration-300 group-hover:scale-110">
            {{ $icon }}
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-1 rounded-b-2xl bg-gradient-to-r {{ $theme['gradient'] }} opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
</div>
