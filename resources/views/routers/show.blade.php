@extends('layouts.app')

@section('title', $routerName)
@section('page-title', $routerName)

@section('content')

  <header class="page-head" x-data="routerStats('{{ route('routers.stats', $routerId) }}')" x-init="load()">
    <div>
      <div style="display:flex;align-items:center;gap:8px;color:var(--text-3);font-size:12px;margin-bottom:6px">
        <a href="{{ route('routers.index') }}" style="color:var(--text-2)">Manajemen Router</a>
        <span>/</span>
        <span>{{ $routerName }}</span>
      </div>
      <h2>
        {{ $routerName }}
        <template x-if="loading"><span class="badge" style="font-size:12px;vertical-align:middle;margin-left:8px">Mengecek…</span></template>
        <template x-if="!loading && online"><span class="badge ok" style="font-size:12px;vertical-align:middle;margin-left:8px">Online</span></template>
        <template x-if="!loading && !online"><span class="badge err" style="font-size:12px;vertical-align:middle;margin-left:8px">Offline</span></template>
      </h2>
      <p class="mono">{{ $routerHost }} · ether1 (WAN) <span x-show="!loading && online && stats.identity">· <span x-text="stats.identity"></span></span></p>
    </div>
    <div class="head-actions">
      <a class="btn" href="{{ route('routers.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
      </a>
      @if((auth()->user()->role ?? null) === 'admin')
        <form method="POST" action="{{ route('routers.reboot', $routerId) }}"
              onsubmit="return confirm('Yakin reboot {{ $routerName }}? Semua koneksi aktif akan terputus sementara.')"
              style="display:inline-flex">
          @csrf
          <button type="submit" class="btn btn-warn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Reboot
          </button>
        </form>
        <a href="{{ route('routers.backup', $routerId) }}" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Backup Config
        </a>
      @endif
    </div>
  </header>

  {{-- Resource cards --}}
  <section x-data="routerStats('{{ route('routers.stats', $routerId) }}')" x-init="load()" x-cloak
           style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:22px">

    <template x-if="loading">
      <template x-for="i in 4" :key="i">
        <div class="res-card"><div class="skeleton" style="height:80px"></div></div>
      </template>
    </template>

    <template x-if="!loading && !online">
      <div class="card card-pad" style="grid-column:1/-1;border-color: color-mix(in srgb, var(--err) 30%, transparent); background: color-mix(in srgb, var(--err) 6%, var(--bg-elev));display:flex;align-items:center;gap:10px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--err);flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div style="flex:1">
          <div style="font-weight:600;color:var(--err)">Router tidak dapat dijangkau</div>
          <div style="font-size:12px;color:var(--text-3);margin-top:2px" x-text="error"></div>
        </div>
        <button @click="load()" class="btn btn-sm">Coba lagi</button>
      </div>
    </template>

    <template x-if="!loading && online">
      <div style="display:contents">
        <div class="res-card">
          <div class="rc-l">CPU Load</div>
          <div class="rc-v mono" :style="stats.cpu_load > 80 ? 'color:var(--err)' : stats.cpu_load > 50 ? 'color:var(--warn)' : 'color:var(--ok)'" x-text="stats.cpu_load + '%'"></div>
          <div class="progress" :class="stats.cpu_load > 80 ? 'err' : stats.cpu_load > 50 ? 'warn' : 'ok'"><i :style="'width:' + stats.cpu_load + '%'"></i></div>
          <div class="rc-s" x-text="(stats.cpu_count || '') + (stats.cpu_count ? ' cores · ' : '') + (stats.cpu || '')"></div>
        </div>

        <div class="res-card">
          <div class="rc-l">RAM</div>
          <div class="rc-v mono" :style="stats.mem_pct > 85 ? 'color:var(--err)' : stats.mem_pct > 60 ? 'color:var(--warn)' : 'color:var(--info)'" x-text="stats.mem_pct + '%'"></div>
          <div class="progress" :class="stats.mem_pct > 85 ? 'err' : stats.mem_pct > 60 ? 'warn' : ''"><i :style="'width:' + stats.mem_pct + '%'"></i></div>
          <div class="rc-s mono" x-text="fmtBytes(stats.used_mem) + ' / ' + fmtBytes(stats.total_mem)"></div>
        </div>

        <div class="res-card">
          <div class="rc-l">Storage</div>
          <div class="rc-v mono" :style="stats.hdd_pct > 85 ? 'color:var(--err)' : stats.hdd_pct > 60 ? 'color:var(--warn)' : 'color:var(--brand-3)'" x-text="stats.hdd_pct + '%'"></div>
          <div class="progress" :class="stats.hdd_pct > 85 ? 'err' : stats.hdd_pct > 60 ? 'warn' : ''"><i :style="'width:' + stats.hdd_pct + '%'"></i></div>
          <div class="rc-s mono" x-text="fmtBytes(stats.used_hdd) + ' / ' + fmtBytes(stats.total_hdd)"></div>
        </div>

        <div class="res-card">
          <div class="rc-l">Uptime</div>
          <div class="rc-v" style="font-size:22px" x-text="fmtUptime(stats.uptime)"></div>
          <div style="height:6px"></div>
          <div class="rc-s" x-text="(stats.identity || '—') + (stats.version ? ' · ROS ' + stats.version : '')"></div>
        </div>
      </div>
    </template>
  </section>

  {{-- Active Hotspot Users --}}
  <div class="card"
       x-data="hotspotUsers('{{ route('routers.hotspot-users', $routerId) }}', '{{ route('routers.disconnect', $routerId) }}')"
       x-init="init()" x-cloak>

    <div class="card-head">
      <div>
        <h3>User Hotspot Aktif</h3>
        <p>
          <span x-show="!loading" x-text="filtered.length + ' user online'"></span>
          <span x-show="loading">Memuat…</span>
        </p>
      </div>
      <div class="ch-actions">
        <div class="input-group" style="width:240px">
          <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" x-model="search" placeholder="Cari user, IP, MAC…" class="input" />
        </div>
        <span style="color:var(--text-3);font-size:12px" x-show="!loading && !fetchError">Refresh dalam <b class="mono" x-text="countdown + 's'"></b></span>
        <button @click="load()" :disabled="loading" class="btn btn-sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
               :style="loading ? 'animation: spin 1s linear infinite' : ''"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <div x-show="loading" style="padding: 12px var(--pad-card);">
      @for ($i = 0; $i < 6; $i++)
        <div class="skeleton" style="height:14px;margin-bottom:10px"></div>
      @endfor
    </div>

    <div x-show="!loading && fetchError" style="padding: 36px var(--pad-card);text-align:center;">
      <div style="color:var(--err);font-weight:600;font-size:13px" x-text="fetchError"></div>
      <button @click="load()" class="btn btn-sm" style="margin-top:8px">Coba lagi</button>
    </div>

    <div x-show="!loading && !fetchError" class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>User</th>
            <th>IP</th>
            <th>MAC</th>
            <th>Waktu Online</th>
            <th>Sisa Waktu</th>
            <th>RX / TX</th>
            @if((auth()->user()->role ?? null) === 'admin') <th style="text-align:right">Aksi</th> @endif
          </tr>
        </thead>
        <tbody>
          <template x-if="users.length === 0">
            <tr>
              <td colspan="7" style="padding: 56px 0; text-align:center; color:var(--text-2)">
                <div style="display:inline-flex;flex-direction:column;align-items:center;gap:8px">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-3)"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                  <span>Tidak ada user hotspot aktif</span>
                </div>
              </td>
            </tr>
          </template>

          <template x-for="user in filtered" :key="user['.id']">
            <tr>
              <td>
                <div class="user-cell">
                  <div class="avatar xs" x-text="(user.user || '?')[0].toUpperCase()"></div>
                  <div class="um"><b x-text="user.user || '-'"></b></div>
                </div>
              </td>
              <td class="mono" x-text="user.address || '-'"></td>
              <td class="mono" style="color:var(--text-2)" x-text="user['mac-address'] || '-'"></td>
              <td class="mono" x-text="fmtUptime(user.uptime)"></td>
              <td class="mono" style="color:var(--text-3)">
                <span x-show="user['session-time-left'] && user['session-time-left'] !== '0s'" x-text="fmtUptime(user['session-time-left'])"></span>
                <span x-show="!user['session-time-left'] || user['session-time-left'] === '0s'">—</span>
              </td>
              <td>
                <span class="mono" style="color:var(--err)">↓ <span x-text="fmtBytes(parseInt(user['bytes-in'] ?? 0))"></span></span>
                <span class="mono" style="color:var(--info);margin-left:8px">↑ <span x-text="fmtBytes(parseInt(user['bytes-out'] ?? 0))"></span></span>
              </td>
              @if((auth()->user()->role ?? null) === 'admin')
                <td style="text-align:right">
                  <button @click="disconnect(user['.id'], user.user)" :disabled="disconnecting === user['.id']"
                          class="btn btn-sm btn-danger" style="padding:4px 10px;font-size:12px">Putus</button>
                </td>
              @endif
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && !fetchError" style="padding:10px var(--pad-card);border-top:1px solid var(--border);background:var(--bg-mute);font-size:11.5px;color:var(--text-3)">
      Terakhir diperbarui: <span x-text="lastUpdated"></span>
    </div>
  </div>

  <style>
    .res-card { background:var(--card-bg);border:var(--card-border);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--card-shadow); }
    .rc-l { color:var(--text-3);font-size:11.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;margin-bottom:8px }
    .rc-v { font-size:30px;font-weight:700;letter-spacing:-.02em;margin-bottom:10px;font-variant-numeric:tabular-nums }
    .rc-s { color:var(--text-3);font-size:11.5px;margin-top:8px }
  </style>

