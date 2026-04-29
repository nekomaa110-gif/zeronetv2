@extends('layouts.app')

@section('title', 'Tambah User Hotspot')
@section('page-title', 'Tambah User Hotspot')

@section('content')

    <x-admin.page-header title="Tambah User Hotspot">
        <x-slot:actions>
            <a href="{{ route('user-hotspot.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                ← Kembali
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('user-hotspot.store') }}">
                @csrf

                {{-- Username --}}
                <div class="mb-5">
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="off"
                        class="w-full px-3 py-2 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  {{ $errors->has('username') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                  focus:outline-none focus:ring-2 focus:border-transparent"
                        placeholder="contoh: user01">
                    @error('username')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-5" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" autocomplete="new-password"
                            class="w-full px-3 py-2 pr-11 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                      {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                      focus:outline-none focus:ring-2 focus:border-transparent">
                        <button type="button" @click="show = !show" tabindex="-1" title="Tampilkan/sembunyikan password"
                            class="absolute inset-y-0 right-0 flex items-center justify-center w-10 rounded-r-lg text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200 transition-colors focus:outline-none">
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Password bebas panjang, minimal 1 karakter.</p>
                </div>

                {{-- Profil / Paket --}}
                <div class="mb-5">
                    <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Profil / Paket
                    </label>
                    <select id="group" name="group"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">— Tidak ada —</option>
                        @foreach ($groups as $g)
                            <option value="{{ $g }}" {{ old('group') === $g ? 'selected' : '' }}>
                                {{ $g }}</option>
                        @endforeach
                    </select>
                    @if ($groups->isEmpty())
                        <p class="mt-1.5 text-xs text-yellow-600 dark:text-yellow-400">
                            Belum ada paket. <a href="{{ route('packages.index') }}" class="underline">Buat paket
                                dahulu →</a>
                        </p>
                    @endif
                </div>

                {{-- Expire --}}
                <div class="mb-6">
                    <label for="expiry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Tanggal Expire
                    </label>
                    <input type="date" id="expiry" name="expiry" value="{{ old('expiry') }}"
                        class="w-full px-3 py-2 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  {{ $errors->has('expiry') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                  focus:outline-none focus:ring-2 focus:border-transparent">
                    @error('expiry')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Kosongkan jika tidak ada expire. Waktu otomatis disimpan sebagai 23:59:59.
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                        class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Simpan User
                    </button>
                    <a href="{{ route('user-hotspot.index') }}"
                        class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
