<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <script>
        (function() {
            if (localStorage.theme === 'dark' ||
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .no-transition, .no-transition * { transition: none !important; }
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans antialiased">

    {{-- Dark mode toggle (pojok kanan atas) --}}
    <div class="absolute top-4 right-4" x-data="darkToggle()">
        <button @click="toggle()"
            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors"
            :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
            <svg x-show="!isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    {{-- Container utama --}}
    <div class="min-h-screen flex">

        {{-- Panel kiri: branding (hanya tampil di layar besar) --}}
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-3/5 bg-gray-900 dark:bg-gray-950 relative overflow-hidden flex-col items-center justify-center p-12">

            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            {{-- Glow effect --}}
            <div
                class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brand-600 rounded-full opacity-10 blur-3xl pointer-events-none">
            </div>

            {{-- Content --}}
            <div class="relative z-10 text-center">
                <div
                    class="w-20 h-20 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-2xl shadow-brand-600/30">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-3">ZeroNet</h1>
                <p class="text-gray-400 text-lg mb-10">Panel Manajemen User</p>
            </div>
        </div>

        {{-- Panel kanan: form login --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-white dark:bg-gray-900">
            <div class="w-full max-w-sm">

                {{-- Logo kecil (mobile only) --}}
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <div class="w-9 h-9 bg-brand-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white text-lg">ZeroNet</span>
                </div>

                {{-- Heading --}}
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Selamat datang</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Masuk untuk melanjutkan ke panel ZeroNet</p>
                </div>

                {{-- Alert error --}}
                @if ($errors->any())
                    <div
                        class="mb-5 flex items-center gap-2.5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-sm text-red-700 dark:text-red-400">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div
                        class="mb-5 p-3.5 rounded-xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900 text-sm text-green-700 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                {{--
                    autocomplete="off" pada form mencegah browser mengisi otomatis
                    credential yang tersimpan secara keseluruhan.
                    Username diisi manual oleh JS dari localStorage jika "Ingat saya" pernah dipilih.
                --}}
                <form id="login-form" method="POST" action="{{ route('login') }}"
                      class="space-y-4" autocomplete="off">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input id="login" name="login" type="text"
                                value="{{ old('login') }}"
                                required autofocus
                                autocomplete="off"
                                placeholder="Masukkan username anda"
                                class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400
                                          transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                          {{ $errors->has('login') ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }}">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }">
                        <label for="password"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            {{--
                                autocomplete="new-password" mencegah browser mengisi password
                                yang tersimpan ke field ini, lebih andal dari autocomplete="off"
                                untuk field password di semua browser modern.
                            --}}
                            <input id="password" name="password"
                                :type="show ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                placeholder="Masukkan password anda"
                                class="w-full pl-10 pr-12 py-3 text-sm rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400
                                          transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                          {{ $errors->has('password') ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }}">
                            {{-- Toggle show/hide --}}
                            <button type="button" @click="show = !show" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" style="display:none" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input id="remember" type="checkbox" name="remember"
                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-brand-600 focus:ring-brand-500 bg-white dark:bg-gray-800">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-1">
                        <button type="submit"
                            class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-brand-600/25">
                            Masuk ke Panel
                        </button>
                    </div>
                </form>

                {{-- Footer --}}
                <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-600">
                    ZeroNet &copy; {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>

    <script>
        // ── Dark mode toggle ──────────────────────────────────────────────
        function darkToggle() {
            return {
                isDark: document.documentElement.classList.contains('dark'),
                toggle() {
                    document.documentElement.classList.add('no-transition');
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        document.documentElement.classList.remove('no-transition');
                    }));
                }
            };
        }

        // ── Remember username (bukan password) ───────────────────────────
        (function () {
            var STORAGE_KEY = 'zeronet_remember_login';
            var loginInput    = document.getElementById('login');
            var rememberBox   = document.getElementById('remember');
            var form          = document.getElementById('login-form');

            // Saat halaman terbuka: isi username jika pernah disimpan
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                // Hanya isi jika tidak ada nilai dari old() Laravel (validasi gagal)
                if (!loginInput.value) {
                    loginInput.value = saved;
                }
                rememberBox.checked = true;
            }

            // Saat form submit: simpan atau hapus username sesuai centang
            form.addEventListener('submit', function () {
                if (rememberBox.checked && loginInput.value.trim()) {
                    localStorage.setItem(STORAGE_KEY, loginInput.value.trim());
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            });
        })();
    </script>
</body>

</html>
