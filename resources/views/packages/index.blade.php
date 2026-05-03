@extends('layouts.app')

@section('title', 'Paket / Profile')
@section('page-title', 'Paket / Profile')

@section('content')

  <header class="page-head">
    <div>
      <h2>Paket / Profile</h2>
      <p>Kelola paket dan profil radius untuk user hotspot ZeroNet.</p>
    </div>
    @if (\Illuminate\Support\Facades\Route::has('packages.create'))
      <div class="head-actions">
        <a href="{{ route('packages.create') }}" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Paket
        </a>
      </div>
    @endif
  </header>

  {{-- Plan cards (top paket by user count) --}}
  @php
    $sortedForCards = collect($packages)->sortByDesc('user_count')->take(4)->values();
    $maxUsers = $sortedForCards->max('user_count') ?: 0;
  @endphp
  @if($sortedForCards->isNotEmpty())
    <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:22px">
      @foreach($sortedForCards as $pkg)
        @php
          $name = strtolower($pkg['groupname']);
          $tier = match(true) {
            str_contains($name, 'jam') || str_contains($name, 'hour')   => ['Hourly', 'brand'],
            str_contains($name, 'mingg') || str_contains($name, 'week') => ['Mingguan', 'info'],
            str_contains($name, 'bulan') || str_contains($name, 'month'), str_contains($name, 'member') => ['Bulanan', 'brand'],
            default => ['Profile', 'info'],
          };
          $isPopular = $maxUsers > 0 && $pkg['user_count'] === $maxUsers;
        @endphp
        <div class="plan-card {{ $isPopular ? 'popular' : '' }}">
          <div class="pc-head">
            <span class="badge {{ $tier[1] }}">{{ $tier[0] }}</span>
            @if($pkg['is_active'])
              <span class="badge ok">Aktif</span>
            @else
              <span class="badge">Nonaktif</span>
            @endif
            @if($pkg['is_legacy'] ?? false)
              <span class="badge warn">Legacy</span>
            @endif
          </div>
          <h3>{{ $pkg['groupname'] }}</h3>
          <p>{{ $pkg['description'] ?: '—' }}</p>
          <div class="pc-meta">
            <span><b>{{ $pkg['attribute_count'] }}</b> atribut</span>
            <span><b>{{ $pkg['user_count'] }}</b> user</span>
          </div>
          @if($isPopular)
            <span class="pc-tag">Paling banyak dipakai</span>
          @endif
        </div>
      @endforeach
    </section>
  @endif

  <div class="card">
    <div class="card-head">
      <h3>Semua Paket</h3>
      <div class="ch-actions">
        <input class="input" id="pkg-search" placeholder="Cari paket…" style="max-width:240px" />
      </div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl" id="pkg-tbl">
        <thead>
          <tr>
            <th class="ix">#</th>
            <th>Nama Paket</th>
            <th>Deskripsi</th>
            <th class="num">Atribut</th>
            <th class="num">User</th>
            <th>Status</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($packages as $i => $pkg)
            <tr data-name="{{ strtolower($pkg['groupname'].' '.$pkg['description']) }}">
              <td class="ix">{{ $i + 1 }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                  <b>{{ $pkg['groupname'] }}</b>
                  @if($pkg['is_legacy'] ?? false)
                    <span class="badge warn">Panel Lama</span>
                  @endif
                </div>
              </td>
              <td style="color:var(--text-2);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $pkg['description'] ?: '—' }}</td>
              <td class="num"><span class="badge brand no-dot">{{ $pkg['attribute_count'] }}</span></td>
              <td class="num">@if($pkg['user_count'] > 0)<b>{{ $pkg['user_count'] }}</b>@else <span style="color:var(--text-3)">{{ $pkg['user_count'] }}</span> @endif</td>
              <td>
                @if($pkg['is_active'])
                  <span class="badge ok">Aktif</span>
                @else
                  <span class="badge">Nonaktif</span>
                @endif
              </td>
              <td>
                <div class="tbl-actions">
                  @if($pkg['is_legacy'] ?? false)
                    @if (\Illuminate\Support\Facades\Route::has('packages.legacy-edit'))
                      <a href="{{ route('packages.legacy-edit', $pkg['groupname']) }}" class="icon-btn" style="color:var(--info)" title="Edit & import">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </a>
                    @endif
                  @else
                    @if (\Illuminate\Support\Facades\Route::has('packages.toggle'))
                      <form method="POST" action="{{ route('packages.toggle', $pkg['id']) }}" style="display:inline-flex">
                        @csrf @method('PATCH')
                        <button type="submit" class="icon-btn" title="{{ $pkg['is_active'] ? 'Nonaktifkan' : 'Aktifkan' }}" style="color:{{ $pkg['is_active'] ? 'var(--ok)' : 'var(--text-3)' }}">
                          @if($pkg['is_active'])
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                          @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                          @endif
                        </button>
                      </form>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('packages.edit'))
                      <a href="{{ route('packages.edit', $pkg['id']) }}" class="icon-btn" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      </a>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('packages.destroy'))
                      <form method="POST" action="{{ route('packages.destroy', $pkg['id']) }}" style="display:inline-flex"
                            onsubmit="return confirm('Hapus paket {{ addslashes($pkg['groupname']) }}?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" title="Hapus" style="color:var(--err)">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                      </form>
                    @endif
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="padding:56px 0;text-align:center;color:var(--text-2)">
                <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px">
                  <div style="width:40px;height:40px;border-radius:12px;background:var(--bg-mute);color:var(--text-3);display:grid;place-items:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                  </div>
                  <div style="font-weight:600;color:var(--text)">Belum ada paket.</div>
                  @if (\Illuminate\Support\Facades\Route::has('packages.create'))
                    <a href="{{ route('packages.create') }}" style="color:var(--brand-3);font-weight:600">Buat paket pertama →</a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <style>
    .plan-card { position:relative;background:var(--card-bg);border:var(--card-border);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--card-shadow);overflow:hidden;transition:transform .15s ease, box-shadow .15s ease; }
    .plan-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-md); }
    .plan-card.popular { border-color:transparent;background:var(--brand-grad-soft);box-shadow:0 0 0 1px var(--brand-3) inset, var(--shadow-md); }
    .pc-head { display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap; }
    .plan-card h3 { margin:0 0 4px;font-size:18px;font-weight:700;letter-spacing:-.01em; }
    .plan-card p { margin:0 0 14px;color:var(--text-2);font-size:12.5px;min-height:1.4em }
    .pc-meta { display:flex;gap:18px;font-size:12px;color:var(--text-2); }
    .pc-meta b { color:var(--text);font-weight:600;font-size:14px; }
    .pc-tag { position:absolute;top:0;right:0;background:var(--brand-grad);color:white;padding:3px 10px;font-size:10.5px;font-weight:600;border-bottom-left-radius:8px; }
  </style>

  <script>
    // Quick local filter (same-page) — tetap pakai search bar di card head
    (function () {
      const inp = document.getElementById('pkg-search');
      const tbl = document.getElementById('pkg-tbl');
      if (!inp || !tbl) return;
      inp.addEventListener('input', () => {
        const q = inp.value.trim().toLowerCase();
        tbl.querySelectorAll('tbody tr[data-name]').forEach(tr => {
          tr.style.display = !q || tr.dataset.name.includes(q) ? '' : 'none';
        });
      });
    })();
  </script>

@endsection
