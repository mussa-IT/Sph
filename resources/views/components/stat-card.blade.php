@props([
    'icon' => '📊',
    'title' => 'Statistic',
    'value' => '0',
    'change' => null,
    'changeType' => 'neutral',
    'color' => 'primary',
    'subtitle' => null,
    'progress' => null,
    'trend' => null,
    'size' => 'default',
])

@php
// Enhanced color themes with modern gradients
$themes = [
    'primary'   => [
        'bg' => 'bg-gradient-to-br from-indigo-500/10 to-purple-500/10', 
        'bgDark' => 'dark:from-indigo-500/20 dark:to-purple-500/20', 
        'text' => 'text-indigo-600', 
        'border' => 'border-indigo-200/50', 
        'gradient' => 'from-indigo-500 to-purple-600',
        'iconBg' => 'bg-gradient-to-br from-indigo-500 to-purple-600',
        'lightBg' => 'bg-indigo-50',
        'darkLightBg' => 'dark:bg-indigo-900/20'
    ],
    'success'   => [
        'bg' => 'bg-gradient-to-br from-emerald-500/10 to-green-500/10', 
        'bgDark' => 'dark:from-emerald-500/20 dark:to-green-500/20', 
        'text' => 'text-emerald-600', 
        'border' => 'border-emerald-200/50', 
        'gradient' => 'from-emerald-500 to-green-600',
        'iconBg' => 'bg-gradient-to-br from-emerald-500 to-green-600',
        'lightBg' => 'bg-emerald-50',
        'darkLightBg' => 'dark:bg-emerald-900/20'
    ],
    'warning'   => [
        'bg' => 'bg-gradient-to-br from-amber-500/10 to-orange-500/10', 
        'bgDark' => 'dark:from-amber-500/20 dark:to-orange-500/20', 
        'text' => 'text-amber-600', 
        'border' => 'border-amber-200/50', 
        'gradient' => 'from-amber-500 to-orange-600',
        'iconBg' => 'bg-gradient-to-br from-amber-500 to-orange-600',
        'lightBg' => 'bg-amber-50',
        'darkLightBg' => 'dark:bg-amber-900/20'
    ],
    'danger'    => [
        'bg' => 'bg-gradient-to-br from-red-500/10 to-rose-500/10', 
        'bgDark' => 'dark:from-red-500/20 dark:to-rose-500/20', 
        'text' => 'text-red-600', 
        'border' => 'border-red-200/50', 
        'gradient' => 'from-red-500 to-rose-600',
        'iconBg' => 'bg-gradient-to-br from-red-500 to-rose-600',
        'lightBg' => 'bg-red-50',
        'darkLightBg' => 'dark:bg-red-900/20'
    ],
    'info'      => [
        'bg' => 'bg-gradient-to-br from-blue-500/10 to-cyan-500/10', 
        'bgDark' => 'dark:from-blue-500/20 dark:to-cyan-500/20', 
        'text' => 'text-blue-600', 
        'border' => 'border-blue-200/50', 
        'gradient' => 'from-blue-500 to-cyan-600',
        'iconBg' => 'bg-gradient-to-br from-blue-500 to-cyan-600',
        'lightBg' => 'bg-blue-50',
        'darkLightBg' => 'dark:bg-blue-900/20'
    ],
    'purple'    => [
        'bg' => 'bg-gradient-to-br from-purple-500/10 to-violet-500/10', 
        'bgDark' => 'dark:from-purple-500/20 dark:to-violet-500/20', 
        'text' => 'text-purple-600', 
        'border' => 'border-purple-200/50', 
        'gradient' => 'from-purple-500 to-violet-600',
        'iconBg' => 'bg-gradient-to-br from-purple-500 to-violet-600',
        'lightBg' => 'bg-purple-50',
        'darkLightBg' => 'dark:bg-purple-900/20'
    ],
];

$theme = $themes[$color] ?? $themes['primary'];

// Enhanced change indicators
$changeColors = [
    'positive'  => 'text-emerald-600 dark:text-emerald-400',
    'negative'  => 'text-red-600 dark:text-red-400',
    'neutral'   => 'text-gray-500 dark:text-gray-400',
    'increase'  => 'text-emerald-600 dark:text-emerald-400',
    'decrease'  => 'text-red-600 dark:text-red-400',
];

$changeIcon = [
    'positive'  => '↑',
    'negative'  => '↓',
    'neutral'   => '→',
    'increase'  => '↑',
    'decrease'  => '↓',
];

// Size variations
$sizes = [
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8',
];

$iconSizes = [
    'sm' => 'w-8 h-8 text-lg',
    'default' => 'w-12 h-12 text-xl',
    'lg' => 'w-16 h-16 text-2xl',
];

$valueSizes = [
    'sm' => 'text-2xl',
    'default' => 'text-3xl',
    'lg' => 'text-4xl',
];

$cardSize = $sizes[$size] ?? $sizes['default'];
$iconSize = $iconSizes[$size] ?? $iconSizes['default'];
$valueSize = $valueSizes[$size] ?? $valueSizes['default'];
@endphp

<div class="group relative {{ $cardSize }} rounded-2xl border {{ $theme['border'] }} bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 dark:shadow-gray-900/50">
    <!-- Background decoration -->
    <div class="absolute inset-0 {{ $theme['bg'] }} {{ $theme['bgDark'] }} rounded-2xl opacity-50"></div>
    
    <!-- Top accent bar -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $theme['gradient'] }} rounded-t-2xl"></div>
    
    <div class="relative z-10">
        <!-- Header section -->
        <div class="flex items-start justify-between mb-4">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">{{ $title }}</p>
                @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            
            <!-- Icon with enhanced styling -->
            <div class="inline-flex {{ $iconSize }} shrink-0 items-center justify-center rounded-2xl {{ $theme['iconBg'] }} text-white shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl">
                {{ $icon }}
            </div>
        </div>
        
        <!-- Main value -->
        <div class="mb-4">
            <p class="{{ $valueSize }} font-bold text-gray-900 dark:text-white tracking-tight">{{ $value }}</p>
        </div>
        
        <!-- Progress bar (if provided) -->
        @if($progress !== null)
            <div class="mb-3">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-gradient-to-r {{ $theme['gradient'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif
        
        <!-- Footer section with change indicator -->
        <div class="flex items-center justify-between">
            @if($change)
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $theme['lightBg'] }} {{ $theme['darkLightBg'] }} {{ $changeColors[$changeType] }}">
                        <span class="mr-1">{{ $changeIcon[$changeType] }}</span>
                        {{ $change }}
                    </span>
                    @if($trend)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $trend }}</span>
                    @endif
                </div>
            @else
                <div class="h-6"></div>
            @endif
            
            <!-- Hover indicator -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Hover effect overlay -->
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r {{ $theme['gradient'] }} opacity-0 group-hover:opacity-5 transition-opacity duration-300 pointer-events-none"></div>
</div>
