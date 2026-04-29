@props([
    'title' => 'Chart',
    'chartId' => 'chart',
    'chartType' => 'line',
    'height' => '300'
])

<div class="premium-card p-6 animate-fade-in-up">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
    </div>
    <div style="height: {{ $height }}px;">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>
