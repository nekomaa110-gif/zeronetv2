{{-- Partial: tabel + pagination log aktivitas. Dipakai oleh full view dan response AJAX. --}}
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
                        'login'            => ['label' => 'Login',          'color' => 'green'],
                        'logout'           => ['label' => 'Logout',         'color' => 'gray'],
                        'login_failed'     => ['label' => 'Login Gagal',    'color' => 'red'],
                        'wa_send'          => ['label' => 'Kirim WA',       'color' => 'teal'],
                    ];
                    $action = $actionMap[$log->action] ?? ['label' => $log->action, 'color' => 'gray'];
                    $isSystem = is_null($log->user_id);
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
                        @if($isSystem)
                            <div class="font-medium text-gray-500 dark:text-gray-400 text-sm italic">Sistem</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">scheduler</div>
                        @else
                            <div class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $log->user?->name ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $log->user?->username ?? '' }}
                            </div>
                        @endif
                    </td>

                    <td class="px-5 py-3.5 text-center">
                        @if($isSystem)
                            <x-admin.badge color="gray">Sistem</x-admin.badge>
                        @elseif($log->user?->role === 'admin')
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
                                <a href="{{ route('activity-logs.index') }}"
                                   class="text-sm text-brand-600 hover:underline">Reset filter →</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($logs->hasPages())
    <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
        {{ $logs->withQueryString()->links() }}
    </div>
@endif
