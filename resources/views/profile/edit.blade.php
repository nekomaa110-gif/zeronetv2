@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')

  <header class="page-head">
    <div>
      <h2>Profil Saya</h2>
      <p>Kelola informasi akun dan keamanan login Anda.</p>
    </div>
  </header>

  @php
    $lastLoginIp = optional(
      \App\Models\ActivityLog::where('user_id', $user->id)->where('action', 'login')->latest()->first()
    )->ip_address;
    $userInitial = strtoupper(substr($user->name ?? $user->username, 0, 1));
  @endphp

  {{-- ── Section 1: Informasi Akun + Ubah Password ─────────────────────── --}}
  <section class="profile-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px">

    {{-- Info akun --}}
    <div class="card">
      <div class="card-head">
        <div class="avatar" style="width:36px;height:36px">{{ $userInitial }}</div>
        <div>
          <h3>Informasi Akun</h3>
          <p style="margin:0;color:var(--text-2);font-size:12.5px">Nama dan username yang tampil di panel.</p>
        </div>
      </div>

      @if(session('success_info'))
        <div class="card-pad" style="padding-bottom:0">
          <x-admin.alert type="success" :message="session('success_info')"/>
        </div>
      @endif

      <form method="POST" action="{{ route('profile.update-info') }}" class="card-pad" style="display:flex;flex-direction:column;gap:14px;">
        @csrf @method('PATCH')

        <div class="field">
          <label>Nama Lengkap <span class="req">*</span></label>
          <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                 placeholder="Nama lengkap Anda" class="input">
          @error('name') <small style="color:var(--err);font-size:12px">{{ $message }}</small> @enderror
        </div>

        <div class="field">
          <label>Username <span class="req">*</span></label>
          <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                 placeholder="username" class="input mono">
          @error('username') <small style="color:var(--err);font-size:12px">{{ $message }}</small> @enderror
        </div>

        <div class="field">
          <label>Role</label>
          <input class="input" value="{{ ucfirst($user->role) }}" disabled style="opacity:.7" />
        </div>

        <div style="display:flex;justify-content:flex-start">
          <button type="submit" class="btn btn-primary">Simpan Informasi</button>
        </div>
      </form>
    </div>

    {{-- Ubah Password --}}
    <div class="card">
      <div class="card-head">
        <div style="width:36px;height:36px;border-radius:10px;background:color-mix(in srgb,var(--brand-3) 14%,transparent);color:var(--brand-3);display:grid;place-items:center;flex-shrink:0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div>
          <h3>Ubah Password</h3>
          <p style="margin:0;color:var(--text-2);font-size:12.5px">Gunakan password yang kuat dan unik.</p>
        </div>
      </div>

      @if(session('success_password'))
        <div class="card-pad" style="padding-bottom:0">
          <x-admin.alert type="success" :message="session('success_password')"/>
        </div>
      @endif

      <form method="POST" action="{{ route('profile.update-password') }}" class="card-pad" style="display:flex;flex-direction:column;gap:14px;">
        @csrf @method('PATCH')

        <div class="field">
          <label>Password Saat Ini <span class="req">*</span></label>
          <input type="password" name="current_password" id="current_password" class="input" placeholder="••••••••">
          @error('current_password') <small style="color:var(--err);font-size:12px">{{ $message }}</small> @enderror
        </div>

        <div class="field">
          <label>Password Baru <span class="req">*</span></label>
          <input type="password" name="password" id="password" class="input" placeholder="••••••••">
          @error('password') <small style="color:var(--err);font-size:12px">{{ $message }}</small> @enderror
          <small style="color:var(--text-3);font-size:11.5px">Minimal 8 karakter.</small>
        </div>

        <div class="field">
          <label>Konfirmasi Password Baru <span class="req">*</span></label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="input" placeholder="••••••••">
        </div>

        <div style="display:flex;justify-content:flex-start">
          <button type="submit" class="btn btn-primary">Ubah Password</button>
        </div>
      </form>
    </div>
  </section>

  {{-- ── Section 2: Info Sesi + Tips Keamanan ─────────────────────────── --}}
  <section class="profile-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px">

    {{-- Info Sesi --}}
    <div class="card">
      <div class="card-head">
        <h3>Info Sesi</h3>
        <p style="margin:0;color:var(--text-2);font-size:12.5px">Detail waktu akses akun ini.</p>
      </div>
      <div class="card-pad">
        <div class="kvp">
          <span class="k">Login Terakhir</span>
          <span class="v">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}</span>
        </div>
        <div class="kvp">
          <span class="k">Akun Dibuat</span>
          <span class="v mono">{{ $user->created_at->format('d M Y') }}</span>
        </div>
        <div class="kvp">
          <span class="k">Role</span>
          <span class="v">
            @if($user->role === 'admin')
              <span class="badge brand">Admin</span>
            @else
              <span class="badge info">Operator</span>
            @endif
          </span>
        </div>
        <div class="kvp">
          <span class="k">IP Login Terakhir</span>
          <span class="v mono">{{ $lastLoginIp ?: '—' }}</span>
        </div>
      </div>
    </div>

    {{-- Tips Keamanan --}}
    <div class="card card-pad" style="display:flex;flex-direction:column;justify-content:center;gap:12px">
      <h3 style="margin:0;font-size:14px">Tips Keamanan</h3>
      <ul style="margin:0;padding-left:18px;color:var(--text-2);font-size:13px;line-height:1.7">
        <li>- Aktifkan 2FA untuk lapisan keamanan tambahan</li>
        <li>- Jangan bagikan akses panel ke pihak yang tidak berwenang</li>
        <li>- Ganti password setiap 90 hari</li>
        <li>- Periksa log aktivitas secara berkala</li>
      </ul>
    </div>
  </section>

  {{-- ── Section 3: Two-Factor Authentication (full-width) ─────────────── --}}
  <div class="card">
    <div class="card-head">
      <div style="width:36px;height:36px;border-radius:10px;background:color-mix(in srgb,var(--ok) 14%,transparent);color:var(--ok);display:grid;place-items:center;flex-shrink:0">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <h3>Two-Factor Authentication (2FA)</h3>
        <p style="margin:0;color:var(--text-2);font-size:12.5px">Lindungi akun dengan kode dari aplikasi authenticator.</p>
      </div>
      <div class="ch-actions">
        @if ($user->hasTwoFactorEnabled())
          <span class="badge ok">Aktif</span>
        @else
          <span class="badge">Nonaktif</span>
        @endif
      </div>
    </div>

    @if(session('success_2fa'))
      <div class="card-pad" style="padding-bottom:0">
        <x-admin.alert type="success" :message="session('success_2fa')"/>
      </div>
    @endif

    <div class="card-pad">
      @if ($user->hasTwoFactorEnabled())
        <p style="margin:0 0 16px;color:var(--text-2);font-size:13.5px">
          2FA aktif sejak <b class="mono">{{ $user->two_factor_confirmed_at->translatedFormat('d M Y H:i') }}</b>.
          Setiap login akan meminta kode 6 digit dari authenticator Anda.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="twofa-pair">
          {{-- Regenerate Recovery Codes --}}
          <form method="POST" action="{{ route('two-factor.regenerate-codes') }}"
                class="card card-pad" style="background:var(--bg-mute);border:1px solid var(--border)">
            @csrf
            <h4 style="margin:0 0 6px;font-size:13px">Regenerate Recovery Codes</h4>
            <p style="margin:0 0 12px;color:var(--text-2);font-size:12.5px">Code lama akan tidak berlaku.</p>
            <input type="password" name="current_password" required placeholder="Password saat ini" class="input" style="margin-bottom:10px">
            @error('current_password') <small style="color:var(--err);font-size:12px;display:block;margin-top:-6px;margin-bottom:8px">{{ $message }}</small> @enderror
            <button type="submit" class="btn btn-primary" style="width:100%">Regenerate</button>
          </form>

          {{-- Nonaktifkan 2FA --}}
          <form method="POST" action="{{ route('two-factor.disable') }}"
                onsubmit="return confirm('Yakin nonaktifkan 2FA? Akun jadi kurang aman.');"
                class="card card-pad" style="background:color-mix(in srgb,var(--err) 6%,transparent);border:1px solid color-mix(in srgb,var(--err) 30%,var(--border))">
            @csrf @method('DELETE')
            <h4 style="margin:0 0 6px;font-size:13px;color:var(--err)">Nonaktifkan 2FA</h4>
            <p style="margin:0 0 12px;color:var(--text-2);font-size:12.5px">Hapus 2FA — login hanya menggunakan username dan password.</p>
            <input type="password" name="current_password" required placeholder="Password saat ini" class="input" style="margin-bottom:10px">
            <button type="submit" class="btn btn-danger" style="width:100%">Nonaktifkan</button>
          </form>
        </div>
      @else
        <p style="margin:0 0 14px;color:var(--text-2);font-size:13px">
          Saat ini akun Anda hanya dilindungi password. Aktifkan 2FA agar login juga butuh kode dari aplikasi authenticator.
        </p>
        <a href="{{ route('two-factor.setup') }}" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Aktifkan 2FA
        </a>
      @endif
    </div>
  </div>

  <style>
    @media (max-width: 880px) {
      .profile-row { grid-template-columns: 1fr !important; }
      .twofa-pair { grid-template-columns: 1fr !important; }
    }
  </style>

@endsection
