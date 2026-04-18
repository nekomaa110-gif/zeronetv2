@props(['color' => 'gray', 'dot' => false])

@php
    $colors = [
        'green'  => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'red'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'blue'   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'gray'   => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    ];
    $dotColors = [
        'green'  => 'bg-green-500',
        'red'    => 'bg-red-500',
        'yellow' => 'bg-yellow-400',
        'blue'   => 'bg-blue-500',
        'purple' => 'bg-purple-500',
        'gray'   => 'bg-gray-400',
    ];
    $style    = $colors[$color] ?? $colors['gray'];
    $dotStyle = $dotColors[$color] ?? $dotColors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium $style"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotStyle }}"></span>
    @endif
    {{ $slot }}
</span>
