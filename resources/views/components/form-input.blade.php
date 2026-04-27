@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'value' => '',
    'label' => '',
    'required' => false,
    'autofocus' => false,
])

@php
$inputId = $id ?? $name;
@endphp

<div class="space-y-2">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-foreground dark:text-foreground-dark">{{ $label }}</label>
    @endif
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        @if($autofocus) autofocus @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition duration-200 placeholder:text-muted focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:placeholder:text-muted-dark'
        ]) }}
    />
</div>
