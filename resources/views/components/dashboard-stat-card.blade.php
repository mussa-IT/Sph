@props([
    'value' => '0',
    'label' => 'Stat',
    'icon' => null,
    'iconColor' => 'blue',
    'change' => null,
    'changeType' => 'positive',
    'progress' => null,
    'progressLabel' => null
])

@php
    $iconColors = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'red' => 'bg-red-100 text-red-600'
    ];
    
    $iconClass = $iconColors[$iconColor] ?? $iconColors['blue'];
@endphp

<div class="stats-card animate-fade-in-up">
    @if($icon)
        <div class="stats-card-icon {{ $iconClass }}">
            {!! $icon !!}
        </div>
    @endif
    
    <div class="stats-card-value">{{ $value }}</div>
    <div class="stats-card-label">{{ $label }}</div>
    
    @if($change !== null)
        <div class="stats-card-change {{ $changeType === 'positive' ? 'positive' : 'negative' }}">
            @if($changeType === 'positive')
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            @else
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            @endif
            {{ $change }}
        </div>
    @endif
    
    @if($progress !== null)
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                <span>{{ $progressLabel ?? 'Progress' }}</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    @endif
</div>
