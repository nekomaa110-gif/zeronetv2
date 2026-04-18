@extends('admin.layouts.app')

@section('title', 'Paket / Profile')
@section('page-title', 'Paket / Profile')

@section('content')

    <x-admin.page-header title="Paket / Profile" description="Kelola paket dan profil untuk user hotspot ZeroNet.">
        <x-slot:actions>
            <a href="{{ route('admin.packages.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Paket
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead
                    class="bg-gray-50 dark:bg-gray-700/60 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Nama Paket</th>
                        <th class="px-5 py-3 font-medium">Deskripsi</th>
                        <th class="px-5 py-3 font-medium text-center">Atribut</th>
                        <th class="px-5 py-3 font-medium text-center">User</th>
                        <th class="px-5 py-3 font-medium text-center">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($packages as $i => $pkg)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pkg['groupname'] }}</span>
                                    @if($pkg['is_legacy'])
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                            Panel Lama
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400 text-xs max-w-xs truncate">
                                {{ $pkg['description'] ?: '—' }}
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                             {{ $pkg['attribute_count'] > 0 ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }}">
                                    {{ $pkg['attribute_count'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                             {{ $pkg['user_count'] > 0 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }}">
                                    {{ $pkg['user_count'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                @if ($pkg['is_active'])
                                    <x-admin.badge color="green">Aktif</x-admin.badge>
                                @else
                                    <x-admin.badge color="gray">Nonaktif</x-admin.badge>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    @if($pkg['is_legacy'])
                                        {{-- Profil panel lama: hanya Edit (import otomatis) --}}
                                        <a href="{{ route('admin.packages.legacy-edit', $pkg['groupname']) }}"
                                           title="Edit &amp; import ke panel baru"
                                           class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @else
                                        {{-- Toggle --}}
                                        <form method="POST" action="{{ route('admin.packages.toggle', $pkg['id']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="{{ $pkg['is_active'] ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="p-1.5 rounded-lg transition-colors
                                                           {{ $pkg['is_active'] ? 'text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    @if ($pkg['is_active'])
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.packages.edit', $pkg['id']) }}" title="Edit"
                                            class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.packages.destroy', $pkg['id']) }}">
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
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11" />
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada paket.</p>
                                    <a href="{{ route('admin.packages.create') }}"
                                        class="text-sm text-brand-600 hover:underline">
                                        Buat paket pertama →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.table>

@endsection
