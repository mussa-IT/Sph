@props([
    'class' => '',
    'search' => false,
    'pagination' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden ' . $class]) }}>
    @if($search)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    {{ $search }}
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                {{ $header }}
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                {{ $rows }}
            </tbody>
        </table>
    </div>

    @if(isset($cards) && $cards->isNotEmpty())
        <div class="md:hidden px-6 py-4 space-y-4 bg-gray-50 dark:bg-gray-900/50">
            {{ $cards }}
        </div>
    @endif

    @if($pagination)
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            {{ $pagination }}
        </div>
    @endif
</div>
