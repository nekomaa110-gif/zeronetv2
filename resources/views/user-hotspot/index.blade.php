@extends('layouts.app')

@section('title', 'User Hotspot')
@section('page-title', 'User Hotspot')

@section('content')

  <header class="page-head">
    <div>
      <h2>User Hotspot</h2>
      <p>Kelola akun radius user hotspot ZeroNet — buat, perpanjang, reset, dan pantau.</p>
    </div>
    <div class="head-actions">
      <a href="{{ route('user-hotspot.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export
      </a>
      <a href="{{ route('user-hotspot.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah User
      </a>
    </div>
  </header>

  {{-- Stat strip --}}
  <div class="stat-grid" style="margin-bottom: 18px;">
    <div class="stat tone-info">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="7" r="4"/><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/></svg>
      </div>
      <div class="stat-label">Total User</div>
      <div class="stat-value">{{ number_format($stats['total']) }}</div>
      <div class="stat-foot">Semua akun terdaftar</div>
    </div>
    <div class="stat tone-ok">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div class="stat-label">Aktif</div>
      <div class="stat-value">{{ number_format($stats['active']) }}</div>
      <div class="stat-foot">{{ $stats['total'] > 0 ? round($stats['active']/$stats['total']*100).'% dari total' : '—' }}</div>
    </div>
    <div class="stat tone-rose">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="stat-label">Expired</div>
      <div class="stat-value">{{ number_format($stats['expired']) }}</div>
      <div class="stat-foot">Perlu diperpanjang</div>
    </div>
    <div class="stat tone-warn">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
      </div>
      <div class="stat-label">Nonaktif</div>
      <div class="stat-value">{{ number_format($stats['disabled']) }}</div>
      <div class="stat-foot">Diblokir admin</div>
    </div>
  </div>

  {{-- Toolbar + Table card --}}
  <div class="card">
    <form method="GET" action="{{ route('user-hotspot.index') }}"
          data-live-target="#radius-users-results"
          style="padding:14px var(--pad-card);display:flex;gap:12px;flex-wrap:wrap;align-items:center;border-bottom:1px solid var(--border)">

      @php
        $segments = [
          ['k' => '',         'l' => 'Semua',    'c' => $stats['total']],
          ['k' => 'aktif',    'l' => 'Aktif',    'c' => $stats['active']],
          ['k' => 'expired',  'l' => 'Expired',  'c' => $stats['expired']],
          ['k' => 'nonaktif', 'l' => 'Nonaktif', 'c' => $stats['disabled']],
        ];
      @endphp

      <div class="tabs">
        @foreach ($segments as $seg)
          @php $on = (string)$status === (string)$seg['k']; @endphp
          <button type="button" data-status-pick="{{ $seg['k'] }}" class="{{ $on ? 'active' : '' }}">
            {{ $seg['l'] }}
            <span class="count">{{ $seg['c'] }}</span>
          </button>
        @endforeach
      </div>

      <input type="hidden" name="status" id="status-input" value="{{ $status }}" data-live-submit>

      <div class="input-group" style="flex:1;min-width:200px;max-width:340px;">
        <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari username, paket…"
               data-live-search class="input" />
      </div>

      <select name="group" data-live-submit class="select" style="max-width:180px;">
        <option value="">Semua Profil</option>
        @foreach ($groups as $g)
          <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
        @endforeach
      </select>

      <span class="live" style="margin-left:auto">Live</span>
    </form>

    <div id="radius-users-results" data-live-results>
      @include('user-hotspot._results')
    </div>
  </div>

  {{-- Quick view drawer --}}
  <div class="drawer-overlay" id="drawer-ov"></div>
  <aside class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-head">
      <div class="avatar" id="dr-avatar">A</div>
      <div style="flex:1;min-width:0">
        <h3 id="dr-name" style="margin:0;font-size:16px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">—</h3>
        <p id="dr-sub" style="margin:0;color:var(--text-3);font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">—</p>
      </div>
      <button class="icon-btn" id="dr-close" type="button" aria-label="Tutup">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="drawer-body">
      <div id="dr-status-row" style="margin-bottom:18px"></div>

      <h4 class="dr-section">Detail Akun</h4>
      <div class="kvp"><span class="k">Username</span><span class="v mono" id="dr-username">—</span></div>
      <div class="kvp"><span class="k">Paket</span><span class="v" id="dr-paket">—</span></div>
      <div class="kvp"><span class="k">Expire</span><span class="v mono" id="dr-expire">—</span></div>
      <div class="kvp"><span class="k">Sisa Waktu</span><span class="v mono" id="dr-remain">—</span></div>

      <h4 class="dr-section">Sesi Saat Ini</h4>
      <div class="kvp"><span class="k">IP Klien</span><span class="v mono" id="dr-ip">—</span></div>
      <div class="kvp"><span class="k">MAC Address</span><span class="v mono" id="dr-mac">—</span></div>
      <div class="kvp"><span class="k">Login Terakhir</span><span class="v" id="dr-last">—</span></div>
      <div class="kvp"><span class="k">RX / TX</span><span class="v mono" id="dr-rxtx">—</span></div>

      <h4 class="dr-section">Catatan WA</h4>
      <p style="color:var(--text-2);font-size:13px;margin:0">
        Reminder otomatis dikirim 2 hari sebelum expire melalui WhatsApp Gateway.
      </p>
    </div>

    <div class="drawer-foot">
      <a href="#" id="dr-edit" class="btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit
      </a>
      <a href="#" id="dr-extend" class="btn btn-primary" style="margin-left:auto">Perpanjang +30 hari</a>
    </div>
  </aside>

  <style>
    .user-row { cursor: pointer; }
    .dr-section { margin:0 0 10px; font-size:11.5px; color:var(--text-3); font-weight:600; letter-spacing:.07em; text-transform:uppercase; }
    .dr-section + .dr-section { margin-top:24px; }
    h4.dr-section ~ h4.dr-section { margin-top:24px; }
    .drawer-body h4.dr-section:not(:first-child) { margin-top:24px; }
  </style>

  <script>
    (function () {
      // Status pill buttons (segmented).
      // Form ini di luar `data-live-results` (cuma tabel yg di-swap),
      // jadi class `.active` di-toggle manual di sini setiap klik.
      function bindStatusPicks() {
        document.querySelectorAll('[data-status-pick]').forEach(btn => {
          if (btn._bound) return;
          btn._bound = true;
          btn.addEventListener('click', () => {
            // Update visual highlight immediately
            document.querySelectorAll('[data-status-pick]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const form = btn.closest('form');
            const hidden = form?.querySelector('#status-input');
            if (!hidden) return;
            hidden.value = btn.getAttribute('data-status-pick');
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
          });
        });
      }
      bindStatusPicks();

      // Drawer
      const drawer  = document.getElementById('drawer');
      const overlay = document.getElementById('drawer-ov');
      const closeBtn = document.getElementById('dr-close');

      const editTpl   = '{{ route('user-hotspot.edit', ['radius_user' => '__NAME__']) }}';
      // Endpoint resmi untuk perpanjang belum dipasang di route map publik —
      // tombol Perpanjang mengarah ke halaman edit (admin set tanggal manual).
      const extendTpl = '{{ route('user-hotspot.edit', ['radius_user' => '__NAME__']) }}';

      function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

      function fillSession(elId, value, fallback) {
        const el = document.getElementById(elId);
        if (value) {
          el.textContent = value;
          el.style.color = '';
        } else {
          el.textContent = fallback;
          el.style.color = 'var(--text-3)';
        }
      }

      function openDrawer(row) {
        const ds = row.dataset;
        const username = ds.username || '—';
        const paket    = ds.paket    || '—';
        const expire   = ds.expire   || '—';
        const remain   = ds.remain   || '—';
        const status   = ds.status   || 'active';
        const avBg     = ds.avatarBg || '#10B981';
        const ip       = ds.ip       || '';
        const mac      = ds.mac      || '';
        const rx       = ds.rx       || '';
        const tx       = ds.tx       || '';
        const lastLog  = ds.lastLogin|| '';
        const isOnline = ds.online === '1';

        const av = document.getElementById('dr-avatar');
        av.textContent = (username[0] || 'A').toUpperCase();
        av.style.background = avBg;

        document.getElementById('dr-name').textContent = username;
        document.getElementById('dr-sub').textContent  = '@' + username.toLowerCase() + ' · ' + paket;

        const statusRow = document.getElementById('dr-status-row');
        let statusBadge = '';
        let statusNote  = '';
        if (status === 'active') {
          statusBadge = '<span class="badge ok">Aktif</span>';
          statusNote  = isOnline ? 'Sesi online sekarang' : 'Akun aktif';
        } else if (status === 'expired') {
          statusBadge = '<span class="badge err">Expired</span>';
          statusNote  = 'Perlu diperpanjang';
        } else {
          statusBadge = '<span class="badge warn">Nonaktif</span>';
          statusNote  = 'Diblokir admin';
        }
        statusRow.innerHTML = statusBadge + ' <span style="color:var(--text-3);margin-left:8px;font-size:12.5px">' + statusNote + '</span>';

        document.getElementById('dr-username').textContent = username;
        document.getElementById('dr-paket').textContent    = paket;
        document.getElementById('dr-expire').textContent   = expire;
        document.getElementById('dr-remain').textContent   = remain;

        // Sesi terakhir (dari radacct, sudah di-pre-render di data-* attr)
        fillSession('dr-ip',   ip,                       'Belum pernah login');
        fillSession('dr-mac',  mac,                      '—');
        fillSession('dr-last', lastLog,                  '—');
        fillSession('dr-rxtx', (rx && tx) ? rx + ' / ' + tx : '', '—');

        document.getElementById('dr-edit').href   = editTpl.replace('__NAME__', encodeURIComponent(username));
        document.getElementById('dr-extend').href = extendTpl.replace('__NAME__', encodeURIComponent(username));

        drawer.classList.add('open');
        overlay.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
      }

      function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
      }

      // Event delegation: listener dipasang di container yang TIDAK ke-swap.
      // `data-live-results` boleh ke-swap; click delegated dari ancestornya.
      document.addEventListener('click', (e) => {
        const row = e.target.closest('.user-row');
        if (!row) return;
        // Jangan buka drawer kalau klik kena tombol/form/anchor di dalam baris
        if (e.target.closest('button, a, form, .tbl-actions')) return;
        openDrawer(row);
      });

      closeBtn.addEventListener('click', closeDrawer);
      overlay.addEventListener('click', closeDrawer);
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

      // Re-bind setelah AJAX swap (untuk segmented status di form yang ke-replace?
      // Form sendiri tidak di-swap; tapi dengarkan event live-search:loaded untuk safety.)
      document.addEventListener('live-search:loaded', bindStatusPicks);
    })();
  </script>
@endsection
