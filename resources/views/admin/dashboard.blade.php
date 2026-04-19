@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <x-admin.page-header title="Selamat Datang, {{ auth()->user()->name }}!" description="Ringkasan Status WiFi ZeroNet." />

    {{-- Stat Cards (live polling tiap 30 detik) --}}
    <div x-data="{
            stats: {
                total_users:        {{ $stats['total_users'] }},
                active_users:       {{ $stats['active_users'] }},
                online_sessions:    {{ $stats['online_sessions'] }},
                available_vouchers: {{ $stats['available_vouchers'] }}
            },
            init() { setInterval(() => this.poll(), 30000); },
            async poll() {
                try {
                    const r = await axios.get('{{ route('admin.dashboard.stats') }}');
                    this.stats = r.data;
                } catch(e) {}
            }
         }"
         class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        {{-- Total User Hotspot --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total User Hotspot</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_users">{{ $stats['total_users'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- User Aktif --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.active_users">{{ $stats['active_users'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- User Online --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Online</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.online_sessions">{{ $stats['online_sessions'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Voucher Tersedia --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Voucher Tersedia</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.available_vouchers">{{ $stats['available_vouchers'] }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Log Aktivitas Terbaru --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h3>
            <a href="{{ route('admin.activity-logs.index') }}"
                class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">
                Lihat semua →
            </a>
        </div>

        @if ($recentLogs->isEmpty())
            <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada aktivitas tercatat.
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($recentLogs as $log)
                    <li class="flex items-start gap-3 px-5 py-3">
                        <div
                            class="flex-shrink-0 w-7 h-7 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mt-0.5">
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-300">
                                {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 dark:text-gray-200">
                                <span class="font-medium">{{ $log->user->name ?? 'Unknown' }}</span>
                                {{ $log->description }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <x-admin.badge :color="match ($log->action) {
                            'create' => 'green',
                            'update' => 'blue',
                            'delete' => 'red',
                            'login' => 'purple',
                            default => 'gray',
                        }">
                            {{ $log->action }}
                        </x-admin.badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

@endsection
