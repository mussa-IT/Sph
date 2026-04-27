@props([
    'status' => '',
    'label' => null,
    'class' => '',
])

@php
    $statusKey = trim(strtolower(str_replace(['_', '-'], ' ', $status)));
    $labelText = $label ?? \Illuminate\Support\Str::title($statusKey);
    $colorMap = [
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'in progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'done' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'finished' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
        'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
        'canceled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
        'planning' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'active' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'on hold' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    ];
    $badgeClass = $colorMap[$statusKey] ?? 'bg-muted/10 text-muted dark:bg-muted-dark/20 dark:text-muted-dark';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold tracking-wide {$badgeClass} {$class}"]) }}>
    {{ $labelText }}
</span>
