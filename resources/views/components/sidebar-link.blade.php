@props([
    'href' => '#',
    'icon' => null,
    'label' => 'Link',
    'active' => false,
    'class' => ''
])

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => ($active ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700') . ' group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors ' . $class
]) }}>
    @if($icon)
        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {{ $icon }}
        </svg>
    @endif
    {{ $label }}
</a>
