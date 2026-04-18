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
            <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                <div class="flex flex-wrap items-center gap-3">

                    {{-- Search --}}
                    <div class="relative w-60">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari user atau aksi..."
                               class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    </div>

                    {{-- Date from --}}
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           title="Dari tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">

                    <span class="text-gray-400 text-xs">—</span>

                    {{-- Date to --}}
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           title="Sampai tanggal"
                           class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">

                    <button type="submit"
                            class="px-4 py-2 text-sm bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition-colors">
                        Filter
                    </button>

                    @if($search || $dateFrom || $dateTo)
                        <a href="{{ route('admin.activity-logs.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline underline-offset-2">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/60 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium w-10">#</th>
                        <th class="px-5 py-3 font-medium">Waktu</th>
                        <th class="px-5 py-3 font-medium">User Panel</th>
                        <th class="px-5 py-3 font-medium text-center">Role</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                        <th class="px-5 py-3 font-medium">Deskripsi</th>
                        <th class="px-5 py-3 font-medium">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php
                            $actionMap = [
                                'create_user'      => ['label' => 'Tambah User',    'color' => 'green'],
                                'update_user'      => ['label' => 'Edit User',      'color' => 'blue'],
                                'delete_user'      => ['label' => 'Hapus User',     'color' => 'red'],
                                'toggle_user'      => ['label' => 'Toggle User',    'color' => 'yellow'],
                                'create_package'   => ['label' => 'Tambah Paket',   'color' => 'green'],
                                'update_package'   => ['label' => 'Edit Paket',     'color' => 'blue'],
                                'delete_package'   => ['label' => 'Hapus Paket',    'color' => 'red'],
                                'toggle_package'   => ['label' => 'Toggle Paket',   'color' => 'yellow'],
                                'update_profile'   => ['label' => 'Edit Profil',    'color' => 'blue'],
                                'update_password'  => ['label' => 'Ganti Password', 'color' => 'purple'],
                                'generate_voucher' => ['label' => 'Generate Voucher','color' => 'green'],
                                'disable_voucher'  => ['label' => 'Nonaktif Voucher','color' => 'yellow'],
                                'enable_voucher'   => ['label' => 'Aktifkan Voucher','color' => 'blue'],
                                'delete_voucher'   => ['label' => 'Hapus Voucher',  'color' => 'red'],
                            ];
                            $action = $actionMap[$log->action] ?? ['label' => $log->action, 'color' => 'gray'];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">

                            <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500 text-xs">
                                {{ $logs->firstItem() + $loop->index }}
                            </td>

                            <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ $log->created_at->format('d M Y') }}</div>
                                <div class="font-mono text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $log->user?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $log->user?->username ?? '' }}
                                </div>
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                @if($log->user?->role === 'admin')
                                    <x-admin.badge color="purple">Admin</x-admin.badge>
                                @elseif($log->user?->role === 'operator')
                                    <x-admin.badge color="blue">Operator</x-admin.badge>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <x-admin.badge :color="$action['color']">{{ $action['label'] }}</x-admin.badge>
                            </td>

                            <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 max-w-xs">
                                {{ $log->description ?: '—' }}
                            </td>

                            <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                {{ $log->ip_address ?: '—' }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">
                                        @if($search || $dateFrom || $dateTo)
                                            Tidak ada log yang cocok dengan filter.
                                        @else
                                            Belum ada log aktivitas.
                                        @endif
                                    </p>
                                    @if($search || $dateFrom || $dateTo)
                                        <a href="{{ route('admin.activity-logs.index') }}"
                                           class="text-sm text-brand-600 hover:underline">Reset filter →</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif

    </x-admin.table>

@endsection
