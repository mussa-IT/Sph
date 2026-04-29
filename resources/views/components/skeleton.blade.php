{{-- Skeleton Loading Component --}}
@props([
    'class' => '',
    'variant' => 'text', // text, card, avatar, button, table
    'lines' => 3,
    'width' => null,
    'height' => null,
])

@php
$baseClasses = 'animate-pulse rounded-lg bg-muted/30 dark:bg-muted-dark/30';

$variantClasses = match($variant) {
    'text' => 'h-4',
    'card' => 'h-24',
    'avatar' => 'h-12 w-12 rounded-full',
    'button' => 'h-10 w-24',
    'table' => 'h-12',
    default => 'h-4',
};

$style = '';
if ($width) {
    $style .= 'width: ' . $width . ';';
}
if ($height) {
    $style .= 'height: ' . $height . ';';
}
@endphp

@if($variant === 'text' && $lines > 1)
    <div class="space-y-2 {{ $class }}">
        @for($i = 0; $i < $lines; $i++)
            <div class="{{ $baseClasses }} {{ $variantClasses }}" style="{{ $i === $lines - 1 ? 'width: 60%;' : 'width: 100%;' }}"></div>
        @endfor
    </div>
@elseif($variant === 'table')
    <div class="space-y-3 {{ $class }}">
        {{-- Table header skeleton --}}
        <div class="flex gap-4">
            @for($i = 0; $i < 4; $i++)
                <div class="{{ $baseClasses }} h-6 flex-1"></div>
            @endfor
        </div>
        {{-- Table rows skeleton --}}
        @for($i = 0; $i < 5; $i++)
            <div class="flex gap-4">
                @for($j = 0; $j < 4; $j++)
                    <div class="{{ $baseClasses }} h-12 flex-1"></div>
                @endfor
            </div>
        @endfor
    </div>
@else
    <div class="{{ $baseClasses }} {{ $variantClasses }} {{ $class }}" style="{{ $style }}"></div>
@endif
