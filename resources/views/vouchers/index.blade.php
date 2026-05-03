@extends('layouts.app')

@section('title', 'Voucher')
@section('page-title', 'Voucher')

@section('content')

  <header class="page-head">
    <div>
      <h2>Voucher</h2>
      <p>Generate, cetak, dan kelola voucher hotspot batch.</p>
    </div>
    <div class="head-actions">
      <button id="btn-print-selected" type="button" onclick="printSelected()" class="btn hidden">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        <span id="btn-print-label">Print Terpilih</span>
      </button>
      <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Generate Voucher
      </a>
    </div>
  </header>

  @php
    // Lightweight aggregate stats (4 indexed count() — murah, dipakai hanya untuk tampilan ringkas)
    $vTotal    = \App\Models\Voucher::count();
    $vReady    = \App\Models\Voucher::where('status', 'ready')->count();
    $vUsed     = \App\Models\Voucher::where('status', 'active')->count();
    $vExpired  = \App\Models\Voucher::where('status', 'expired')->count();
  @endphp
  <section class="stat-grid" style="margin-bottom: 22px;">
    <div class="stat tone-info">
      <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/></svg></div>
      <div class="stat-label">Total Voucher</div>
      <div class="stat-value">{{ number_format($vTotal) }}</div>
    </div>
    <div class="stat tone-ok">
      <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="stat-label">Terpakai</div>
      <div class="stat-value">{{ number_format($vUsed) }}</div>
    </div>
    <div class="stat">
      <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div>
      <div class="stat-label">Tersedia</div>
      <div class="stat-value">{{ number_format($vReady) }}</div>
      @if($vReady === 0)<div class="stat-foot"><a href="{{ route('vouchers.create') }}" style="color:var(--brand-3);font-weight:600">Generate batch baru →</a></div>@endif
    </div>
    <div class="stat tone-warn">
      <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      <div class="stat-label">Expired</div>
      <div class="stat-value">{{ number_format($vExpired) }}</div>
    </div>
  </section>

  <div class="card">

    {{-- Filter --}}
    <div style="padding: 14px var(--pad-card); border-bottom: 1px solid var(--border);">
      <form method="GET" action="{{ route('vouchers.index') }}"
            style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">

        <div class="input-group" style="width:240px;position:relative;">
          <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode / catatan..." data-live-search class="input" />
        </div>

        <select name="status" class="select" style="width:auto;min-width:160px;">
          <option value="">Semua Status</option>
          <option value="ready"    @selected($status === 'ready')>Ready</option>
          <option value="active"   @selected($status === 'active')>Digunakan</option>
          <option value="expired"  @selected($status === 'expired')>Expired</option>
          <option value="disabled" @selected($status === 'disabled')>Nonaktif</option>
        </select>

        <select name="type" class="select" style="width:auto;min-width:160px;">
          <option value="">Semua Tipe</option>
          @foreach($types as $key => $cfg)
            <option value="{{ $key }}" @selected($type === $key)>{{ $cfg['label'] }}</option>
          @endforeach
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>

        @if($search || $status || $type)
          <a href="{{ route('vouchers.index') }}" class="btn btn-ghost">Reset</a>
        @endif
      </form>
    </div>

    {{-- Banner pilih semua lintas halaman --}}
    <div id="select-all-banner" class="hidden"
         style="padding: 10px var(--pad-card); background: color-mix(in srgb, var(--brand-3) 8%, transparent); border-bottom: 1px solid var(--border); font-size: 13px; color: var(--brand-3); display:flex; align-items:center; gap:8px;">
      <span id="banner-text"></span>
      <button id="btn-select-all-pages" type="button" style="background:none;border:0;color:inherit;font-weight:600;text-decoration:underline;cursor:pointer"></button>
    </div>

    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px"><input type="checkbox" id="check-all"></th>
            <th>Username</th>
            <th>Password</th>
            <th>Tipe</th>
            <th>Paket</th>
            <th style="text-align:center">Status</th>
            <th>Login Pertama</th>
            <th>Expired</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vouchers as $v)
            <tr>
              <td>
                <input type="checkbox" value="{{ $v->id }}" class="voucher-checkbox">
              </td>
              <td>
                <span class="mono" style="font-weight:700;letter-spacing:.06em">{{ $v->code }}</span>
                @if($v->note)
                  <div style="font-size:11.5px;color:var(--text-3);margin-top:2px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $v->note }}</div>
                @endif
              </td>
              <td>
                <span class="mono" style="font-weight:700;font-size:15px;letter-spacing:.06em">{{ $v->password ?? '—' }}</span>
              </td>
              <td>
                @php $typeInfo = $types[$v->type] ?? null; @endphp
                <span style="font-size:12px;color:var(--text-2);font-weight:500">{{ $typeInfo['label'] ?? $v->type }}</span>
              </td>
              <td>
                @if($v->package)
                  <span class="badge brand">{{ $v->package->groupname }}</span>
                @else
                  <span style="color:var(--text-3)">—</span>
                @endif
              </td>
              <td style="text-align:center">
                @switch($v->status)
                  @case('ready')    <span class="badge ok">Ready</span> @break
                  @case('active')   <span class="badge warn">Digunakan</span> @break
                  @case('expired')  <span class="badge err">Expired</span> @break
                  @case('disabled') <span class="badge">Nonaktif</span> @break
                @endswitch
              </td>
              <td style="font-size:12px;color:var(--text-2)">
                @if($v->first_login_at)
                  <div>{{ $v->first_login_at->format('d M Y') }}</div>
                  <div class="mono" style="color:var(--text-3)">{{ $v->first_login_at->format('H:i') }}</div>
                @else
                  <span style="color:var(--text-3)">—</span>
                @endif
              </td>
              <td style="font-size:12px">
                @if($v->expired_at)
                  <div style="color:{{ $v->status === 'expired' ? 'var(--err)' : 'var(--text-2)' }};font-weight:{{ $v->status === 'expired' ? '600' : '400' }}">
                    {{ $v->expired_at->format('d M Y') }}
                  </div>
                  <div class="mono" style="color:var(--text-3)">{{ $v->expired_at->format('H:i') }}</div>
                @else
                  <span style="color:var(--text-3)">—</span>
                @endif
              </td>
              <td>
                <div class="tbl-actions">
                  <a href="{{ route('vouchers.print', ['ids' => $v->id]) }}" target="_blank" class="icon-btn" title="Print">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  </a>
                  @if($v->status === 'disabled' && (auth()->user()->role ?? null) === 'admin' && \Illuminate\Support\Facades\Route::has('vouchers.enable'))
                    <form method="POST" action="{{ route('vouchers.enable', $v) }}" style="display:inline-flex">
                      @csrf @method('PATCH')
                      <button type="submit" class="icon-btn" title="Aktifkan kembali" style="color:var(--ok)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                    </form>
                  @endif
                  @if(in_array($v->status, ['ready', 'active']))
                    <form method="POST" action="{{ route('vouchers.disable', $v) }}" style="display:inline-flex">
                      @csrf @method('PATCH')
                      <button type="submit" class="icon-btn" title="Nonaktifkan" style="color:var(--warn)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                      </button>
                    </form>
                  @endif
                  @if((auth()->user()->role ?? null) === 'admin' && \Illuminate\Support\Facades\Route::has('vouchers.destroy'))
                    <form method="POST" action="{{ route('vouchers.destroy', $v) }}" style="display:inline-flex"
                          onsubmit="return confirm('Hapus voucher {{ $v->code }}?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="icon-btn" title="Hapus" style="color:var(--err)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="padding: 56px 0; text-align:center; color:var(--text-2)">
                <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px">
                  <div style="width:40px;height:40px;border-radius:12px;background:var(--bg-mute);color:var(--text-3);display:grid;place-items:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9.5V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2.5a2.5 2.5 0 0 0 0 5V17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2.5a2.5 2.5 0 0 0 0-5z"/></svg>
                  </div>
                  <div style="font-weight:600;color:var(--text)">
                    @if($search || $status || $type) Tidak ada voucher yang cocok @else Belum ada voucher @endif
                  </div>
                  @if($search || $status || $type)
                    <a href="{{ route('vouchers.index') }}" style="color:var(--brand-3);font-weight:600">Reset filter →</a>
                  @else
                    <a href="{{ route('vouchers.create') }}" style="color:var(--brand-3);font-weight:600">Generate voucher pertama →</a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($vouchers->hasPages())
      <div style="padding: 12px var(--pad-card); border-top: 1px solid var(--border);">
        {{ $vouchers->withQueryString()->links() }}
      </div>
    @endif
  </div>

