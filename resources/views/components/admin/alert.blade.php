@props(['type' => 'success', 'message' => ''])

@php
    $styles = [
        'success' => 'bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-700 text-green-800 dark:text-green-300',
        'error'   => 'bg-red-50 dark:bg-red-900/20 border-red-400 dark:border-red-700 text-red-800 dark:text-red-300',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300',
        'info'    => 'bg-blue-50 dark:bg-blue-900/20 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-300',
    ];
    $style = $styles[$type] ?? $styles['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     {{ $attributes->merge(['class' => "flex items-center gap-3 p-4 border rounded-lg text-sm $style"]) }}>
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        @if($type === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @elseif($type === 'error')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @elseif($type === 'warning')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        @endif
    </svg>
    <span class="flex-1">{{ $message }}</span>
    <button @click="show = false" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
