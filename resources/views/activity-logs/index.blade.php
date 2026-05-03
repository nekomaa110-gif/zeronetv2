@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')

@section('content')

  <header class="page-head">
    <div>
      <h2>Log Aktivitas</h2>
      <p>Riwayat semua aksi yang dilakukan oleh admin dan operator panel ZeroNet.</p>
    </div>
  </header>

  <div class="card">
    <div style="padding: 14px var(--pad-card); border-bottom: 1px solid var(--border);">
      <form method="GET" action="{{ route('activity-logs.index') }}"
            data-live-target="#activity-logs-results"
            style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">

        <div class="input-group" style="width:240px;position:relative;">
          <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" value="{{ $search }}" placeholder="Cari user atau aksi..." data-live-search class="input">
          @php $hasFilter = $search || $dateFrom || $dateTo; @endphp
          <button type="button" data-live-reset title="Reset semua filter"
                  class="icon-btn {{ $hasFilter ? '' : 'hidden' }}"
                  style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:26px;height:26px;border:0;background:transparent">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <input type="date" name="date_from" value="{{ $dateFrom }}" data-live-submit class="input" style="width:auto" title="Dari tanggal">
        <span style="color:var(--text-3)">—</span>
        <input type="date" name="date_to" value="{{ $dateTo }}" data-live-submit class="input" style="width:auto" title="Sampai tanggal">
      </form>
    </div>

    <div id="activity-logs-results" data-live-results>
      @include('activity-logs._results')
    </div>
  </div>

@endsection
