<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Verifikasi 2FA — {{ config('app.name', 'ZeroNet') }}</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <script src="{{ asset('assets/shell.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/app.css') }}" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    [x-cloak] { display: none !important; }
    body { min-height:100vh; display:grid; place-items:center; padding:24px;
      background: radial-gradient(circle at 70% 20%, color-mix(in srgb, var(--brand-1) 15%, var(--bg)), var(--bg) 60%); }
    .twofa-card { width:100%; max-width:440px; padding:36px 32px;
      background:var(--bg-elev); border:1px solid var(--border); border-radius:var(--r-xl);
      box-shadow:var(--shadow-lg); position:relative; }
    .twofa-theme { position:absolute; top:16px; right:16px; }
    .twofa-head { display:flex; align-items:center; gap:14px; margin-bottom:24px; }
    .twofa-mark { width:52px; height:52px; border-radius:14px; background:var(--brand-grad);
      display:grid; place-items:center; box-shadow:0 12px 24px -8px color-mix(in srgb, var(--brand-2) 50%, transparent); }
    .twofa-mark svg { width:24px; height:24px; color:white; }
    .twofa-head h2 { margin:0; font-size:20px; letter-spacing:-.01em; }
    .twofa-head p { margin:4px 0 0; color:var(--text-2); font-size:13px; line-height:1.5; }

    .otp-grid { display:flex; gap:8px; justify-content:center; margin:8px 0 4px; }
    .otp-grid .sep { width:14px; }
    .otp-input { width:48px; height:60px; text-align:center; font-family:'JetBrains Mono',monospace;
      font-size:24px; font-weight:600; border:1.5px solid var(--border); border-radius:var(--r-md);
      background:var(--bg-mute); color:var(--text); transition:all .15s ease; }
    .otp-input:focus { outline:none; border-color:var(--brand-3); background:var(--bg-elev); box-shadow:var(--ring); }
    .otp-input.filled { background:var(--bg-elev); border-color:var(--brand-3); color:var(--brand-3); }

    .otp-help { display:flex; align-items:center; justify-content:space-between; margin:18px 0 22px; font-size:12.5px; color:var(--text-3); }
    .otp-help .timer { display:inline-flex; align-items:center; gap:6px; color:var(--text-2); font-family:'JetBrains Mono',monospace; }
    .otp-help .timer::before { content:""; width:6px; height:6px; border-radius:50%; background:var(--ok); animation:otpPulse 1.4s ease-in-out infinite; }
    @keyframes otpPulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }

    .twofa-actions { display:flex; flex-direction:column; gap:10px; }
    .twofa-actions .submit { width:100%; padding:13px; justify-content:center; font-size:14.5px; font-weight:600; }
    .twofa-actions .ghost { background:transparent; color:var(--text-2); padding:10px; font-size:13px; border:0; cursor:pointer; }
    .twofa-actions .ghost:hover { color:var(--brand-3); }
    .twofa-actions .danger-link { color:var(--text-3); font-size:12.5px; }

    .twofa-foot { text-align:center; color:var(--text-3); font-size:11.5px; margin-top:18px; }
    .login-alert { padding:10px 12px; border-radius: var(--r-md); border:1px solid transparent; font-size:13px; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
    .login-alert.err { background: color-mix(in srgb, var(--err) 10%, transparent); color: var(--err); border-color: color-mix(in srgb, var(--err) 30%, transparent); }
    .recovery-input { font-family: 'JetBrains Mono', monospace; }
  </style>
</head>
<body>
  <div class="twofa-card" x-data="{ mode: 'totp' }">
    <button class="icon-btn twofa-theme" data-theme-toggle aria-label="Toggle tema" type="button"><span data-theme-icon style="display:contents"></span></button>

    <div class="twofa-head">
      <div class="twofa-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div>
        <h2>Verifikasi 2FA</h2>
        <p>Masukkan kode 6 digit dari aplikasi authenticator Anda.</p>
      </div>
    </div>

    @if ($errors->any())
      <div class="login-alert err">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form id="otp-form" method="POST" action="{{ route('two-factor.verify') }}">
      @csrf

      {{-- TOTP mode (split 3+3 grid) --}}
      <div x-show="mode === 'totp'">
        <div class="otp-grid" id="otp">
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autofocus />
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" />
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" />
          <span class="sep"></span>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" />
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" />
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" />
        </div>
        <input type="hidden" name="code" id="code-hidden" :disabled="mode !== 'totp'">
        <div class="otp-help">
          <span>Kode kedaluwarsa otomatis</span>
          <span class="timer" id="timer">00:30</span>
        </div>
      </div>

      {{-- Recovery mode --}}
      <div x-show="mode === 'recovery'" x-cloak class="field" style="margin-bottom: 18px;">
        <label>Recovery Code</label>
        <input id="recovery_code" name="recovery_code" type="text" autocomplete="off"
               placeholder="xxxxx-xxxxx"
               class="input recovery-input"
               x-bind:disabled="mode !== 'recovery'" />
      </div>

      <div class="twofa-actions">
        <button type="submit" class="btn btn-primary submit">Verifikasi</button>
        <button type="button" class="ghost" x-show="mode === 'totp'" @click="mode = 'recovery'">Gunakan recovery code</button>
        <button type="button" class="ghost" x-show="mode === 'recovery'" x-cloak @click="mode = 'totp'">Kembali ke kode authenticator</button>
      </div>
    </form>

    <form method="POST" action="{{ route('two-factor.cancel') }}" style="text-align:center;margin-top:14px">
      @csrf
      <button type="submit" class="ghost danger-link" style="background:none;border:0;cursor:pointer;color:var(--text-3);font-size:12.5px">Batalkan login</button>
    </form>

    <div class="twofa-foot">ZeroNet © {{ date('Y') }}</div>
  </div>

  <script>
    // OTP auto-advance + paste handling
    const inputs = document.querySelectorAll('#otp .otp-input');
    const hiddenCode = document.getElementById('code-hidden');
    function syncCode() { hiddenCode.value = [...inputs].map(i => i.value).join(''); }
    inputs.forEach((inp, i) => {
      inp.addEventListener('input', (e) => {
        const v = e.target.value.replace(/\D/g,'');
        e.target.value = v.slice(0,1);
        e.target.classList.toggle('filled', !!v);
        syncCode();
        if (v && i < inputs.length - 1) inputs[i+1].focus();
      });
      inp.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && i > 0) inputs[i-1].focus();
      });
      inp.addEventListener('paste', (e) => {
        e.preventDefault();
        const txt = (e.clipboardData.getData('text') || '').replace(/\D/g,'').slice(0, inputs.length);
        [...txt].forEach((d, idx) => { if (inputs[idx]) { inputs[idx].value = d; inputs[idx].classList.add('filled'); }});
        const next = Math.min(txt.length, inputs.length-1);
        inputs[next].focus();
        syncCode();
      });
    });
    // Countdown timer
    let secs = 30;
    setInterval(() => {
      secs = secs > 0 ? secs - 1 : 30;
      const m = String(Math.floor(secs/60)).padStart(2,'0');
      const s = String(secs%60).padStart(2,'0');
      document.getElementById('timer').textContent = `${m}:${s}`;
    }, 1000);
    // Theme toggle
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => btn.addEventListener('click', () => {
      const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
      window.ZeroNet.setPref('theme', next); updateThemeIcon();
    }));
    function updateThemeIcon(){
      const dark = document.documentElement.dataset.theme === 'dark';
      document.querySelectorAll('[data-theme-icon]').forEach(el => {
        el.innerHTML = dark
          ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>'
          : '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
      });
    }
    updateThemeIcon();
  </script>
</body>
</html>
