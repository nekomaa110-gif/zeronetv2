<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    {{-- Cegah flicker dark mode: jalankan SEBELUM CSS dimuat --}}
    <script>
        (function() {
            var dark = localStorage.theme === 'dark' ||
                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.background = '#111827';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.background = '#f3f4f6';
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Sembunyikan elemen x-cloak sampai Alpine selesai init --}}
    <style>
        [x-cloak] { display: none !important; }
        /* Matikan semua transisi sementara saat ganti tema agar tidak flicker */
        .no-transition, .no-transition * { transition: none !important; }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">

    {{-- Sidebar --}}
    <aside id="sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0"
        aria-label="Sidebar">
        <div class="h-full flex flex-col bg-gray-800 dark:bg-gray-950 overflow-y-auto">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700">
                <div class="flex-shrink-0 w-9 h-9 bg-brand-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <div>
                    <span class="text-white font-bold text-base leading-tight">ZeroNet</span>
                    <p class="text-gray-400 text-xs">Hotspot Manager</p>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-1">
                <x-admin.nav-item route="admin.dashboard" icon="dashboard">Dashboard</x-admin.nav-item>
                <x-admin.nav-item route="admin.radius-users.index" icon="users">User Hotspot</x-admin.nav-item>
                <x-admin.nav-item route="admin.packages.index" icon="package">Paket / Profile</x-admin.nav-item>
                <x-admin.nav-item route="admin.vouchers.index" icon="voucher">Voucher</x-admin.nav-item>
                <x-admin.nav-item route="admin.routers.index" icon="router">Manajemen Router</x-admin.nav-item>
                <x-admin.nav-item route="admin.whatsapp.index" icon="chat">WhatsApp Gateway</x-admin.nav-item>
                <div class="my-3 border-t border-gray-700"></div>
                <x-admin.nav-item route="admin.hotspot-logs.index" icon="hotspot-log">Log Hotspot</x-admin.nav-item>
                <x-admin.nav-item route="admin.activity-logs.index" icon="log">Log Aktivitas</x-admin.nav-item>
            </nav>

            {{-- User info --}}
            <div class="px-3 py-4 border-t border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main wrapper --}}
    <div class="sm:ml-64">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">

                {{-- Mobile toggle --}}
                <button type="button" data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar"
                    class="sm:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="hidden sm:block">
                    <h1 class="text-base font-semibold text-gray-800 dark:text-white">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-2 ml-auto">

                    {{-- Dark mode toggle --}}
                    <div x-data="darkToggle()">
                        <button @click="toggle()"
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
                            :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
                            {{-- Ikon bulan: muncul saat light mode --}}
                            <svg x-show="!isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            {{-- Ikon matahari: muncul saat dark mode --}}
                            <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- User dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-brand-600 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                            <span class="hidden sm:block text-sm text-gray-700 dark:text-gray-200 font-medium">
                                {{ auth()->user()->name }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak x-transition
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 z-50">
                            <a href="{{ route('admin.profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profil Saya
                            </a>
                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="p-4 md:p-6">
            @if (session('success'))
                <x-admin.alert type="success" :message="session('success')" class="mb-4" />
            @endif
            @if (session('error'))
                <x-admin.alert type="error" :message="session('error')" class="mb-4" />
            @endif
            @if (session('forbidden'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="flex items-start gap-3 mb-4 px-4 py-3 rounded-lg border bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700 text-orange-800 dark:text-orange-300 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0-6v2m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="flex-1">{{ session('forbidden') }}</span>
                    <button @click="show = false" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function pageAutoRefresh(seconds) {
            return {
                remaining: seconds,
                total: seconds,
                _timer: null,
                init() {
                    this._timer = setInterval(() => {
                        this.remaining--;
                        if (this.remaining <= 0) location.reload();
                    }, 1000);
                },
                reset() { this.remaining = this.total; },
                get pct() { return (this.remaining / this.total) * 100; }
            };
        }

        function darkToggle() {
            return {
                isDark: document.documentElement.classList.contains('dark'),
                toggle() {
                    // Nonaktifkan semua transisi dulu agar tidak ada glitch setengah halaman
                    document.documentElement.classList.add('no-transition');
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    // Aktifkan kembali transisi setelah browser selesai render frame baru
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        document.documentElement.classList.remove('no-transition');
                    }));
                }
            };
        }
    </script>
    {{-- Live search:
         - <input data-live-search>   → debounced auto-submit (default 400ms; override: data-live-search="600")
         - <form data-live-target="#id"> → AJAX swap target HTML alih-alih full reload
         - <select data-live-submit>   → trigger live-submit (AJAX bila form punya data-live-target) --}}
    <script>
        (function () {
            const FOCUS_KEY = '__live_search_focus__';
            const SPINNER_SVG = '<svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';

            // Restore focus + caret setelah full reload (non-AJAX path)
            const last = sessionStorage.getItem(FOCUS_KEY);
            if (last) {
                sessionStorage.removeItem(FOCUS_KEY);
                const el = document.querySelector('[data-live-search][name="' + CSS.escape(last) + '"]');
                if (el) {
                    el.focus();
                    const len = el.value.length;
                    try { el.setSelectionRange(len, len); } catch (_) {}
                }
            }

            // Spinner: pasang di setiap input live-search (di dalam wrapper .relative-nya)
            function ensureSpinner(input) {
                const wrap = input.parentElement;
                if (!wrap) return null;
                let sp = wrap.querySelector('[data-live-spinner]');
                if (!sp) {
                    sp = document.createElement('span');
                    sp.setAttribute('data-live-spinner', '');
                    sp.className = 'absolute inset-y-0 right-0 hidden items-center pr-3 pointer-events-none';
                    sp.innerHTML = SPINNER_SVG;
                    wrap.appendChild(sp);
                }
                return sp;
            }
            function showSpinner(input) {
                const sp = ensureSpinner(input);
                if (sp) { sp.classList.remove('hidden'); sp.classList.add('flex'); }
                // Sembunyikan tombol clear-X / reset-X saat loading agar tidak menumpuk
                input.parentElement?.querySelectorAll('[data-live-clear], [data-live-reset]')
                    .forEach(b => b.classList.add('hidden'));
            }
            function hideSpinner(input) {
                if (!input) return;
                const sp = input.parentElement?.querySelector('[data-live-spinner]');
                if (sp) { sp.classList.add('hidden'); sp.classList.remove('flex'); }
                // Tampilkan kembali clear-X / reset-X sesuai state form
                syncClearBtn(input);
                if (input.form) syncResetBtn(input.form);
            }

            // Build URL dari form state (skip empty values agar URL bersih)
            function formUrl(form) {
                const data = new FormData(form);
                const params = new URLSearchParams();
                for (const [k, v] of data.entries()) {
                    if (v !== '' && v !== null && k !== '_token' && k !== '_method') params.append(k, v);
                }
                const base = (form.action || window.location.href).split('?')[0];
                const qs = params.toString();
                return qs ? base + '?' + qs : base;
            }

            let currentCtrl = null;

            async function ajaxSwap(url, targetEl, sourceInput) {
                if (currentCtrl) currentCtrl.abort();
                currentCtrl = new AbortController();

                if (sourceInput) showSpinner(sourceInput);
                targetEl.style.opacity = '0.55';
                targetEl.style.pointerEvents = 'none';

                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        signal: currentCtrl.signal,
                        credentials: 'same-origin',
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const html = await res.text();
                    targetEl.innerHTML = html;
                    history.pushState({ liveSearch: true }, '', url);
                    targetEl.dispatchEvent(new CustomEvent('live-search:loaded', { bubbles: true }));
                } catch (e) {
                    if (e.name !== 'AbortError') console.error('[live-search]', e);
                } finally {
                    targetEl.style.opacity = '';
                    targetEl.style.pointerEvents = '';
                    if (sourceInput) hideSpinner(sourceInput);
                }
            }

            function triggerSubmit(form, sourceInput) {
                const targetSel = form.getAttribute('data-live-target');
                const targetEl  = targetSel ? document.querySelector(targetSel) : null;

                if (targetEl) {
                    ajaxSwap(formUrl(form), targetEl, sourceInput);
                } else {
                    // Fallback: full reload + simpan focus
                    if (sourceInput) showSpinner(sourceInput);
                    if (sourceInput?.name) sessionStorage.setItem(FOCUS_KEY, sourceInput.name);
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            }

            // Toggle visibility tombol clear (data-live-clear) sesuai isi input terdekat
            function syncClearBtn(input) {
                const btn = input.parentElement?.querySelector('[data-live-clear]');
                if (btn) btn.classList.toggle('hidden', !input.value);
            }

            // Toggle visibility tombol reset-all (data-live-reset) berdasarkan ada/tidaknya filter aktif
            function syncResetBtn(form) {
                const btn = form.querySelector('[data-live-reset]');
                if (!btn) return;
                let hasFilter = false;
                form.querySelectorAll('input[name], select[name]').forEach(function (el) {
                    if (el.type === 'hidden' || el.name === '_token' || el.name === '_method') return;
                    if (el.value) hasFilter = true;
                });
                btn.classList.toggle('hidden', !hasFilter);
            }
            // Sync untuk semua form yang punya tombol reset, saat init dan setelah AJAX load
            function syncAllResetBtns() {
                document.querySelectorAll('form').forEach(syncResetBtn);
            }

            // Bind input live-search
            document.querySelectorAll('[data-live-search]').forEach(function (input) {
                ensureSpinner(input);
                syncClearBtn(input);
                const delay = parseInt(input.dataset.liveSearch, 10) || 400;
                let timer = null;
                let lastVal = input.value;

                input.addEventListener('input', function () {
                    syncClearBtn(input);
                    if (input.form) syncResetBtn(input.form);
                    if (input.value === lastVal) return;
                    lastVal = input.value;
                    clearTimeout(timer);
                    if (input.form) showSpinner(input); // visual feedback langsung
                    timer = setTimeout(function () {
                        if (!input.form) return;
                        triggerSubmit(input.form, input);
                    }, delay);
                });

                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        clearTimeout(timer);
                        if (input.form) {
                            e.preventDefault();
                            triggerSubmit(input.form, input);
                        }
                    }
                });
            });

            // Bind tombol clear (X) — kosongkan input + langsung submit
            document.querySelectorAll('[data-live-clear]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const wrap = btn.parentElement;
                    const input = wrap?.querySelector('[data-live-search]');
                    if (!input) return;
                    input.value = '';
                    syncClearBtn(input);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    if (input.form) triggerSubmit(input.form, input);
                    input.focus();
                });
            });

            // Bind tombol reset (X) — kosongkan SEMUA filter di form lalu submit
            document.querySelectorAll('[data-live-reset]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const form = btn.closest('form');
                    if (!form) return;
                    e.preventDefault();
                    form.querySelectorAll('input[name], select[name]').forEach(function (el) {
                        if (el.type === 'hidden' || el.name === '_token' || el.name === '_method') return;
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        else el.value = '';
                    });
                    const search = form.querySelector('[data-live-search]');
                    if (search) syncClearBtn(search);
                    triggerSubmit(form, search);
                });
            });

            // Bind elemen live-submit (select / input date dll) di form ber-data-live-target
            document.querySelectorAll('[data-live-submit]').forEach(function (el) {
                el.addEventListener('change', function () {
                    if (el.form) {
                        syncResetBtn(el.form);
                        triggerSubmit(el.form, el.form.querySelector('[data-live-search]'));
                    }
                });
            });

            // Init reset-button visibility
            syncAllResetBtns();

            // Intercept native form submit untuk form yang punya data-live-target
            // (misal: tombol clear-X, tombol "Filter", atau Enter di field non-search)
            document.querySelectorAll('form[data-live-target]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    triggerSubmit(form, form.querySelector('[data-live-search]'));
                });
            });

            // Intercept klik link pagination/anchor di dalam target AJAX
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a[href]');
                if (!link) return;
                const targetEl = link.closest('[data-live-results]');
                if (!targetEl) return;
                if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;
                // Cari form yang punya target ke container ini
                const form = document.querySelector('form[data-live-target="#' + CSS.escape(targetEl.id) + '"]');
                if (!form) return;
                // Hanya intercept link yang masih di path yang sama (pagination/sort),
                // bukan link navigasi ke halaman lain seperti Edit/Detail.
                let linkUrl, formUrlObj;
                try {
                    linkUrl = new URL(link.href, window.location.href);
                    formUrlObj = new URL(form.action || window.location.href, window.location.href);
                } catch (_) { return; }
                if (linkUrl.origin !== formUrlObj.origin) return;
                if (linkUrl.pathname !== formUrlObj.pathname) return;
                e.preventDefault();
                ajaxSwap(link.href, targetEl, form.querySelector('[data-live-search]'));
            });

            // Back/forward → reload target sesuai URL saat ini
            window.addEventListener('popstate', function () {
                document.querySelectorAll('form[data-live-target]').forEach(function (form) {
                    const targetEl = document.querySelector(form.getAttribute('data-live-target'));
                    if (!targetEl) return;
                    // Sync nilai form dari URL params
                    const params = new URL(window.location.href).searchParams;
                    form.querySelectorAll('input[name], select[name]').forEach(function (el) {
                        if (el.type === 'hidden') return;
                        el.value = params.get(el.name) || '';
                    });
                    ajaxSwap(window.location.href, targetEl, form.querySelector('[data-live-search]'));
                });
            });
        })();
    </script>
    @stack('scripts')
    @stack('overlays')
</body>

</html>
