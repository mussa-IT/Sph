@props(['icon' => '', 'title' => '', 'value' => '', 'change' => '', 'changeType' => 'positive', 'color' => 'primary'])

@php
    $colorClasses = [
        'primary' => 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400',
        'success' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        'warning' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
        'info' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
    ];
    
    $bgColorClass = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 {{ $bgColorClass }} rounded-lg flex items-center justify-center text-2xl">
                {{ $icon }}
            </div>
        </div>
        <div class="ml-4 flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
            @if($change)
                <p class="text-sm mt-1 {{ $changeType === 'positive' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $changeType === 'positive' ? '↑' : '↓' }} {{ $change }}
                </p>
            @endif
        </div>
    </div>
</div>
