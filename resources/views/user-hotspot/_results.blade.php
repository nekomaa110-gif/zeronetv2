{{-- Partial: tabel + pagination user hotspot. Dipakai oleh full view dan response AJAX. --}}

@php
    $avPalette = ['#7C3AED','#EC4899','#F97316','#10B981','#0EA5E9','#6366F1','#F59E0B','#06B6D4'];

    // Batch fetch sesi RADIUS terbaru (untuk drawer quick-view) — read-only, 1 query saja.
    $usernamesOnPage = collect($users->items())->pluck('username')->all();
    $latestSessions = collect();
    if (! empty($usernamesOnPage)) {
        $sub = \Illuminate\Support\Facades\DB::table('radacct')
            ->whereIn('username', $usernamesOnPage)
            ->select('username', \Illuminate\Support\Facades\DB::raw('MAX(radacctid) as max_id'))
            ->groupBy('username');

        $latestSessions = \Illuminate\Support\Facades\DB::table('radacct as a')
            ->joinSub($sub, 'b', fn($j) => $j->on('a.username', '=', 'b.username')->on('a.radacctid', '=', 'b.max_id'))
            ->select(
                'a.username',
                'a.framedipaddress',
                'a.callingstationid',
                'a.acctstarttime',
                'a.acctstoptime',
                'a.acctinputoctets',
                'a.acctoutputoctets',
                'a.nasipaddress',
            )
            ->get()
            ->keyBy('username');
    }

    $fmtBytes = function ($n) {
        $n = (int) ($n ?? 0);
        if ($n >= 1073741824) return number_format($n / 1073741824, 2) . ' GB';
        if ($n >= 1048576)    return number_format($n / 1048576, 1) . ' MB';
        if ($n >= 1024)       return number_format($n / 1024, 1) . ' KB';
        return $n . ' B';
    };
@endphp

