@props([
    'label'  => '',
    'value'  => '0',
    'icon'   => 'default',
    'color'  => 'blue',
    'trend'  => null,
])

@php
    $colors = [
        'blue'   => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
        'green'  => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
        'red'    => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
        'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    ];
    $iconColor = $colors[$color] ?? $colors['blue'];

    $icons = [
        'users'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'online'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>',
        'voucher' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
        'active'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'default' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
    $iconPath = $icons[$icon] ?? $icons['default'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
            @if($trend)
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $trend }}</p>
            @endif
        </div>
        <div class="flex-shrink-0 w-12 h-12 rounded-lg {{ $iconColor }} flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {!! $iconPath !!}
            </svg>
        </div>
    </div>
</div>
