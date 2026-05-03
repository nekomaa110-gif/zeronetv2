@extends('layouts.app')

@section('title', 'WhatsApp Gateway')
@section('page-title', 'WhatsApp Gateway')

@php
    $st = $status['status'] ?? 'unknown';
    $statusTone = match($st) {
        'open'             => 'ok',
        'qr', 'connecting' => 'warn',
        default            => 'err',
    };
@endphp

@section('content')

  <header class="page-head">
    <div>
      <h2>WhatsApp Gateway</h2>
      <p>Kirim pesan WhatsApp dan kelola kontak pelanggan untuk reminder otomatis.</p>
    </div>
    <div class="head-actions">
      <a href="/wa-admin" target="_blank" class="btn">
        Buka panel QR
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </header>

  <div style="display:flex;flex-direction:column;gap:16px;">

    @php
      $bannerTone = $statusTone;  // ok | warn | err
      $bannerColor = $bannerTone === 'ok' ? 'ok' : ($bannerTone === 'warn' ? 'warn' : 'err');
      $phoneNumber = $status['number'] ?? $status['phone'] ?? null;
      $weeklyCount = $status['weekly_count'] ?? null;
    @endphp
    <div class="card card-pad" style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg, color-mix(in srgb, var({{ '--' . $bannerColor }}) 8%, transparent), color-mix(in srgb, var({{ '--' . $bannerColor }}) 0%, transparent));border-color:color-mix(in srgb, var({{ '--' . $bannerColor }}) 30%, var(--border))">
      <div style="width:40px;height:40px;border-radius:10px;background:color-mix(in srgb,var({{ '--' . $bannerColor }}) 18%,transparent);color:var({{ '--' . $bannerColor }});display:grid;place-items:center;flex-shrink:0">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
      </div>
      <div style="flex:1">
        <b>Status: {{ strtoupper($st) }}</b>
        <div style="color:var(--text-2);font-size:12.5px">
          @if($phoneNumber)Terhubung sebagai <span class="mono">{{ $phoneNumber }}</span>@else WhatsApp Gateway @endif
          @if($weeklyCount) · {{ $weeklyCount }} pesan minggu ini @endif
        </div>
      </div>
      <span class="badge {{ $statusTone }}">{{ strtoupper($st) === 'OPEN' ? 'Connected' : ucfirst($st) }}</span>
    </div>

    @if(session('ok'))
      <div class="card card-pad" style="background: color-mix(in srgb, var(--ok) 8%, var(--bg-elev)); border-color: color-mix(in srgb, var(--ok) 30%, transparent); color: var(--ok); font-size:13px;">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
      <div class="card card-pad" style="background: color-mix(in srgb, var(--err) 8%, var(--bg-elev)); border-color: color-mix(in srgb, var(--err) 30%, transparent); color: var(--err); font-size:13px;">
        <ul style="margin:0;padding-left:18px">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(360px,1fr));gap:16px;">

      {{-- Manual send --}}
      <div class="card">
        <div class="card-head"><h3>Kirim Manual</h3></div>
        <form method="post" action="{{ route('whatsapp.send') }}" class="card-pad" style="display:flex;flex-direction:column;gap:14px;">
          @csrf
          <div class="field">
            <label>Nomor tujuan</label>
            <input name="number" required placeholder="08xxxxxxxxxx" class="input">
          </div>
          <div class="field">
            <label>Pesan</label>
            <textarea name="message" rows="5" required placeholder="Isi pesan..." class="textarea"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="align-self:flex-start">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim
          </button>
        </form>
      </div>

      {{-- Tambah kontak --}}
      <div class="card">
        <div class="card-head"><h3>Tambah Kontak Pelanggan</h3></div>
        <form method="post" action="{{ route('whatsapp.contacts.store') }}" class="card-pad" style="display:flex;flex-direction:column;gap:14px;">
          @csrf
          <div class="field">
            <label>Username</label>
            <input name="username" required placeholder="contoh: admin" class="input">
          </div>
          <div class="field">
            <label>Nomor WhatsApp</label>
            <input name="phone" required placeholder="08xxxxxxxxxx" class="input">
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="field">
              <label>Nama <span style="color:var(--text-3)">(opsional)</span></label>
              <input name="name" placeholder="Nama pelanggan" class="input">
            </div>
            <div class="field">
              <label>Catatan <span style="color:var(--text-3)">(opsional)</span></label>
              <input name="notes" placeholder="catatan internal" class="input">
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="align-self:flex-start">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan Kontak
          </button>
        </form>
      </div>
    </div>

    {{-- List kontak --}}
    <div class="card">
      <div class="card-head">
        <h3>Daftar Kontak</h3>
        <div class="ch-actions">
          <form method="get" style="display:flex;gap:8px">
            <input name="q" value="{{ $q }}" placeholder="username, phone, nama" class="input" style="width:240px">
            <button class="btn btn-sm">Cari</button>
          </form>
        </div>
      </div>

      @foreach($contacts as $c)
        <form id="upd-{{ $c->id }}" method="post" action="{{ route('whatsapp.contacts.update', $c) }}">@csrf @method('PATCH')</form>
        <form id="del-{{ $c->id }}" method="post" action="{{ route('whatsapp.contacts.destroy', $c) }}"
              onsubmit="return confirm('Hapus kontak {{ $c->username }}?');">@csrf @method('DELETE')</form>
      @endforeach

      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Username</th>
              <th>Nama</th>
              <th>Phone</th>
              <th>Notes</th>
              <th>Dikirim</th>
              <th style="text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($contacts as $c)
              <tr>
                <td class="mono" style="font-size:12px">{{ $c->username }}</td>
                <td><input form="upd-{{ $c->id }}" name="name"  value="{{ $c->name }}"  class="input" style="min-width:140px;padding:6px 8px"></td>
                <td><input form="upd-{{ $c->id }}" name="phone" value="{{ $c->phone }}" required class="input" style="min-width:140px;padding:6px 8px"></td>
                <td><input form="upd-{{ $c->id }}" name="notes" value="{{ $c->notes }}" class="input" style="min-width:160px;padding:6px 8px"></td>
                <td style="font-size:12px;color:var(--text-3);white-space:nowrap">
                  {{ $c->reminder_sent_at?->format('d M Y H:i') ?? '—' }}
                </td>
                <td>
                  <div style="display:flex;gap:6px;justify-content:flex-end">
                    <button form="upd-{{ $c->id }}" type="submit" class="btn btn-sm btn-primary">Update</button>
                    <button form="del-{{ $c->id }}" type="submit" class="btn btn-sm btn-danger">Hapus</button>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" style="padding:48px 0;text-align:center;color:var(--text-3)">Belum ada kontak.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div style="padding: 12px var(--pad-card); border-top:1px solid var(--border)">{{ $contacts->links() }}</div>
    </div>
  </div>
@endsection