@endsection

@push('scripts')
<script>
function routerStats(statsUrl) {
  return {
    statsUrl, loading: true, online: false, stats: {}, error: '',
    async load() {
      this.loading = true;
      try {
        const res = await fetch(this.statsUrl);
        const data = await res.json();
        this.online = data.online;
        this.stats  = data.stats ?? {};
        this.error  = data.error ?? '';
      } catch (e) { this.online = false; this.error = e.message; }
      finally { this.loading = false; }
    },
    fmtBytes(n) { n = parseInt(n) || 0; if (n >= 1073741824) return (n/1073741824).toFixed(1)+' GB'; if (n >= 1048576) return (n/1048576).toFixed(1)+' MB'; if (n >= 1024) return (n/1024).toFixed(1)+' KB'; return n+' B'; },
    fmtUptime(str) {
      if (!str) return '-';
      const label = { w:'mg', d:'hr', h:'j', m:'m' }; const parts = [];
      for (const [, num, unit] of str.matchAll(/(\d+)([wdhms])/g)) if (label[unit]) parts.push(num + label[unit]);
      return parts.join(' ') || '-';
    },
  };
}

function hotspotUsers(usersUrl, disconnectUrl) {
  return {
    usersUrl, disconnectUrl,
    loading: true, users: [], search: '', fetchError: '', disconnecting: null,
    countdown: 30, lastUpdated: '—',
    csrfToken: document.querySelector('meta[name="csrf-token"]').content,
    get filtered() {
      const q = this.search.trim().toLowerCase();
      if (!q) return this.users;
      return this.users.filter(u =>
        (u.user||'').toLowerCase().includes(q) ||
        (u.address||'').toLowerCase().includes(q) ||
        (u['mac-address']||'').toLowerCase().includes(q)
      );
    },
    init() {
      this.load();
      setInterval(() => { this.countdown = this.countdown > 0 ? this.countdown - 1 : 30; if (this.countdown === 30) this.load(); }, 1000);
    },
    async load() {
      this.loading = true; this.fetchError = '';
      try {
        const res = await fetch(this.usersUrl);
        const data = await res.json();
        if (data.success) { this.users = data.users; this.lastUpdated = new Date().toLocaleTimeString('id-ID'); this.countdown = 30; }
        else this.fetchError = data.error ?? 'Gagal memuat data.';
      } catch (e) { this.fetchError = e.message; }
      finally { this.loading = false; }
    },
    async disconnect(sessionId, username) {
      if (!confirm(`Putus koneksi user "${username}"?`)) return;
      this.disconnecting = sessionId;
      try {
        const res = await fetch(this.disconnectUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
          body: JSON.stringify({ session_id: sessionId }),
        });
        const data = await res.json();
        if (data.success) this.users = this.users.filter(u => u['.id'] !== sessionId);
        else alert('Gagal: ' + (data.error ?? 'Unknown error'));
      } catch (e) { alert('Error: ' + e.message); }
      finally { this.disconnecting = null; }
    },
    fmtBytes(n) { n = parseInt(n) || 0; if (n >= 1073741824) return (n/1073741824).toFixed(1)+' GB'; if (n >= 1048576) return (n/1048576).toFixed(1)+' MB'; if (n >= 1024) return (n/1024).toFixed(1)+' KB'; return n+' B'; },
    fmtUptime(str) {
      if (!str) return '-';
      const label = { w:'mg', d:'hr', h:'j', m:'m' }; const parts = [];
      for (const [, num, unit] of str.matchAll(/(\d+)([wdhms])/g)) if (label[unit]) parts.push(num + label[unit]);
      return parts.join(' ') || '-';
    },
  };
}
</script>
@endpush
