{{-- Partial: tabel + pagination user hotspot. Dipakai oleh full view dan response AJAX. --}}
<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead
            class="bg-gray-50 dark:bg-gray-700/60 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <tr>
                <th class="px-5 py-3 font-medium w-10">#</th>
                <th class="px-5 py-3 font-medium">Username</th>
                <th class="px-5 py-3 font-medium">Profil / Paket</th>
                <th class="px-5 py-3 font-medium">Expire</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($users as $user)
                @php
                    $expired =
                        $user['expiry'] !== '-' &&
                        \Carbon\Carbon::parse($user['expiry'])->isPast();
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500 text-xs">
                        {{ $users->firstItem() + $loop->index }}
                    </td>
                    <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                        {{ $user['username'] }}
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($user['group'] !== '-')
                            <x-admin.badge color="blue">{{ $user['group'] }}</x-admin.badge>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($user['expiry'] !== '-')
                            <span
                                class="{{ $expired ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-300' }} text-xs">
                                {{ $user['expiry'] }}
                            </span>
                            @if ($expired)
                                <x-admin.badge color="red" class="ml-1">Expired</x-admin.badge>
                            @endif
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @if ($user['active'])
                            <x-admin.badge color="green" :dot="true">Aktif</x-admin.badge>
                        @else
                            <x-admin.badge color="red" :dot="true">Nonaktif</x-admin.badge>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1">

                            {{-- Toggle aktif/nonaktif --}}
                            <form method="POST"
                                action="{{ route('user-hotspot.toggle', $user['username']) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="{{ $user['active'] ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="p-1.5 rounded-lg transition-colors
                                               {{ $user['active']
                                                   ? 'text-yellow-500 hover:bg-yellow-50 dark:hover:bg-yellow-900/20'
                                                   : 'text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                                    @if ($user['active'])
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Edit --}}
                            <a href="{{ route('user-hotspot.edit', $user['username']) }}" title="Edit"
                                class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            {{-- Hapus --}}
                            <form method="POST"
                                action="{{ route('user-hotspot.destroy', $user['username']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus"
                                    class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                        @if ($search || $group || $status)
                            Tidak ada user
                            @if ($group) dengan profil "<strong class="text-gray-600 dark:text-gray-300">{{ $group }}</strong>"@endif
                            @if ($status) berstatus "<strong class="text-gray-600 dark:text-gray-300">{{ ucfirst($status) }}</strong>"@endif
                            @if ($search) yang cocok dengan "<strong class="text-gray-600 dark:text-gray-300">{{ $search }}</strong>"@endif.
                        @else
                            Belum ada user hotspot.
                            <a href="{{ route('user-hotspot.create') }}"
                                class="text-brand-600 hover:underline ml-1">Tambah sekarang →</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700">
        {{ $users->withQueryString()->links() }}
    </div>
@endif
