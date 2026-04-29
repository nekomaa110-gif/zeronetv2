<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi 2FA — {{ config('app.name') }}</title>
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

    <div class="absolute top-4 right-4" x-data="darkToggle()">
        <button @click="toggle()"
            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors"
            :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
            <svg x-show="!isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 p-8"
             x-data="{ mode: 'totp' }">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-11 h-11 bg-brand-600 rounded-xl flex items-center justify-center shadow-lg shadow-brand-600/25 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Verifikasi 2FA</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Masukkan kode 6 digit dari aplikasi authenticator.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-5 flex items-center gap-2.5 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-sm text-red-700 dark:text-red-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
                @csrf

                <div x-show="mode === 'totp'">
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Kode Otentikasi
                    </label>
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*"
                           maxlength="6" autocomplete="one-time-code" autofocus
                           x-bind:disabled="mode !== 'totp'"
                           placeholder="123456"
                           class="w-full px-4 py-3 text-center text-lg tracking-[0.4em] font-mono rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('code') ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }}">
                </div>

                <div x-show="mode === 'recovery'" x-cloak>
                    <label for="recovery_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Recovery Code
                    </label>
                    <input id="recovery_code" name="recovery_code" type="text" autocomplete="off"
                           x-bind:disabled="mode !== 'recovery'"
                           placeholder="xxxxx-xxxxx"
                           class="w-full px-4 py-3 text-sm font-mono rounded-xl border bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('recovery_code') ? 'border-red-400 dark:border-red-600' : 'border-gray-200 dark:border-gray-700' }}">
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-brand-600/25">
                    Verifikasi
                </button>
            </form>

            <div class="mt-5 text-center">
                <button type="button" x-show="mode === 'totp'" @click="mode = 'recovery'"
                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                    Gunakan recovery code
                </button>
                <button type="button" x-show="mode === 'recovery'" x-cloak @click="mode = 'totp'"
                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                    Kembali ke kode authenticator
                </button>
            </div>

            <form method="POST" action="{{ route('two-factor.cancel') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                    Batalkan login
                </button>
            </form>
        </div>
    </div>

    <script>
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
    </script>
</body>

</html>
