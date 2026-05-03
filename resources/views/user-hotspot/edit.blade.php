@extends('layouts.app')

@section('title', 'Edit User: ' . $user['username'])
@section('page-title', 'Edit User Hotspot')

@section('content')

  <header class="page-head">
    <div>
      <h2>Edit User: <span class="mono">{{ $user['username'] }}</span></h2>
      <p>Ubah paket, expire, atau password.</p>
    </div>
    <div class="head-actions">
      <a href="{{ route('user-hotspot.index') }}" class="btn">← Kembali</a>
    </div>
  </header>

  <div style="max-width: 640px;">
    <div class="card">
      <div class="card-head">
        <h3>Detail User</h3>
      </div>

      <form method="POST" action="{{ route('user-hotspot.update', $user['username']) }}"
            x-data="{
              showCurrent: false, changePass: false, showNew: false,
              cancelChange() { this.changePass = false; this.showNew = false; var f = document.getElementById('password'); if (f) f.value = ''; }
            }"
            class="card-pad">
        @csrf @method('PUT')

        <div class="field" style="margin-bottom:14px;">
          <label>Username</label>
          <input type="text" value="{{ $user['username'] }}" readonly tabindex="-1"
                 class="input mono" style="background:var(--bg-mute);color:var(--text-2);cursor:not-allowed" />
          <input type="hidden" name="username" value="{{ $user['username'] }}">
        </div>

        <div class="field" style="margin-bottom:14px;">
          <label>Password Saat Ini</label>
          <div style="position:relative">
            <input :type="showCurrent ? 'text' : 'password'" value="{{ $user['password'] }}" readonly tabindex="-1"
                   class="input mono" style="background:var(--bg-mute);padding-right:40px" />
            <button type="button" @click="showCurrent = !showCurrent" tabindex="-1"
                    class="icon-btn"
                    style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;border:0;background:transparent">
              <svg x-show="!showCurrent" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg x-show="showCurrent" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <div style="font-size:12px;color:var(--text-3);margin-top:4px">Klik ikon mata untuk melihat password aktif user.</div>
        </div>

        {{-- Ganti Password toggle --}}
        <div style="border:1px solid var(--border);border-radius:var(--r-md);overflow:hidden;margin-bottom:14px;">
          <button type="button" @click="changePass ? cancelChange() : (changePass = true)"
                  style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:0;background:var(--bg-mute);cursor:pointer">
            <div style="display:flex;align-items:center;gap:8px">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   :style="changePass ? 'color:var(--brand-3)' : 'color:var(--text-3)'"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              <span style="font-weight:600;font-size:13px">Ganti Password</span>
              <span x-show="!changePass" style="font-size:12px;color:var(--text-3)">— password tidak akan diubah</span>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                 style="transition:transform .15s" :style="changePass ? 'transform:rotate(180deg)' : ''"><polyline points="6 9 12 15 18 9"/></svg>
          </button>

          <div x-show="changePass" x-cloak x-transition class="card-pad" style="border-top:1px solid var(--border);padding:14px;">
            <div class="field">
              <label>Password Baru</label>
              <div style="position:relative">
                <input :type="showNew ? 'text' : 'password'" id="password" name="password"
                       autocomplete="new-password" placeholder="Masukkan password baru..."
                       class="input" style="padding-right:40px" />
                <button type="button" @click="showNew = !showNew" tabindex="-1"
                        class="icon-btn"
                        style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;border:0;background:transparent">
                  <svg x-show="!showNew" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg x-show="showNew" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
              </div>
              @error('password') <div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
              <div style="font-size:12px;color:var(--text-3);margin-top:4px">Kosongkan untuk tidak mengubah password.</div>
            </div>
          </div>
        </div>

        <div class="field" style="margin-bottom:14px;">
          <label>Profil / Paket</label>
          <select id="group" name="group" class="select">
            <option value="">— Tidak ada —</option>
            @foreach($groups as $g)
              <option value="{{ $g }}" {{ (old('group', $user['group']) === $g) ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
          </select>
        </div>

        <div class="field" style="margin-bottom:18px;">
          <label>Tanggal Expire</label>
          <input type="date" id="expiry" name="expiry" value="{{ old('expiry', $user['expiry_input']) }}" class="input">
          @error('expiry') <div style="color:var(--err);font-size:12px;margin-top:4px">{{ $message }}</div> @enderror
          <div style="font-size:12px;color:var(--text-3);margin-top:4px">Kosongkan untuk menghapus expire. Waktu otomatis disimpan sebagai 23:59:59.</div>
        </div>

        <div style="display:flex;gap:10px;align-items:center;padding-top:12px;border-top:1px solid var(--border)">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('user-hotspot.index') }}" class="btn">Batal</a>
        </div>
      </form>
    </div>
  </div>

@endsection
