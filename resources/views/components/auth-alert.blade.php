@props(['type' => 'error'])

@php
$styles = [
    'error'   => 'border-warning/20 bg-warning/10 text-warning-foreground dark:border-warning/20 dark:bg-warning/10 dark:text-warning-foreground',
    'success' => 'border-success/20 bg-success/10 text-success-foreground dark:border-success/20 dark:bg-success/10 dark:text-success-foreground',
];
$titles = [
    'error'   => 'Something went wrong.',
    'success' => '',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border p-5 text-sm ' . $styles[$type]]) }}>
    @if($type === 'error')
        <p class="font-semibold">{{ $titles[$type] }}</p>
        <ul class="mt-3 space-y-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @else
        {{ $slot }}
    @endif
</div>
