@extends('admin.layouts.app')

@section('title', 'User Hotspot')
@section('page-title', 'User Hotspot')

@section('content')

    <x-admin.page-header title="User Hotspot" description="Kelola user Hotspot.">
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

        {{-- Search bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.radius-users.index') }}">
                <div class="relative w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari username..."
                        class="w-full pl-9 pr-8 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                    @if ($search)
                        <a href="{{ route('admin.radius-users.index') }}"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel --}}
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
                                \Carbon\Carbon::createFromFormat('d M Y', $user['expiry'])->isPast();
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
                                        action="{{ route('admin.radius-users.toggle', $user['username']) }}">
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
                                    <a href="{{ route('admin.radius-users.edit', $user['username']) }}" title="Edit"
                                        class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- Hapus --}}
                                    <form method="POST"
                                        action="{{ route('admin.radius-users.destroy', $user['username']) }}">
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
                                @if ($search)
                                    Tidak ada user dengan username "<strong
                                        class="text-gray-600 dark:text-gray-300">{{ $search }}</strong>".
                                @else
                                    Belum ada user hotspot.
                                    <a href="{{ route('admin.radius-users.create') }}"
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
    </x-admin.table>

@endsection
