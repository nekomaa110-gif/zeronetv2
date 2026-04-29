@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')

    <x-admin.page-header
        title="Profil Saya"
        description="Kelola informasi akun dan keamanan login Anda."/>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Kolom Kiri: Informasi Akun + Info Sesi ──────────────────────── --}}
        <div class="flex flex-col gap-6">

            {{-- Informasi Akun --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

                {{-- Header card --}}
                <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="w-10 h-10 rounded-full bg-brand-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Informasi Akun</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Nama dan username yang tampil di panel.</p>
                    </div>
                </div>

                @if(session('success_info'))
                    <div class="px-6 pt-4">
                        <x-admin.alert type="success">{{ session('success_info') }}</x-admin.alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-info') }}" class="px-6 py-5 space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Nama --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', $user->name) }}"
                               placeholder="Nama lengkap Anda"
                               class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                      {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                      focus:outline-none focus:ring-2 focus:border-transparent">
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username"
                               value="{{ old('username', $user->username) }}"
                               placeholder="username"
                               class="w-full px-3 py-2.5 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                      {{ $errors->has('username') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                      focus:outline-none focus:ring-2 focus:border-transparent">
                        @error('username')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role (read-only) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role</label>
                        <div class="flex items-center gap-2 px-3 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                            {{ ucfirst($user->role) }}
                            <span class="text-xs text-gray-400 dark:text-gray-500">(tidak dapat diubah)</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Simpan Informasi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info Sesi --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Info Sesi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Detail waktu akses akun ini.</p>
                </div>
                <dl class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Login terakhir</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Akun dibuat</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->created_at->format('d M Y') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Role</dt>
                        <dd>
                            @if($user->role === 'admin')
                                <x-admin.badge color="purple">Admin</x-admin.badge>
                            @else
                                <x-admin.badge color="blue">Operator</x-admin.badge>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- ── Kolom Kanan: Ubah Password ───────────────────────────────────── --}}
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full"
                 x-data="{ showCurrent: false, showNew: false, showConfirm: false }">

                {{-- Header card --}}
                <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ubah Password</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Gunakan password yang kuat dan unik.</p>
                    </div>
                </div>

                @if(session('success_password'))
                    <div class="px-6 pt-4">
                        <x-admin.alert type="success">{{ session('success_password') }}</x-admin.alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-password') }}" class="px-6 py-5 space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Password saat ini --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'"
                                   id="current_password" name="current_password"
                                   placeholder="••••••••"
                                   class="w-full px-3 py-2.5 pr-10 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                          {{ $errors->has('current_password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                          focus:outline-none focus:ring-2 focus:border-transparent">
                            <button type="button" @click="showCurrent = !showCurrent" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg x-show="!showCurrent" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showCurrent" style="display:none" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password baru --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'"
                                   id="password" name="password"
                                   placeholder="••••••••"
                                   class="w-full px-3 py-2.5 pr-10 text-sm rounded-lg border transition-colors bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                          {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:ring-brand-500' }}
                                          focus:outline-none focus:ring-2 focus:border-transparent">
                            <button type="button" @click="showNew = !showNew" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg x-show="!showNew" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showNew" style="display:none" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Minimal 8 karakter.</p>
                    </div>

                    {{-- Konfirmasi password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'"
                                   id="password_confirmation" name="password_confirmation"
                                   placeholder="••••••••"
                                   class="w-full px-3 py-2.5 pr-10 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                          focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-colors">
                            <button type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showConfirm" style="display:none" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- ── Two-Factor Authentication ─────────────────────────────────────── --}}
    <div class="mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">

            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Two-Factor Authentication (2FA)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Lindungi akun dengan kode dari aplikasi authenticator.</p>
                </div>
                @if ($user->hasTwoFactorEnabled())
                    <x-admin.badge color="green">Aktif</x-admin.badge>
                @else
                    <x-admin.badge color="gray">Nonaktif</x-admin.badge>
                @endif
            </div>

            @if(session('success_2fa'))
                <div class="px-6 pt-4">
                    <x-admin.alert type="success" :message="session('success_2fa')"/>
                </div>
            @endif

            <div class="px-6 py-5">
                @if ($user->hasTwoFactorEnabled())
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        2FA aktif sejak {{ $user->two_factor_confirmed_at->translatedFormat('d M Y H:i') }}.
                        Setiap login akan meminta kode 6 digit dari authenticator Anda.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Regenerate recovery codes --}}
                        <form method="POST" action="{{ route('two-factor.regenerate-codes') }}"
                              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            @csrf
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Regenerate Recovery Codes</h4>
                            <p class="text-xs text-gray-400 mb-3">Generate ulang recovery codes (yang lama akan tidak berlaku).</p>
                            <input type="password" name="current_password" required
                                   placeholder="Password saat ini"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent mb-2">
                            @error('current_password')
                                <p class="mb-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-lg transition-colors">
                                Regenerate
                            </button>
                        </form>

                        {{-- Disable 2FA --}}
                        <form method="POST" action="{{ route('two-factor.disable') }}"
                              onsubmit="return confirm('Yakin nonaktifkan 2FA? Akun jadi kurang aman.');"
                              class="border border-red-200 dark:border-red-900/50 rounded-lg p-4">
                            @csrf
                            @method('DELETE')
                            <h4 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">Nonaktifkan 2FA</h4>
                            <p class="text-xs text-gray-400 mb-3">Hapus 2FA — login hanya akan butuh password.</p>
                            <input type="password" name="current_password" required
                                   placeholder="Password saat ini"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent mb-2">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                Nonaktifkan
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Saat ini akun Anda hanya dilindungi password. Aktifkan 2FA agar login juga butuh kode dari aplikasi authenticator.
                    </p>
                    <a href="{{ route('two-factor.setup') }}"
                       class="inline-flex items-center gap-2 px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Aktifkan 2FA
                    </a>
                @endif
            </div>
        </div>
    </div>

@endsection
