@props(['type' => 'primary', 'class' => ''])

@php
    $typeClasses = [
        'primary' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400',
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        'planning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
    ];
    
    $badgeClass = $typeClasses[$type] ?? $typeClasses['primary'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }} {{ $class }}">
    {{ $slot }}
</span>
