@props(['title' => '', 'subtitle' => '', 'class' => ''])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 {{ $class }}">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    
    <div class="p-6 {{ $title ? '' : 'pt-6' }}">
        {{ $slot }}
    </div>
</div>