@endsection

@push('scripts')
<script>
(function () {
    var checkAll   = document.getElementById('check-all');
    var checkboxes = document.querySelectorAll('.voucher-checkbox');
    var btnPrint   = document.getElementById('btn-print-selected');
    var btnLabel   = document.getElementById('btn-print-label');
    var banner     = document.getElementById('select-all-banner');
    var bannerText = document.getElementById('banner-text');
    var btnAllPages= document.getElementById('btn-select-all-pages');

    var totalAll   = {{ $vouchers->total() }};
    var pageCount  = {{ $vouchers->count() }};
    var hasPages   = totalAll > pageCount;

    var filterParams = new URLSearchParams({
        @if($status) status: '{{ $status }}', @endif
        @if($type)   type:   '{{ $type }}',   @endif
        @if($search) search: '{{ $search }}', @endif
    }).toString();

    var allPagesSelected = false;

    function updateUI() {
        var checked = document.querySelectorAll('.voucher-checkbox:checked').length;
        var count = allPagesSelected ? totalAll : checked;
        if (count > 0) {
            btnPrint.classList.remove('hidden');
            btnLabel.textContent = 'Print Terpilih (' + count + ')';
        } else {
            btnPrint.classList.add('hidden');
        }
        checkAll.indeterminate = !allPagesSelected && checked > 0 && checked < pageCount;
        checkAll.checked = allPagesSelected || (checked === pageCount && pageCount > 0);
        if (allPagesSelected) {
            banner.classList.remove('hidden');
            bannerText.textContent = 'Semua ' + totalAll + ' voucher dipilih.';
            btnAllPages.textContent = 'Batalkan';
        } else if (checked === pageCount && hasPages) {
            banner.classList.remove('hidden');
            bannerText.textContent = pageCount + ' voucher di halaman ini dipilih.';
            btnAllPages.textContent = 'Pilih semua ' + totalAll + ' hasil →';
        } else {
            banner.classList.add('hidden');
            allPagesSelected = false;
        }
    }

    if (checkAll) checkAll.addEventListener('change', function () {
        allPagesSelected = false;
        checkboxes.forEach(function (cb) { cb.checked = checkAll.checked; });
        updateUI();
    });
    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', function () { allPagesSelected = false; updateUI(); });
    });
    if (btnAllPages) btnAllPages.addEventListener('click', function () { allPagesSelected = !allPagesSelected; updateUI(); });

    window.printSelected = function () {
        var printUrl = '{{ route('vouchers.print') }}';
        if (allPagesSelected) {
            var params = 'print_all=1' + (filterParams ? '&' + filterParams : '');
            window.open(printUrl + '?' + params, '_blank');
        } else {
            var ids = Array.from(document.querySelectorAll('.voucher-checkbox:checked')).map(function (cb) { return cb.value; }).join(',');
            if (!ids) return;
            window.open(printUrl + '?ids=' + ids, '_blank');
        }
    };
})();
</script>
@endpush
