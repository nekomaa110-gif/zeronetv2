@extends('layouts.app')

@section('title', 'Aktifkan 2FA')
@section('page-title', 'Aktifkan 2FA')

@section('content')

  <header class="page-head">
    <div>
      <h2>Aktifkan Two-Factor Authentication</h2>
      <p>Tambahkan lapisan keamanan ekstra dengan kode dari aplikasi authenticator.</p>
    </div>
    <div class="head-actions">
      <a href="{{ route('profile.edit') }}" class="btn">← Kembali</a>
    </div>
  </header>

  <div style="max-width: 760px;">
    <div class="card">
      <div class="card-head">
        <h3>Langkah-langkah</h3>
      </div>
      <div class="card-pad" style="padding-bottom: 12px;">
        <ol style="margin:0;padding-left:18px;display:flex;flex-direction:column;gap:6px;font-size:12.5px;color:var(--text-2)">
          <li>Install aplikasi authenticator (Google Authenticator, Authy, Microsoft Authenticator).</li>
          <li>Scan QR code di bawah, atau masukkan secret key secara manual.</li>
          <li>Masukkan kode 6 digit yang muncul di aplikasi untuk konfirmasi.</li>
        </ol>
      </div>

      <div class="card-pad" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;align-items:start;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
          <div style="padding:12px;background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);">
            {!! $qrSvg !!}
          </div>
          <p style="font-size:11.5px;color:var(--text-3);text-align:center;margin:0">Scan dengan aplikasi authenticator</p>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px;">
          <div class="field">
            <label>Secret Key (input manual)</label>
            <div style="display:flex;align-items:stretch;gap:8px">
              <input type="text" readonly value="{{ $secret }}" class="input mono" style="background:var(--bg-mute);flex:1">
              <button type="button"
                      onclick="navigator.clipboard.writeText('{{ $secret }}'); this.innerText='Tersalin'; setTimeout(() => this.innerText='Salin', 1500);"
                      class="btn btn-sm">Salin</button>
            </div>
          </div>

          @if ($errors->any())
            <x-admin.alert type="error" :message="$errors->first()"/>
          @endif

          <form method="POST" action="{{ route('two-factor.confirm') }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            <div class="field">
              <label>Kode dari Authenticator <span class="req">*</span></label>
              <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*"
                     maxlength="6" autocomplete="one-time-code" required autofocus
                     placeholder="123456"
                     class="input mono" style="text-align:center;font-size:18px;letter-spacing:8px">
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <button type="submit" class="btn btn-primary">Konfirmasi & Aktifkan</button>
              <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection
