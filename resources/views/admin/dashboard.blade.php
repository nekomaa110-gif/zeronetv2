@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <x-admin.page-header title="Selamat Datang, {{ auth()->user()->name }}!" description="Ringkasan Status WiFi ZeroNet." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-card label="Total User Hotspot" :value="$stats['total_users']" icon="users" color="blue" />
        <x-admin.stat-card label="User Aktif" :value="$stats['active_users']" icon="active" color="green" />
        <x-admin.stat-card label="User Online" :value="$stats['online_sessions']" icon="online" color="purple" />
        <x-admin.stat-card label="Voucher Tersedia" :value="$stats['available_vouchers']" icon="voucher" color="yellow" />
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
