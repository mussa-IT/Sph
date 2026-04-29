@props([
    'icon' => '📭',
    'title' => 'Nothing here yet',
    'message' => 'Get started by creating your first item.',
    'actionText' => 'Create new',
    'actionHref' => '#',
    'showAction' => true,
    'size' => 'default',
    'variant' => 'default'
])

@php
$sizes = [
    'sm' => 'p-8',
    'default' => 'p-12',
    'lg' => 'p-16'
];

$iconSizes = [
    'sm' => 'h-16 w-16 text-3xl',
    'default' => 'h-20 w-20 text-4xl',
    'lg' => 'h-24 w-24 text-5xl'
];

$variants = [
    'default' => [
        'bg' => 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-900/50',
        'border' => 'border-gray-200 dark:border-gray-700',
        'iconBg' => 'bg-gray-100 dark:bg-gray-800',
        'buttonBg' => 'bg-indigo-600 hover:bg-indigo-700',
        'textColor' => 'text-gray-900 dark:text-white'
    ],
    'primary' => [
        'bg' => 'bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20',
        'border' => 'border-indigo-200 dark:border-indigo-700',
        'iconBg' => 'bg-indigo-100 dark:bg-indigo-800',
        'buttonBg' => 'bg-indigo-600 hover:bg-indigo-700',
        'textColor' => 'text-indigo-900 dark:text-indigo-100'
    ],
    'success' => [
        'bg' => 'bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20',
        'border' => 'border-emerald-200 dark:border-emerald-700',
        'iconBg' => 'bg-emerald-100 dark:bg-emerald-800',
        'buttonBg' => 'bg-emerald-600 hover:bg-emerald-700',
        'textColor' => 'text-emerald-900 dark:text-emerald-100'
    ]
];

$size = $sizes[$size] ?? $sizes['default'];
$iconSize = $iconSizes[$size] ?? $iconSizes['default'];
$variant = $variants[$variant] ?? $variants['default'];
@endphp

<div class="flex flex-col items-center justify-center rounded-2xl border {{ $variant['border'] }} {{ $variant['bg'] }} {{ $size }} text-center relative overflow-hidden group animate-fade-in-up">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-white/20 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-white/10 to-transparent rounded-full translate-y-12 -translate-x-12"></div>
    </div>
    
    <div class="relative z-10">
        <!-- Icon with enhanced styling -->
        <div class="mb-6 inline-flex {{ $iconSize }} items-center justify-center rounded-2xl {{ $variant['iconBg'] }} shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
            {{ $icon }}
        </div>

        <!-- Title and message -->
        <h3 class="mb-3 text-2xl font-bold {{ $variant['textColor'] }} transition-colors duration-300">
            {{ $title }}
        </h3>

        <p class="mb-8 max-w-md text-gray-600 dark:text-gray-400 text-lg leading-relaxed transition-colors duration-300">
            {{ $message }}
        </p>

        @if($showAction)
            <!-- Enhanced CTA button -->
            <a href="{{ $actionHref }}" class="group inline-flex items-center px-8 py-3.5 text-base font-semibold text-white {{ $variant['buttonBg'] }} rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ $actionText }}
            </a>
        @endif

        <!-- Additional content slot -->
        {{ $slot }}
        
        <!-- Subtle hint text -->
        @if($showAction)
            <p class="mt-6 text-sm text-gray-500 dark:text-gray-500">
                or press <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">Ctrl</kbd> + <kbd class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono">N</kbd> for keyboard shortcut
            </p>
        @endif
    </div>
</div>
