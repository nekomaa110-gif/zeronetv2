@extends('admin.layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')

@section('content')

    <x-admin.page-header
        title="Log Aktivitas"
        description="Riwayat semua aksi yang dilakukan oleh admin dan operator panel ZeroNet."/>

    <x-admin.table>

        {{-- Filter bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}"
                  data-live-target="#activity-logs-results">
                <div class="flex flex-wrap items-center gap-3">

                    {{-- Search --}}
                    <div class="relative w-60">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari user atau aksi..." data-live-search
                               class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        @php $hasFilter = $search || $dateFrom || $dateTo; @endphp
                        <button type="button" data-live-reset title="Reset semua filter"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 {{ $hasFilter ? '' : 'hidden' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Date from --}}
                    <input type="date" name="date_from" value="{{ $dateFrom }}" data-live-submit
                           title="Dari tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">

                    <span class="text-gray-400 text-xs">—</span>

                    {{-- Date to --}}
                    <input type="date" name="date_to" value="{{ $dateTo }}" data-live-submit
                           title="Sampai tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                </div>
            </form>
        </div>

        {{-- Container hasil (di-swap via AJAX) --}}
        <div id="activity-logs-results" data-live-results class="transition-opacity duration-150">
            @include('admin.activity-logs._results')
        </div>
    </x-admin.table>

@endsection
