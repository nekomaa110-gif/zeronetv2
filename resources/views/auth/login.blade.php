<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login — {{ config('app.name', 'ZeroNet') }}</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <script src="{{ asset('assets/shell.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/app.css') }}" />

  <style>
    body { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; }
    @media (max-width: 880px) { body { grid-template-columns: 1fr; } .login-hero { display: none; } }
    .login-hero {
      position: relative; overflow: hidden;
      background: radial-gradient(circle at 30% 30%, color-mix(in srgb, var(--brand-1) 30%, var(--bg)), var(--bg) 60%),
                  linear-gradient(135deg, color-mix(in srgb, var(--brand-2) 18%, var(--bg)), var(--bg));
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 40px;
    }
    [data-theme="dark"] .login-hero {
      background: radial-gradient(circle at 30% 30%, color-mix(in srgb, var(--brand-1) 22%, #050810), #050810 60%),
                  linear-gradient(135deg, color-mix(in srgb, var(--brand-2) 14%, #050810), #050810);
    }
    .login-hero::before {
      content: ""; position: absolute; inset: 0;
      background-image:
        linear-gradient(to right, color-mix(in srgb, var(--text-3) 15%, transparent) 1px, transparent 1px),
        linear-gradient(to bottom, color-mix(in srgb, var(--text-3) 15%, transparent) 1px, transparent 1px);
      background-size: 40px 40px;
      mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
      -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
      pointer-events: none;
    }
    .login-mark {
      width: 90px; height: 90px; border-radius: 24px;
      background: var(--brand-grad);
      display: grid; place-items: center;
      box-shadow: 0 24px 48px -12px color-mix(in srgb, var(--brand-2) 50%, transparent);
      margin-bottom: 24px; position: relative; z-index: 1;
      animation: float 6s ease-in-out infinite;
    }
    @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    .login-mark svg { width: 46px; height: 46px; color: white; }
    .login-hero h1 { margin: 0; font-size: 38px; font-weight: 700; letter-spacing: -.02em; position: relative; z-index: 1;
      background: linear-gradient(135deg, var(--text), var(--brand-3)); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .login-hero p { margin: 8px 0 0; color: var(--text-2); font-size: 15px; position: relative; z-index: 1; }
    .login-hero .pills { display: flex; gap: 8px; margin-top: 28px; flex-wrap: wrap; justify-content: center; position: relative; z-index: 1; }

    .login-form { padding: 40px; display: flex; flex-direction: column; justify-content: center; position: relative; }
    .login-theme { position: absolute; top: 24px; right: 24px; }
    .login-card { width: 100%; max-width: 380px; margin: 0 auto; }
    .login-card h2 { margin: 0 0 6px; font-size: 28px; font-weight: 700; letter-spacing: -.02em; }
    .login-card .lead { margin: 0 0 28px; color: var(--text-2); }
    .login-card .field { margin-bottom: 16px; }
    .login-card .pw { position: relative; }
    .login-card .pw .eye { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: 0; color: var(--text-3); padding: 4px; cursor: pointer; }
    .login-card .row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; font-size: 13px; }
    .login-card .row a { color: var(--brand-3); font-weight: 600; }
    .login-card .row label { display: inline-flex; align-items: center; gap: 8px; color: var(--text-2); cursor: pointer; }
    .login-card .submit { width: 100%; justify-content: center; padding: 12px; font-size: 14px; }
    .login-foot { text-align: center; color: var(--text-3); font-size: 12px; margin-top: 28px; }
    .login-alert { padding:10px 12px; border-radius: var(--r-md); border:1px solid transparent; font-size:13px; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
    .login-alert.err { background: color-mix(in srgb, var(--err) 10%, transparent); color: var(--err); border-color: color-mix(in srgb, var(--err) 30%, transparent); }
    .login-alert.ok { background: color-mix(in srgb, var(--ok) 10%, transparent); color: var(--ok); border-color: color-mix(in srgb, var(--ok) 30%, transparent); }
  </style>
</head>
<body>
  <div class="login-hero">
    <div class="login-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14 0"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M8.5 16.43a6 6 0 0 1 7 0"/><circle cx="12" cy="20" r="1.4" fill="currentColor"/></svg>
    </div>
    <h1>ZeroNet</h1>
    <p>Panel Manajemen Hotspot</p>
    <div class="pills">
      <span class="badge ok">Live monitoring</span>
      <span class="badge info">MikroTik integration</span>
      <span class="badge brand">WhatsApp Gateway</span>
    </div>
  </div>

  <div class="login-form">
    <button class="icon-btn login-theme" data-theme-toggle aria-label="Toggle tema" type="button"><span data-theme-icon style="display:contents"></span></button>
    <div class="login-card">
      <h2>Selamat datang</h2>
      <p class="lead">Masuk untuk melanjutkan ke panel ZeroNet</p>

      @if ($errors->any())
        <div class="login-alert err">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      @if (session('status'))
        <div class="login-alert ok">{{ session('status') }}</div>
      @endif

      <form id="login-form" method="POST" action="{{ route('login') }}" autocomplete="off">
        @csrf

        <div class="field">
          <label>Username</label>
          <div class="input-group">
            <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input class="input" id="login" name="login" type="text"
                   value="{{ old('login') }}" required autofocus
                   autocomplete="off" placeholder="Masukkan username anda" />
          </div>
        </div>

        <div class="field">
          <label>Password</label>
          <div class="input-group pw">
            <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input class="input" id="password" name="password" type="password"
                   required autocomplete="new-password" placeholder="Masukkan password anda" />
            <button type="button" class="eye" id="pw-toggle" tabindex="-1" aria-label="Tampilkan password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <div class="row">
          <label><input id="remember" type="checkbox" name="remember" /> Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-primary submit">Masuk ke Panel</button>
      </form>

      <div class="login-foot">ZeroNet © {{ date('Y') }} · v2.0</div>
    </div>
  </div>

  <script>
    // Password toggle
    document.getElementById('pw-toggle').addEventListener('click', function () {
      var i = document.getElementById('password');
      i.type = i.type === 'password' ? 'text' : 'password';
    });

    // Theme toggle wiring (shell.js sudah expose ZeroNet)
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        window.ZeroNet.setPref('theme', next);
        updateThemeIcon();
      });
    });
    function updateThemeIcon() {
      var isDark = document.documentElement.dataset.theme === 'dark';
      document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
        el.innerHTML = isDark
          ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>'
          : '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
      });
    }
    updateThemeIcon();

    // Remember username (bukan password) — dipertahankan dari panel lama
    (function () {
      var STORAGE_KEY = 'zeronet_remember_login';
      var loginInput  = document.getElementById('login');
      var rememberBox = document.getElementById('remember');
      var form        = document.getElementById('login-form');

      var saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        if (!loginInput.value) loginInput.value = saved;
        rememberBox.checked = true;
      }

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
