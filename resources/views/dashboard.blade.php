@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

  @php
    // 5 aktivitas terbaru — untuk panel "Aktivitas Terbaru" di bawah
    $recentActivities = \App\Models\ActivityLog::with('user')
        ->latest()
        ->limit(5)
        ->get();

    // Weekly delta untuk "Total User Hotspot" — hitung user yang dibuat 7 hari terakhir via ActivityLog
    $weeklyNew = \App\Models\ActivityLog::whereIn('action', ['create_user', 'create'])
        ->where('subject_type', 'radius_user')
        ->where('created_at', '>=', now()->subDays(7))
        ->count();
    // Fallback: kalau subject_type belum dipakai, hitung berdasarkan action saja
    if ($weeklyNew === 0) {
        $weeklyNew = \App\Models\ActivityLog::where('action', 'create_user')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    // Avatar palette deterministik dari nama (matches user-hotspot._results)
    $avPalette = ['#10B981', '#06B6D4', '#0EA5E9', '#8B5CF6', '#F59E0B', '#EC4899', '#EF4444', '#14B8A6'];

    $actionMap = [
      'create_user'      => ['label' => 'Tambah User',      'tone' => 'brand'],
      'update_user'      => ['label' => 'Edit User',        'tone' => 'info'],
      'delete_user'      => ['label' => 'Hapus User',       'tone' => 'err'],
      'toggle_user'      => ['label' => 'Toggle User',      'tone' => 'warn'],
      'create_package'   => ['label' => 'Tambah Paket',     'tone' => 'brand'],
      'update_package'   => ['label' => 'Edit Paket',       'tone' => 'info'],
      'delete_package'   => ['label' => 'Hapus Paket',      'tone' => 'err'],
      'toggle_package'   => ['label' => 'Toggle Paket',     'tone' => 'warn'],
      'update_profile'   => ['label' => 'Edit Profil',      'tone' => 'info'],
      'update_password'  => ['label' => 'Ganti Password',   'tone' => 'brand'],
      'generate_voucher' => ['label' => 'Generate Voucher', 'tone' => 'ok'],
      'disable_voucher'  => ['label' => 'Nonaktif Voucher', 'tone' => 'warn'],
      'enable_voucher'   => ['label' => 'Aktifkan Voucher', 'tone' => 'info'],
      'delete_voucher'   => ['label' => 'Hapus Voucher',    'tone' => 'err'],
      'login'            => ['label' => 'Login',            'tone' => 'ok'],
      'logout'           => ['label' => 'Logout',           'tone' => ''],
      'login_failed'     => ['label' => 'Login Gagal',      'tone' => 'err'],
      'wa_send'          => ['label' => 'Kirim WA',         'tone' => 'info'],
      'reboot_router'    => ['label' => 'Reboot Router',    'tone' => 'warn'],
      'create'           => ['label' => 'Tambah',           'tone' => 'brand'],
      'update'           => ['label' => 'Edit',             'tone' => 'info'],
      'delete'           => ['label' => 'Hapus',            'tone' => 'err'],
    ];

    $activePct = $stats['total_users'] > 0
        ? round($stats['active_users'] / $stats['total_users'] * 100)
        : 0;
  @endphp

  <header class="page-head">
    <div>
      <h2>Selamat datang, {{ auth()->user()->name ?? auth()->user()->username }} 👋</h2>
      <p>Ringkasan status WiFi ZeroNet — <span class="mono" id="dash-now"></span></p>
    </div>
    <div class="head-actions">
      <button class="btn" onclick="location.reload()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        Refresh
      </button>
      <a href="{{ route('user-hotspot.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Quick Action
      </a>
    </div>
  </header>

  {{-- Stat Cards --}}
  <div x-data="{
          stats: {
              total_users:        {{ $stats['total_users'] }},
              active_users:       {{ $stats['active_users'] }},
              online_sessions:    {{ $stats['online_sessions'] }},
              available_vouchers: {{ $stats['available_vouchers'] }}
          },
          init() { setInterval(() => this.poll(), 30000); },
          async poll() {
              try {
                  const r = await axios.get('{{ route('dashboard.stats') }}');
                  this.stats = r.data;
              } catch(e) {}
          }
       }"
       class="stat-grid" style="margin-bottom: 22px;">

    <div class="stat tone-info">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="stat-label">Total User Hotspot</div>
      <div class="stat-value" x-text="stats.total_users">{{ $stats['total_users'] }}</div>
      <div class="stat-foot">
        @if($weeklyNew > 0)
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--ok)" stroke-width="2.4" stroke-linecap="round"><polyline points="18 15 12 9 6 15"/></svg>
          <span style="color:var(--ok);font-weight:600">+{{ $weeklyNew }}</span>
          <span>dari minggu lalu</span>
        @else
          terdaftar di RADIUS
        @endif
      </div>
    </div>

    <div class="stat tone-ok">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="stat-label">User Aktif</div>
      <div class="stat-value" x-text="stats.active_users">{{ $stats['active_users'] }}</div>
      <div class="stat-foot">{{ $activePct }}% dari total terdaftar</div>
    </div>

    <div class="stat">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0 1 14 0"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M8.5 16.43a6 6 0 0 1 7 0"/><circle cx="12" cy="20" r="1.2" fill="currentColor"/></svg>
      </div>
      <div class="stat-label">User Online</div>
      <div class="stat-value" x-text="stats.online_sessions">{{ $stats['online_sessions'] }}</div>
      <div class="stat-foot"><span class="live">Live</span></div>
    </div>

    <div class="stat tone-warn">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9.5V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2.5a2.5 2.5 0 0 0 0 5V17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2.5a2.5 2.5 0 0 0 0-5z"/></svg>
      </div>
      <div class="stat-label">Voucher Tersedia</div>
      <div class="stat-value" x-text="stats.available_vouchers">{{ $stats['available_vouchers'] }}</div>
      <div class="stat-foot">
        @if($stats['available_vouchers'] === 0)
          <a href="{{ route('vouchers.create') }}" style="color:var(--brand-3);font-weight:600">Generate batch →</a>
        @else
          <a href="{{ route('vouchers.index') }}" style="color:var(--brand-3);font-weight:600">Lihat voucher →</a>
        @endif
      </div>
    </div>
  </div>

  {{-- Trafik Real-time per Router --}}
  <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:16px;margin-bottom:22px;">
    @foreach ($routers as $router)
      <div
        x-data="trafficChart('{{ $router['name'] }}', '{{ route('routers.traffic', $router['id']) }}')"
        x-init="init()"
        class="card">

        <div class="card-head">
          <div class="router-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M6 18h.01"/><path d="M10 18h.01"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/></svg>
          </div>
          <div>
            <h3>{{ $router['name'] }}</h3>
            <p class="mono">ether1 (WAN) · {{ $router['host'] ?? '' }}</p>
          </div>
          <div class="ch-actions">
            <template x-if="online"><span class="badge ok">Live</span></template>
            <template x-if="online === false"><span class="badge err">Offline</span></template>
            <template x-if="online === null"><span class="badge">Menghubungkan…</span></template>
            <a href="{{ route('routers.show', $router['id']) }}" class="btn btn-sm btn-ghost">Detail</a>
          </div>
        </div>

        <div class="card-pad">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:12px;">
            <div>
              <div class="dl-ul-label">Download</div>
              <div class="mono" style="font-size:24px;font-weight:700;color:var(--err);margin-top:4px" x-text="currentDownload">—</div>
            </div>
            <div>
              <div class="dl-ul-label">Upload</div>
              <div class="mono" style="font-size:24px;font-weight:700;color:var(--info);margin-top:4px" x-text="currentUpload">—</div>
            </div>
          </div>
          <div style="height:170px;">
            <canvas x-ref="canvas"></canvas>
          </div>
        </div>
      </div>
    @endforeach
  </section>

  {{-- Bottom row: Aktivitas Terbaru + Router Health + Quick Actions --}}
  <section class="dash-bottom" style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">

    {{-- Aktivitas Terbaru --}}
    <div class="card">
      <div class="card-head">
        <h3>Aktivitas Terbaru</h3>
        <div class="ch-actions">
          @if(\Illuminate\Support\Facades\Route::has('activity-logs.index'))
            <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-ghost">Lihat semua →</a>
          @endif
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>User</th>
              <th>Aksi</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentActivities as $log)
              @php
                $action = $actionMap[$log->action] ?? ['label' => $log->action, 'tone' => ''];
                $isSystem = is_null($log->user_id);
                $userName = $isSystem ? 'Sistem' : ($log->user?->name ?? $log->user?->username ?? '—');
                $initial = mb_strtoupper(mb_substr($userName, 0, 1));
                $avBg = $isSystem
                    ? '#64748B'
                    : $avPalette[crc32($userName) % count($avPalette)];
              @endphp
              <tr>
                <td style="color:var(--text-3);white-space:nowrap;font-size:12px">
                  {{ $log->created_at->diffForHumans(['short' => true]) }}
                </td>
                <td>
                  <div class="user-cell">
                    <div class="avatar xs" style="background:{{ $avBg }}">{{ $initial }}</div>
                    <div class="um"><b style="{{ $isSystem ? 'font-style:italic' : '' }}">{{ $userName }}</b></div>
                  </div>
                </td>
                <td>
                  <span class="badge {{ $action['tone'] }}">{{ $action['label'] }}</span>
                </td>
                <td style="color:var(--text-2);font-size:12.5px">{{ $log->description ?: '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="padding:36px 0;text-align:center;color:var(--text-3);font-size:13px">
                  Belum ada aktivitas tercatat.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Right column: Router Health + Quick Actions --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

      {{-- Router Health --}}
      <div class="card card-pad" x-data="routerHealth({{ \Illuminate\Support\Js::from($routers->map(fn($r) => ['id' => $r['id'], 'name' => $r['name'], 'url' => route('routers.stats', $r['id'])])->values()) }})" x-init="init()">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
          <h3 style="margin:0;font-size:14px;font-weight:600">Router Health</h3>
          <span class="badge ok" style="margin-left:auto" x-text="onlineCount + '/' + total + ' online'"></span>
        </div>

        <template x-for="r in routers" :key="r.id">
          <div class="health-row">
            <div class="hr-l">
              <b x-text="r.name"></b>
              <span class="mono" x-text="r.online ? fmtUptime(r.uptime) + ' uptime' : (r.loading ? 'memuat…' : 'offline')"></span>
            </div>
            <div class="hr-r">
              <div class="mini-bar">
                <span>CPU</span>
                <div class="progress" :class="r.cpu > 80 ? 'err' : r.cpu > 50 ? 'warn' : ''">
                  <i :style="'width:' + (r.cpu || 0) + '%'"></i>
                </div>
                <b x-text="(r.cpu || 0) + '%'"></b>
              </div>
              <div class="mini-bar">
                <span>RAM</span>
                <div class="progress" :class="r.ram > 80 ? 'err' : r.ram > 60 ? 'warn' : ''">
                  <i :style="'width:' + (r.ram || 0) + '%'"></i>
                </div>
                <b x-text="(r.ram || 0) + '%'"></b>
              </div>
            </div>
          </div>
        </template>
      </div>

      {{-- Quick Actions --}}
      <div class="card card-pad">
        <h3 style="margin:0 0 12px;font-size:14px;font-weight:600">Quick Actions</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
          <a href="{{ route('user-hotspot.create') }}" class="qa">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah User
          </a>
          <a href="{{ route('vouchers.create') }}" class="qa">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9.5V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2.5a2.5 2.5 0 0 0 0 5V17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2.5a2.5 2.5 0 0 0 0-5z"/></svg>
            Generate Voucher
          </a>
          @if(\Illuminate\Support\Facades\Route::has('whatsapp.index'))
            <a href="{{ route('whatsapp.index') }}" class="qa">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
              Broadcast WA
            </a>
          @endif
          <a href="{{ route('packages.index') }}" class="qa">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Kelola Paket
          </a>
        </div>
      </div>
    </div>
  </section>

  <style>
    .router-mark { width:36px;height:36px;border-radius:10px;background:color-mix(in srgb,var(--brand-3) 12%,transparent);color:var(--brand-3);display:grid;place-items:center;flex-shrink:0; }
    .router-mark svg { width:18px;height:18px; }
    .dl-ul-label { color:var(--text-3);font-size:11.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase; }

    /* Router Health */
    .health-row { display:grid;grid-template-columns:120px 1fr;gap:14px;padding:12px 0;border-bottom:1px dashed var(--border);align-items:center; }
    .health-row:last-child { border-bottom:0; padding-bottom:0; }
    .hr-l b { display:block;font-weight:600;font-size:13px; }
    .hr-l span { color:var(--text-3);font-size:11.5px; }
    .mini-bar { display:grid;grid-template-columns:36px 1fr 36px;gap:10px;align-items:center;font-size:11.5px;margin-bottom:6px; }
    .mini-bar:last-child { margin-bottom:0; }
    .mini-bar span { color:var(--text-2); }
    .mini-bar b { font-family:'JetBrains Mono', monospace;font-weight:600;text-align:right;font-size:12px; }

    /* Quick Actions */
    .qa { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:14px 8px;border:1px solid var(--border);border-radius:var(--r-md);color:var(--text-2);font-size:12px;font-weight:500;text-align:center;transition:all .15s ease;text-decoration:none; }
    .qa svg { width:18px;height:18px;color:var(--brand-3); }
    .qa:hover { background:var(--bg-mute);color:var(--text);border-color:var(--brand-3); }
    .qa.qa-active { color: var(--brand-3); border-color: var(--brand-3); background: color-mix(in srgb, var(--brand-3) 6%, transparent); }

    /* Bottom layout responsive */
    @media (max-width: 1024px) { .dash-bottom { grid-template-columns: 1fr !important; } }
  </style>

@endsection

@push('scripts')
<script src="{{ asset('assets/chart.umd.min.js') }}"></script>
<script>
  // Jam realtime di header
  (function () {
    var el = document.getElementById('dash-now');
    function tick() { if (el) el.textContent = new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }); }
    tick(); setInterval(tick, 1000);
  })();

  // Router Health: poll stats untuk semua router setiap 15 detik
  function routerHealth(initial) {
    return {
      routers: initial.map(r => ({ ...r, loading: true, online: false, cpu: 0, ram: 0, uptime: '' })),
      get total() { return this.routers.length; },
      get onlineCount() { return this.routers.filter(r => r.online).length; },
      init() {
        this.refreshAll();
        setInterval(() => this.refreshAll(), 15000);
      },
      async refreshAll() {
        await Promise.all(this.routers.map(r => this.fetchOne(r)));
      },
      async fetchOne(r) {
        try {
          const res = await fetch(r.url, { signal: AbortSignal.timeout?.(3000) });
          const data = await res.json();
          if (data.online) {
            r.online = true;
            r.cpu = data.stats?.cpu_load ?? 0;
            r.ram = data.stats?.mem_pct ?? 0;
            r.uptime = data.stats?.uptime ?? '';
          } else {
            r.online = false;
          }
        } catch (e) { r.online = false; }
        finally { r.loading = false; }
      },
      fmtUptime(str) {
        if (!str) return '-';
        const label = { w:'mg', d:'hr', h:'j', m:'m' };
        const parts = [];
        for (const [, num, unit] of String(str).matchAll(/(\d+)([wdhms])/g)) if (label[unit]) parts.push(num + label[unit]);
        return parts.slice(0, 2).join(' ') || '-';
      },
    };
  }

  function trafficChart(routerName, trafficUrl) {
    return {
      routerName, trafficUrl,
      online: null, currentDownload: '—', currentUpload: '—',
      _inFlight: false, _firstPoll: true,
      init() {
        this.$nextTick(() => {
          this.initChart();
          this.poll();
          this.$el._intervalId = setInterval(() => this.poll(), 3000);
          this._onVis = () => {
            if (document.hidden) { clearInterval(this.$el._intervalId); this.$el._intervalId = null; }
            else if (!this.$el._intervalId) { this.poll(); this.$el._intervalId = setInterval(() => this.poll(), 3000); }
          };
          document.addEventListener('visibilitychange', this._onVis);
        });
      },
      initChart() {
        const isDark = document.documentElement.dataset.theme === 'dark' || document.documentElement.classList.contains('dark');
        const tickClr = isDark ? '#9ca3af' : '#6b7280';
        const gridClr = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
        const fmtBps  = v => this.fmtBps(v);
        const POINTS  = 30;
        const ctx = this.$refs.canvas.getContext('2d');
        this.$el._chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: Array(POINTS).fill(''),
            datasets: [
              { label: 'Download', data: Array(POINTS).fill(0), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.12)', tension: 0.4, pointRadius: 0, borderWidth: 2, fill: true },
              { label: 'Upload',   data: Array(POINTS).fill(0), borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.12)', tension: 0.4, pointRadius: 0, borderWidth: 2, fill: true },
            ],
          },
          options: {
            responsive: true, maintainAspectRatio: false, animation: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
              x: { display: false },
              y: { beginAtZero: true, border: { display: false }, grid: { color: gridClr },
                   ticks: { color: tickClr, font: { size: 10 }, maxTicksLimit: 4, callback: fmtBps } },
            },
            plugins: {
              legend: { display: true, position: 'bottom', labels: { color: tickClr, boxWidth: 10, boxHeight: 10, padding: 12, font: { size: 11 } } },
              tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + fmtBps(c.raw), title: () => '' } },
            },
          },
        });
      },
      async poll() {
        if (this._inFlight) return;
        this._inFlight = true;
        const ctl = new AbortController();
        const to  = setTimeout(() => ctl.abort(), 2500);
        try {
          const r = await fetch(this.trafficUrl, { signal: ctl.signal });
          const d = await r.json();
          if (!d.online) { this.online = false; return; }
          this.online = true;
          if (this._firstPoll) { this._firstPoll = false; return; }
          const chart = this.$el._chart;
          if (chart) {
            chart.data.labels.push(''); chart.data.labels.shift();
            chart.data.datasets[0].data.push(d.download); chart.data.datasets[0].data.shift();
            chart.data.datasets[1].data.push(d.upload);   chart.data.datasets[1].data.shift();
            chart.update('none');
          }
          this.currentDownload = this.fmtBps(d.download);
          this.currentUpload   = this.fmtBps(d.upload);
        } catch (e) { this.online = false; }
        finally { clearTimeout(to); this._inFlight = false; }
      },
      fmtBps(bps) {
        if (bps >= 1_000_000) return (bps / 1_000_000).toFixed(2) + ' Mbps';
        if (bps >= 1_000)     return (bps / 1_000).toFixed(1)     + ' Kbps';
        return bps + ' bps';
      },
    };
  }
</script>
@endpush
