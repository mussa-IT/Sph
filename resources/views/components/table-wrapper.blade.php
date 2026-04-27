@props(['class' => ''])

<div class="table-scroll-x w-full {{ $class }}">
    <div class="min-w-full inline-block align-middle">
        {{ $slot }}
    </div>
</div>