<div class="tbl-wrap">
  <table class="tbl">
    <thead>
      <tr>
        <th class="ix">#</th>
        <th>Username</th>
        <th>Profil / Paket</th>
        <th>Expire</th>
        <th>Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        @php
          $expired    = $user['expiry'] !== '-' && \Carbon\Carbon::parse($user['expiry'])->isPast();
          $isDisabled = ! $user['active'];
          $isExpired  = $user['active'] && $expired;
          $isActive   = $user['active'] && ! $expired;

          $avHash  = crc32($user['username']);
          $avColor = $avPalette[$avHash % count($avPalette)];
          $initial = mb_strtoupper(mb_substr($user['username'], 0, 1));
          $userKey = strtolower($user['username']);

          $gHasValue = $user['group'] !== '-' && $user['group'] !== '';

          $sisa = '—'; $sisaTone = 'var(--text-3)';
          if ($user['expiry'] !== '-') {
              $exp = \Carbon\Carbon::parse($user['expiry']);
              $diffDays = now()->diffInDays($exp, false);
              if ($diffDays < 0)        { $sisa = abs(round($diffDays)) . ' hr lalu'; $sisaTone = 'var(--err)'; }
              elseif ($diffDays <= 3)   { $sisa = round($diffDays) . ' hr lagi';      $sisaTone = 'var(--warn)'; }
              elseif ($diffDays <= 14)  { $sisa = round($diffDays) . ' hr lagi';      $sisaTone = 'var(--ok)'; }
              else                       { $sisa = round($diffDays) . ' hr lagi';      $sisaTone = 'var(--text-3)'; }
          }

          // Status untuk drawer (lowercase canonical)
          $statusKey = $isDisabled ? 'inactive' : ($isExpired ? 'expired' : 'active');

          // Sesi terakhir dari radacct (untuk drawer)
          $sess = $latestSessions->get($user['username']);
          $sessIp     = $sess?->framedipaddress ?: '';
          $sessMac    = $sess?->callingstationid ?: '';
          $sessRx     = $sess ? $fmtBytes($sess->acctinputoctets)  : '';
          $sessTx     = $sess ? $fmtBytes($sess->acctoutputoctets) : '';
          $sessOnline = $sess && empty($sess->acctstoptime);
          $sessLast   = $sess?->acctstarttime
              ? \Carbon\Carbon::parse($sess->acctstarttime)->format('d M Y H:i')
              : '';
        @endphp
        <tr class="user-row"
            data-username="{{ $user['username'] }}"
            data-paket="{{ $gHasValue ? $user['group'] : '— Tidak ada —' }}"
            data-expire="{{ $user['expiry'] !== '-' ? $user['expiry'] : 'Tidak ada' }}"
            data-remain="{{ $sisa }}"
            data-status="{{ $statusKey }}"
            data-avatar-bg="{{ $avColor }}"
            data-ip="{{ $sessIp }}"
            data-mac="{{ $sessMac }}"
            data-rx="{{ $sessRx }}"
            data-tx="{{ $sessTx }}"
            data-last-login="{{ $sessLast }}"
            data-online="{{ $sessOnline ? '1' : '0' }}">
          <td class="ix">{{ $users->firstItem() + $loop->index }}</td>

          <td>
            <div class="user-cell">
              <div class="avatar" style="background:{{ $avColor }}">{{ $initial }}</div>
              <div class="um">
                <b>{{ $user['username'] }}</b>
                <span>{{ '@'.$userKey }}</span>
              </div>
            </div>
          </td>

          <td>
            @if ($gHasValue)
              <span class="badge brand">{{ $user['group'] }}</span>
            @else
              <span style="color:var(--text-3)">—</span>
            @endif
          </td>

          <td>
            @if ($user['expiry'] !== '-')
              <div class="mono" style="font-weight:600">{{ $user['expiry'] }}</div>
              <div style="font-size:11.5px;color:{{ $sisaTone }}">{{ $sisa }}</div>
            @else
              <span style="color:var(--text-3)">—</span>
            @endif
          </td>

          <td>
            @if ($isDisabled)
              <span class="badge warn">Nonaktif</span>
            @elseif ($isExpired)
              <span class="badge err">Expired</span>
            @else
              <span class="badge ok">Aktif</span>
            @endif
          </td>

          <td>
            <div class="tbl-actions">
              <a href="{{ route('user-hotspot.edit', $user['username']) }}" class="icon-btn" title="Edit"
                 onclick="event.stopPropagation()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>

              @if (! $isActive)
                <form method="POST" action="{{ route('user-hotspot.toggle', $user['username']) }}" style="display:inline-flex" onclick="event.stopPropagation()">
                  @csrf @method('PATCH')
                  <button type="submit" class="icon-btn" title="{{ $user['active'] ? 'Nonaktifkan' : 'Aktifkan' }}"
                          style="color:{{ $user['active'] ? 'var(--warn)' : 'var(--ok)' }}">
                    @if ($user['active'])
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                    @else
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                  </button>
                </form>
                <form method="POST" action="{{ route('user-hotspot.destroy', $user['username']) }}" style="display:inline-flex"
                      onclick="event.stopPropagation()"
                      onsubmit="return confirm('Hapus user {{ addslashes($user['username']) }}? Tindakan ini tidak bisa dibatalkan.');">
                  @csrf @method('DELETE')
                  <button type="submit" class="icon-btn" title="Hapus" style="color:var(--err)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="padding:56px 0;text-align:center;color:var(--text-2)">
            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px">
              <div style="width:40px;height:40px;border-radius:12px;background:var(--bg-mute);color:var(--text-3);display:grid;place-items:center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>
              <div style="font-weight:600;color:var(--text)">
                @if ($search || $group || $status) Tidak ada user yang cocok @else Belum ada user hotspot @endif
              </div>
              <div style="font-size:12.5px">
                @if ($search || $group || $status)
                  Coba ubah pencarian atau filter
                @else
                  <a href="{{ route('user-hotspot.create') }}" style="color:var(--brand-3);font-weight:600">Tambah sekarang →</a>
                @endif
              </div>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($users->total() > 0)
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px var(--pad-card);border-top:1px solid var(--border);font-size:12.5px;color:var(--text-2)">
    <span>
      Menampilkan
      <strong style="color:var(--text)" class="mono">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong>
      dari
      <strong style="color:var(--text)" class="mono">{{ $users->total() }}</strong>
      user
    </span>
    @if ($users->hasPages())
      <div>{{ $users->withQueryString()->links() }}</div>
    @endif
  </div>
@endif
