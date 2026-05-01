@extends('layouts.app')

@section('title', 'User Hotspot')
@section('page-title', 'User Hotspot')

@section('content')

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">User Hotspot</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola akun radius user hotspot ZeroNet — buat, perpanjang, reset, dan pantau.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('user-hotspot.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="inline-flex items-center gap-2 h-9 px-3 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Export
            </a>
            <a href="{{ route('user-hotspot.create') }}"
               class="inline-flex items-center gap-2 h-9 px-3.5 text-sm font-medium rounded-lg text-white
                      bg-brand-600 hover:bg-brand-700 border border-brand-700
                      shadow-[0_1px_0_rgba(255,255,255,.18)_inset,0_1px_2px_rgba(37,99,235,.25),0_4px_14px_rgba(37,99,235,.18)]
                      transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah User
            </a>
        </div>
    </div>

    {{-- ── Stat strip ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @php
            $statCards = [
                ['label' => 'Total User',   'value' => $stats['total'],    'dot' => 'bg-brand-500',    'sub' => 'Semua akun terdaftar'],
                ['label' => 'Aktif',        'value' => $stats['active'],   'dot' => 'bg-green-500',    'sub' => $stats['total'] > 0 ? round($stats['active']/$stats['total']*100).'% dari total' : '—'],
                ['label' => 'Expired',      'value' => $stats['expired'],  'dot' => 'bg-red-500',      'sub' => 'Perlu diperpanjang'],
                ['label' => 'Nonaktif',     'value' => $stats['disabled'], 'dot' => 'bg-gray-400',     'sub' => 'Diblokir admin'],
            ];
        @endphp
        @foreach ($statCards as $c)
            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 transition-colors hover:border-gray-300 dark:hover:border-gray-600">
                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                    {{ $c['label'] }}
                </div>
                <div class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white tabular-nums">
                    {{ number_format($c['value']) }}
                </div>
                <div class="mt-1 text-[11.5px] text-gray-500 dark:text-gray-400">{{ $c['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ── Toolbar (segmented filters + search) ───────────────── --}}
    <form method="GET" action="{{ route('user-hotspot.index') }}"
          data-live-target="#radius-users-results"
          class="mb-3.5 flex flex-wrap items-center gap-2">

        @php
            $segments = [
                ['k' => '',         'l' => 'Semua',    'c' => $stats['total']],
                ['k' => 'aktif',    'l' => 'Aktif',    'c' => $stats['active']],
                ['k' => 'expired',  'l' => 'Expired',  'c' => $stats['expired']],
                ['k' => 'nonaktif', 'l' => 'Nonaktif', 'c' => $stats['disabled']],
            ];
        @endphp

        {{-- Segmented status pills --}}
        <div class="inline-flex items-center gap-0.5 p-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            @foreach ($segments as $seg)
                @php $on = (string)$status === (string)$seg['k']; @endphp
                <button type="button"
                        data-status-pick="{{ $seg['k'] }}"
                        class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-md text-[12.5px] font-medium transition-colors
                               {{ $on
                                    ? 'bg-brand-50 text-brand-600 dark:bg-brand-600/15 dark:text-brand-400'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    {{ $seg['l'] }}
                    <span class="px-1.5 rounded-full text-[11px] font-medium
                                 {{ $on
                                    ? 'bg-brand-100 text-brand-600 dark:bg-brand-600/25 dark:text-brand-300'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $seg['c'] }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Hidden status field driven by the segmented buttons --}}
        <input type="hidden" name="status" id="status-input" value="{{ $status }}" data-live-submit>

        {{-- Search --}}
        <div class="relative w-60">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari username..."
                   data-live-search
                   class="w-full pl-8 pr-9 h-8 text-[13px] rounded-lg border border-gray-200 dark:border-gray-700
                          bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400
                          focus:outline-none focus:ring-[3px] focus:ring-brand-500/20 focus:border-brand-500 transition">
            @php $hasFilter = $search || $group || $status; @endphp
            <button type="button" data-live-reset title="Reset semua filter"
                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 {{ $hasFilter ? '' : 'hidden' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Group / profile filter chip (native select styled as chip) --}}
        <div class="relative">
            <select name="group" data-live-submit
                    class="appearance-none h-8 pl-3 pr-7 text-[12.5px] font-medium rounded-lg border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                           focus:outline-none focus:ring-[3px] focus:ring-brand-500/20 focus:border-brand-500">
                <option value="">Semua Profil</option>
                @foreach ($groups as $g)
                    <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
            </svg>
        </div>
    </form>

    {{-- ── Table card ─────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-[0_1px_2px_rgba(15,23,42,.04)] overflow-hidden">
        <div id="radius-users-results" data-live-results class="transition-opacity duration-150">
            @include('user-hotspot._results')
        </div>
    </div>

    {{-- Wire up segmented status buttons → hidden input → live submit --}}
    <script>
        (function () {
            const form = document.currentScript.previousElementSibling; // not used; safer query below
            document.querySelectorAll('[data-status-pick]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const pickerForm = btn.closest('form');
                    const hidden = pickerForm?.querySelector('#status-input');
                    if (!hidden) return;
                    hidden.value = btn.getAttribute('data-status-pick');
                    // Trigger the global live-search handler — input event matches data-live-submit semantics
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                    hidden.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        })();
    </script>

@endsection
