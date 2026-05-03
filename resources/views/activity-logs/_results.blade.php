{{-- Partial: tabel + pagination log aktivitas. Dipakai oleh full view dan response AJAX. --}}
<div class="tbl-wrap">
  <table class="tbl">
    <thead>
      <tr>
        <th class="ix">#</th>
        <th>Waktu</th>
        <th>User Panel</th>
        <th style="text-align:center">Role</th>
        <th>Aksi</th>
        <th>Deskripsi</th>
        <th>IP Address</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
        @php
          // Map aksi → label + tone (kelas badge baru: ok/warn/err/info/brand/empty)
          $actionMap = [
            'create_user'      => ['label' => 'Tambah User',     'tone' => 'ok'],
            'update_user'      => ['label' => 'Edit User',       'tone' => 'info'],
            'delete_user'      => ['label' => 'Hapus User',      'tone' => 'err'],
            'toggle_user'      => ['label' => 'Toggle User',     'tone' => 'warn'],
            'create_package'   => ['label' => 'Tambah Paket',    'tone' => 'ok'],
            'update_package'   => ['label' => 'Edit Paket',      'tone' => 'info'],
            'delete_package'   => ['label' => 'Hapus Paket',     'tone' => 'err'],
            'toggle_package'   => ['label' => 'Toggle Paket',    'tone' => 'warn'],
            'update_profile'   => ['label' => 'Edit Profil',     'tone' => 'info'],
            'update_password'  => ['label' => 'Ganti Password',  'tone' => 'brand'],
            'generate_voucher' => ['label' => 'Generate Voucher','tone' => 'ok'],
            'disable_voucher'  => ['label' => 'Nonaktif Voucher','tone' => 'warn'],
            'enable_voucher'   => ['label' => 'Aktifkan Voucher','tone' => 'info'],
            'delete_voucher'   => ['label' => 'Hapus Voucher',   'tone' => 'err'],
            'login'            => ['label' => 'Login',           'tone' => 'ok'],
            'logout'           => ['label' => 'Logout',          'tone' => ''],
            'login_failed'     => ['label' => 'Login Gagal',     'tone' => 'err'],
            'wa_send'          => ['label' => 'Kirim WA',        'tone' => 'info'],
          ];
          $action   = $actionMap[$log->action] ?? ['label' => $log->action, 'tone' => ''];
          $isSystem = is_null($log->user_id);
        @endphp
        <tr>
          <td class="ix">{{ $logs->firstItem() + $loop->index }}</td>

          <td style="font-size:12px;color:var(--text-2);white-space:nowrap">
            <div>{{ $log->created_at->format('d M Y') }}</div>
            <div class="mono" style="color:var(--text-3)">{{ $log->created_at->format('H:i:s') }}</div>
          </td>

          <td>
            @if($isSystem)
              <div style="font-weight:500;color:var(--text-2);font-style:italic">Sistem</div>
              <div style="font-size:11.5px;color:var(--text-3)">scheduler</div>
            @else
              <div style="font-weight:600">{{ $log->user?->name ?? '—' }}</div>
              <div style="font-size:11.5px;color:var(--text-3)">{{ $log->user?->username ?? '' }}</div>
            @endif
          </td>

          <td style="text-align:center">
            @if($isSystem)
              <span class="badge">Sistem</span>
            @elseif($log->user?->role === 'admin')
              <span class="badge brand">Admin</span>
            @elseif($log->user?->role === 'operator')
              <span class="badge info">Operator</span>
            @else
              <span style="color:var(--text-3)">—</span>
            @endif
          </td>

          <td>
            <span class="badge {{ $action['tone'] }}">{{ $action['label'] }}</span>
          </td>

          <td style="font-size:12px;color:var(--text-2);max-width:280px">
            {{ $log->description ?: '—' }}
          </td>

          <td class="mono" style="font-size:12px;color:var(--text-2)">
            {{ $log->ip_address ?: '—' }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="padding:56px 0;text-align:center;color:var(--text-2)">
            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px">
              <div style="width:40px;height:40px;border-radius:12px;background:var(--bg-mute);color:var(--text-3);display:grid;place-items:center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <div style="font-weight:600;color:var(--text)">
                @if($search || $dateFrom || $dateTo) Tidak ada log yang cocok dengan filter @else Belum ada log aktivitas @endif
              </div>
              @if($search || $dateFrom || $dateTo)
                <a href="{{ route('activity-logs.index') }}" style="color:var(--brand-3);font-weight:600">Reset filter →</a>
              @endif
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($logs->hasPages())
  <div style="padding:12px var(--pad-card);border-top:1px solid var(--border)">{{ $logs->withQueryString()->links() }}</div>
@endif
