@props(['type' => 'success', 'message' => ''])

@php
    $tone = [
        'success' => 'ok',
        'error'   => 'err',
        'warning' => 'warn',
        'info'    => 'info',
    ][$type] ?? 'info';
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
     {{ $attributes->merge(['class' => 'alert tone-'.$tone]) }}
     style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid var(--border);border-radius:var(--r-md);font-size:13px;background:color-mix(in srgb, var(--{{ $tone === 'ok' ? 'ok' : ($tone === 'err' ? 'err' : ($tone === 'warn' ? 'warn' : 'info')) }}) 8%, var(--bg-elev));color:var(--{{ $tone === 'ok' ? 'ok' : ($tone === 'err' ? 'err' : ($tone === 'warn' ? 'warn' : 'info')) }})">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
        @if($type === 'success')
            <path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01" stroke-linecap="round" stroke-linejoin="round"/>
        @elseif($type === 'error')
            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        @elseif($type === 'warning')
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        @else
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        @endif
    </svg>
    <span style="flex:1;color:var(--text)">{{ $message }}</span>
    <button @click="show = false" type="button" class="icon-btn" style="width:24px;height:24px;border:0;background:transparent">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>
