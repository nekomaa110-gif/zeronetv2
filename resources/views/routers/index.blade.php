@extends('layouts.app')

@section('title', 'Manajemen Router')
@section('page-title', 'Manajemen Router')

@section('content')

  <header class="page-head">
    <div>
      <h2>Manajemen Router</h2>
      <p>Monitor dan kelola router MikroTik via tunnel VPN.</p>
    </div>
  </header>

  <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:16px">
    @foreach ($routers as $router)
      <a href="{{ route('routers.show', $router['id']) }}"
         x-data="routerCard('{{ route('routers.stats', $router['id']) }}')"
         x-init="load()"
         class="router-card">

        <div class="rc-head">
          <div class="router-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/></svg>
          </div>
          <div>
            <h3>{{ $router['name'] }}</h3>
            <p class="mono">{{ $router['host'] }}:{{ $router['port'] }}</p>
          </div>
          <template x-if="loading"><span class="badge" style="margin-left:auto">Mengecek…</span></template>
          <template x-if="!loading && online"><span class="badge ok" style="margin-left:auto">Online</span></template>
          <template x-if="!loading && !online"><span class="badge err" style="margin-left:auto">Offline</span></template>
        </div>

        <template x-if="loading">
          <div>
            <div class="rc-meta">
              <div><span>Identitas</span><b>—</b></div>
              <div><span>Waktu Aktif</span><b class="mono">—</b></div>
              <div><span>Versi</span><b class="mono">—</b></div>
            </div>
            <div class="rc-bars">
              <div class="mb"><span>CPU</span><div class="progress"><i></i></div><b class="mono">--%</b></div>
              <div class="mb"><span>RAM</span><div class="progress"><i></i></div><b class="mono">--%</b></div>
              <div class="mb"><span>Disk</span><div class="progress"><i></i></div><b class="mono">--%</b></div>
            </div>
          </div>
        </template>

        <template x-if="!loading && online">
          <div>
            <div class="rc-meta">
              <div><span>Identitas</span><b x-text="stats.identity || '—'"></b></div>
              <div><span>Waktu Aktif</span><b class="mono" x-text="fmtUptime(stats.uptime)"></b></div>
              <div><span>Versi</span><b class="mono" x-text="stats.version ? 'ROS ' + stats.version : '—'"></b></div>
            </div>
            <div class="rc-bars">
              <div class="mb"><span>CPU</span>
                <div class="progress" :class="stats.cpu_load > 80 ? 'err' : stats.cpu_load > 50 ? 'warn' : 'ok'">
                  <i :style="'width:' + stats.cpu_load + '%'"></i>
                </div>
                <b class="mono" x-text="stats.cpu_load + '%'"></b>
              </div>
              <div class="mb"><span>RAM</span>
                <div class="progress" :class="stats.mem_pct > 80 ? 'err' : stats.mem_pct > 60 ? 'warn' : ''">
                  <i :style="'width:' + stats.mem_pct + '%'"></i>
                </div>
                <b class="mono" x-text="stats.mem_pct + '%'"></b>
              </div>
              <div class="mb"><span>Disk</span>
                <div class="progress" :class="stats.hdd_pct > 80 ? 'err' : stats.hdd_pct > 60 ? 'warn' : ''">
                  <i :style="'width:' + stats.hdd_pct + '%'"></i>
                </div>
                <b class="mono" x-text="stats.hdd_pct + '%'"></b>
              </div>
            </div>
          </div>
        </template>

        <template x-if="!loading && !online">
          <div style="padding:14px 0;text-align:center;color:var(--text-3)">
            <div style="font-size:13px;color:var(--text-2)">Router tidak dapat dijangkau</div>
            <div style="font-size:11.5px;margin-top:4px" x-text="error"></div>
          </div>
        </template>

        <div class="rc-foot">
          Lihat detail
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
    @endforeach
  </section>

  <style>
    .router-card { display:block;background:var(--card-bg);border:var(--card-border);border-radius:var(--r-lg);box-shadow:var(--card-shadow);padding:20px;color:var(--text);transition:all .18s ease;text-decoration:none; }
    .router-card:hover { border-color:var(--brand-3);box-shadow:var(--shadow-md);transform:translateY(-2px); }
    .rc-head { display:flex;align-items:center;gap:12px;margin-bottom:18px; }
    .router-mark { width:42px;height:42px;border-radius:11px;background:color-mix(in srgb,var(--brand-3) 12%,transparent);color:var(--brand-3);display:grid;place-items:center;flex-shrink:0; }
    .router-mark svg { width:20px;height:20px; }
    .rc-head h3 { margin:0;font-size:17px;font-weight:700;letter-spacing:-.01em; }
    .rc-head p { margin:2px 0 0;color:var(--text-3);font-size:12px; }
    .rc-meta { display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:14px;background:var(--bg-mute);border-radius:var(--r-md);margin-bottom:14px; }
    .rc-meta span { display:block;color:var(--text-3);font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;margin-bottom:2px; }
    .rc-meta b { font-size:13px;font-weight:600; }
    .rc-bars { display:flex;flex-direction:column;gap:8px;margin-bottom:14px; }
    .mb { display:grid;grid-template-columns:48px 1fr 44px;gap:12px;align-items:center;font-size:12px; }
    .mb span { color:var(--text-2); }
    .mb b { font-weight:600;text-align:right;font-size:12px; }
    .rc-foot { display:flex;align-items:center;gap:6px;color:var(--brand-3);font-size:13px;font-weight:600;padding-top:12px;border-top:1px solid var(--border); }
  </style>

@endsection

@push('scripts')
<script>
function routerCard(statsUrl) {
  return {
    statsUrl,
    loading: true, online: false, stats: {}, error: '',
    async load() {
      try {
        const res = await fetch(statsUrl);
        const data = await res.json();
        this.online = data.online;
        this.stats  = data.stats ?? {};
        this.error  = data.error ?? '';
      } catch (e) { this.online = false; this.error = e.message; }
      finally { this.loading = false; }
    },
    fmtUptime(str) {
      if (!str) return '-';
      const label = { w: 'mg', d: 'hr', h: 'j', m: 'm' };
      const parts = [];
      for (const [, num, unit] of str.matchAll(/(\d+)([wdhms])/g)) if (label[unit]) parts.push(num + label[unit]);
      return parts.join(' ') || '-';
    },
  };
}
</script>
@endpush
