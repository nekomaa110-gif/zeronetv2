@extends('layouts.app')

@section('title', 'Log User Hotspot')
@section('page-title', 'Log User Hotspot')

@section('content')

  @php
    $hasFilter = (bool)($search || $status || $dateFrom || $dateTo);
    $liveMode  = !$hasFilter && $logs->currentPage() === 1;
  @endphp

  <header class="page-head">
    <div>
      <h2>Log User Hotspot</h2>
      <p>Riwayat autentikasi user hotspot ZeroNet.</p>
    </div>
    @if($liveMode)
      <div class="head-actions"><span class="live">Live</span></div>
    @endif
  </header>

  <div class="card">

    <div style="padding: 14px var(--pad-card); border-bottom: 1px solid var(--border);">
      <form method="GET" action="{{ route('hotspot-logs.index') }}"
            style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">

        <div class="input-group" style="width:220px">
          <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $search }}" placeholder="Cari username..." data-live-search class="input">
        </div>

        <input type="date" name="date_from" value="{{ $dateFrom }}" data-live-submit class="input" style="width:auto" title="Dari tanggal">
        <span style="color:var(--text-3)">—</span>
        <input type="date" name="date_to" value="{{ $dateTo }}" data-live-submit class="input" style="width:auto" title="Sampai tanggal">

        <select name="status" data-live-submit class="select" style="width:auto;min-width:140px">
          <option value="">Semua Status</option>
          <option value="success" @selected($status === 'success')>Berhasil</option>
          <option value="failed"  @selected($status === 'failed')>Gagal</option>
        </select>

        @if($hasFilter)
          <a href="{{ route('hotspot-logs.index') }}" class="btn btn-ghost btn-sm">Reset</a>
        @endif
      </form>
    </div>

    <div class="tbl-wrap"
         @if($liveMode)
         x-data="{
           lastId: {{ $logs->first()?->id ?? 0 }},
           async poll() {
             if (!this.lastId) return;
             try {
               const { data } = await axios.get('{{ route('hotspot-logs.poll') }}', { params: { after: this.lastId } });
               if (data.count > 0) {
                 this.$refs.tbody.insertAdjacentHTML('afterbegin', data.html);
                 this.lastId = data.max_id;
               }
             } catch(e) {}
           },
           init() { setInterval(() => this.poll(), 15000); }
         }"
         @endif>
      <table class="tbl">
        <thead>
          <tr>
            <th class="ix">#</th>
            <th>Username</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th style="text-align:center">Status</th>
            <th>Keterangan</th>
            <th>NAS / IP Klien</th>
          </tr>
        </thead>
        <tbody @if($liveMode) x-ref="tbody" @endif>
          @forelse($logs as $log)
            @include('hotspot-logs._row', [
              'log'           => $log,
              'rejectReasons' => $rejectReasons,
              'userIps'       => $userIps,
              'rowNumber'     => $logs->firstItem() + $loop->index,
              'isNew'         => false,
            ])
          @empty
            <tr>
              <td colspan="7" style="padding:56px 0;text-align:center;color:var(--text-2)">
                <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px">
                  <div style="width:40px;height:40px;border-radius:12px;background:var(--bg-mute);color:var(--text-3);display:grid;place-items:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                  </div>
                  <div style="font-weight:600;color:var(--text)">
                    @if($hasFilter) Tidak ada log yang cocok @else Belum ada log autentikasi @endif
                  </div>
                  @if($hasFilter)
                    <a href="{{ route('hotspot-logs.index') }}" style="color:var(--brand-3);font-weight:600">Reset filter →</a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($logs->hasPages())
      <div style="padding:12px var(--pad-card);border-top:1px solid var(--border)">{{ $logs->withQueryString()->links() }}</div>
    @endif
  </div>

@endsection

@push('scripts')
<style>
  .log-row-new { animation: logRowFade 5s ease-in-out forwards; }
  @keyframes logRowFade {
    0%, 60% { background-color: color-mix(in srgb, var(--ok) 12%, transparent); }
    100%    { background-color: transparent; }
  }
</style>
@endpush
