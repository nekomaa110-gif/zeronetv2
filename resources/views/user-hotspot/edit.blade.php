@extends('layouts.app')

@section('title', 'Edit User: ' . $user['username'])
@section('page-title', 'Edit User Hotspot')

@section('content')

    <x-admin.page-header title="Edit User: {{ $user['username'] }}">
        <x-slot:actions>
            <a href="{{ route('user-hotspot.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="max-w-xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Edit User Hotspot</h3>
                <p class="text-xs text-gray-400 mt-0.5">Ubah paket, expire, atau password untuk <span class="font-mono font-semibold text-gray-500 dark:text-gray-400">{{ $user['username'] }}</span>.</p>
            </div>

            <form method="POST" action="{{ route('user-hotspot.update', $user['username']) }}"
                  x-data="{
                      showCurrent: false,
                      changePass: false,
                      showNew: false,
                      cancelChange() {
                          this.changePass = false;
                          this.showNew = false;
                          var f = document.getElementById('password');
                          if (f) f.value = '';
                      }
                  }"
                  class="px-6 py-5 space-y-5">
                @csrf
                @method('PUT')

                {{-- Username (tampilan saja, nilai dikirim via hidden) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                    <input type="text" value="{{ $user['username'] }}" readonly tabindex="-1"
                           class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700
                                  bg-gray-100 dark:bg-gray-900 text-gray-400 dark:text-gray-500
                                  cursor-not-allowed pointer-events-none select-none focus:outline-none">
                    <input type="hidden" name="username" value="{{ $user['username'] }}">
                </div>

                {{-- Password Saat Ini --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Password Saat Ini
                    </label>
                    <div class="relative">
                        <input :type="showCurrent ? 'text' : 'password'"
                               value="{{ $user['password'] }}"
                               readonly tabindex="-1"
                               class="w-full px-3 py-2.5 pr-11 text-sm rounded-lg border border-gray-200 dark:border-gray-700
                                      bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200
                                      cursor-default select-all focus:outline-none">
                        <button type="button" @click="showCurrent = !showCurrent" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-200 transition-colors focus:outline-none">
                            <svg x-show="!showCurrent" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg x-show="showCurrent" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Klik ikon mata untuk melihat password aktif user.</p>
                </div>

                {{-- Ganti Password toggle --}}
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">

                    {{-- Toggle bar --}}
                    <button type="button"
                            @click="changePass ? cancelChange() : (changePass = true)"
                            class="w-full flex items-center justify-between px-4 py-3 transition-colors"
                            :class="changePass
                                ? 'bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/30'
                                : 'bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700'">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0 transition-colors"
                                 :class="changePass ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span class="text-sm font-medium transition-colors"
                                  :class="changePass ? 'text-brand-700 dark:text-brand-300' : 'text-gray-700 dark:text-gray-300'">
                                Ganti Password
                            </span>
                            <span x-show="!changePass" class="text-xs text-gray-400 dark:text-gray-500">— password tidak akan diubah</span>
                        </div>
                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform duration-200"
                             :class="changePass ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Password Baru field (collapsible) --}}
                    <div x-show="changePass" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="px-4 pb-4 pt-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'"
                                   id="password" name="password"
                                   autocomplete="new-password"
                                   placeholder="Masukkan password baru..."
                                   class="w-full px-3 py-2.5 pr-11 text-sm rounded-lg border transition-colors
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400
                                          {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                          focus:outline-none focus:ring-2 focus:border-transparent">
                            <button type="button" @click="showNew = !showNew" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors focus:outline-none">
                                <svg x-show="!showNew" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg x-show="showNew" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Kosongkan untuk tidak mengubah password.</p>
                    </div>
                </div>

                {{-- Profil / Paket --}}
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Profil / Paket
                    </label>
                    <select id="group" name="group"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                        <option value="">— Tidak ada —</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}" {{ (old('group', $user['group']) === $g) ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Expire --}}
                <div>
                    <label for="expiry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Tanggal Expire
                    </label>
                    <input type="date" id="expiry" name="expiry"
                           value="{{ old('expiry', $user['expiry_input']) }}"
                           class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                  {{ $errors->has('expiry') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                  focus:outline-none focus:ring-2 focus:border-transparent">
                    @error('expiry')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Kosongkan untuk menghapus expire. Waktu otomatis disimpan sebagai 23:59:59.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Simpan Perubahan
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
