@extends('admin.layouts.app')

@section('title', 'User Hotspot')
@section('page-title', 'User Hotspot')

@section('content')

    <x-admin.page-header title="User Hotspot" description="Kelola user Hotspot ZeroNet.">
        <x-slot:actions>
            <a href="{{ route('admin.radius-users.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah User
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table>

        {{-- Search + Filter bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.radius-users.index') }}"
                  data-live-target="#radius-users-results"
                  class="flex flex-wrap items-center gap-3">

                {{-- Search --}}
                <div class="relative w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari username..." data-live-search
                        class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    @php $hasFilter = $search || $group || $status; @endphp
                    <button type="button" data-live-reset title="Reset semua filter"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 {{ $hasFilter ? '' : 'hidden' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Filter Profil --}}
                <div class="flex items-center gap-2">
                    <select name="group" data-live-submit
                        class="py-2 pl-3 pr-8 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">Semua Profil</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Status --}}
                    <select name="status" data-live-submit
                        class="py-2 pl-3 pr-8 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                </div>

            </form>
        </div>

        {{-- Container hasil (di-swap via AJAX saat search/filter/pagination) --}}
        <div id="radius-users-results" data-live-results class="transition-opacity duration-150">
            @include('admin.radius-users._results')
        </div>
    </x-admin.table>

@endsection


