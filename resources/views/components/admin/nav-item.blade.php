@props(['route', 'icon' => 'default'])

@php
    // Highlight jika di route ini atau sub-route dalam namespace yang sama (admin.X.*)
    $parts  = explode('.', $route);
    array_pop($parts);
    $ns     = implode('.', $parts);
    // Wildcard hanya jika ns punya minimal 2 segmen (e.g. admin.radius-users), bukan cuma 'admin'
    $active = request()->routeIs($route) || (str_contains($ns, '.') && request()->routeIs($ns . '.*'));
    $icons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'package'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'voucher'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
        'log'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
        'hotspot-log' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
        'default'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>',
    ];
    $iconPath = $icons[$icon] ?? $icons['default'];
@endphp

@if (\Illuminate\Support\Facades\Route::has($route))
    <a href="{{ route($route) }}"
       @class([
           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150',
           'bg-brand-600 text-white' => $active,
           'text-gray-300 hover:bg-gray-700 hover:text-white' => ! $active,
       ])>
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {!! $iconPath !!}
        </svg>
        <span>{{ $slot }}</span>
    </a>
@else
    <span @class([
           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium',
           'text-gray-500 cursor-not-allowed',
       ])>
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {!! $iconPath !!}
        </svg>
        <span>{{ $slot }}</span>
    </span>
@endif
