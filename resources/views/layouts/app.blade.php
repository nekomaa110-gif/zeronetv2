<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — {{ config('app.name', 'ZeroNet') }}</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  {{-- Apply theme/density/etc as early as possible (no flash) --}}
  <script src="{{ asset('assets/shell.js') }}"></script>

  {{-- Vite dimuat duluan: Tailwind base/preflight + @tailwindcss/forms + Flowbite + Alpine.
       Custom design-system kita muncul SETELAHNYA agar specificity tidak ditimpa
       (Tailwind forms pakai selector `[type='text']` yang specificity-nya sama
       dengan class `.input`, sehingga source-order menentukan pemenang). --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Global stylesheet revamp — DIMUAT TERAKHIR supaya berani menimpa Tailwind forms --}}
  <link rel="stylesheet" href="{{ asset('assets/app.css') }}" />

  <style>
    [x-cloak] { display: none !important; }
    .no-transition, .no-transition * { transition: none !important; }
  </style>

  @stack('head')
</head>
<body>
  <div class="shell">
    @include('partials.sidebar', ['active' => $active ?? ''])

    <div class="main">
      @include('partials.topbar', ['title' => $title ?? View::yieldContent('page-title') ?: 'Dashboard'])

      <main class="page" data-screen-label="@yield('title')">
        @if (session('success'))
          <div class="badge ok" style="margin-bottom:14px;display:inline-flex">{{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="badge err" style="margin-bottom:14px;display:inline-flex">{{ session('error') }}</div>
        @endif
        @if (session('forbidden'))
          <div class="badge warn" style="margin-bottom:14px;display:inline-flex">{{ session('forbidden') }}</div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>

  {{-- Tweaks panel (UI preferences) --}}
  <script src="{{ asset('assets/tweaks.js') }}"></script>
  <script>window.mountTweaks && window.mountTweaks();</script>

  {{-- Auto-refresh helper (kompatibel dengan x-data="pageAutoRefresh(...)") --}}
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
  </script>

  {{-- Live search: debounced auto-submit + AJAX swap target HTML.
       Identik dengan implementasi sebelumnya — TIDAK ada perubahan logika. --}}
  <script>
    (function () {
        const FOCUS_KEY = '__live_search_focus__';
        const SPINNER_SVG = '<svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="12" cy="12" r="10" opacity=".25"/><path d="M22 12a10 10 0 0 0-10-10" /></svg>';

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

        function ensureSpinner(input) {
            const wrap = input.parentElement;
            if (!wrap) return null;
            let sp = wrap.querySelector('[data-live-spinner]');
            if (!sp) {
                sp = document.createElement('span');
                sp.setAttribute('data-live-spinner', '');
                sp.className = 'live-spinner';
                sp.innerHTML = SPINNER_SVG;
                wrap.appendChild(sp);
            }
            return sp;
        }
        function showSpinner(input) {
            const sp = ensureSpinner(input);
            if (sp) sp.classList.add('on');
            input.parentElement?.querySelectorAll('[data-live-clear], [data-live-reset]')
                .forEach(b => b.classList.add('hidden'));
        }
        function hideSpinner(input) {
            if (!input) return;
            const sp = input.parentElement?.querySelector('[data-live-spinner]');
            if (sp) sp.classList.remove('on');
            syncClearBtn(input);
            if (input.form) syncResetBtn(input.form);
        }

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
                if (sourceInput) showSpinner(sourceInput);
                if (sourceInput?.name) sessionStorage.setItem(FOCUS_KEY, sourceInput.name);
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
        }

        function syncClearBtn(input) {
            const btn = input.parentElement?.querySelector('[data-live-clear]');
            if (btn) btn.classList.toggle('hidden', !input.value);
        }
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
        function syncAllResetBtns() { document.querySelectorAll('form').forEach(syncResetBtn); }

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
                if (input.form) showSpinner(input);
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

        document.querySelectorAll('[data-live-submit]').forEach(function (el) {
            el.addEventListener('change', function () {
                if (el.form) {
                    syncResetBtn(el.form);
                    triggerSubmit(el.form, el.form.querySelector('[data-live-search]'));
                }
            });
        });

        syncAllResetBtns();

        document.querySelectorAll('form[data-live-target]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                triggerSubmit(form, form.querySelector('[data-live-search]'));
            });
        });

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            const targetEl = link.closest('[data-live-results]');
            if (!targetEl) return;
            if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;
            const form = document.querySelector('form[data-live-target="#' + CSS.escape(targetEl.id) + '"]');
            if (!form) return;
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

        window.addEventListener('popstate', function () {
            document.querySelectorAll('form[data-live-target]').forEach(function (form) {
                const targetEl = document.querySelector(form.getAttribute('data-live-target'));
                if (!targetEl) return;
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

  <style>
    .live-spinner { position:absolute; right:10px; top:50%; transform:translateY(-50%); display:none; color:var(--text-3); }
    .live-spinner.on { display:inline-flex; }
    .live-spinner svg { animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .hidden { display: none !important; }
  </style>

  @stack('scripts')
  @stack('overlays')
</body>
</html>
