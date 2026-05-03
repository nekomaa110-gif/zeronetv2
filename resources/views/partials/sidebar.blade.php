@php
    $u = auth()->user();
    $isAdmin = $u && method_exists($u, 'isAdmin') ? $u->isAdmin() : ($u?->role === 'admin');

    $isActive = function (string ...$routes): bool {
        foreach ($routes as $r) {
            if (request()->routeIs($r)) return true;
        }
        return false;
    };
@endphp

<aside class="sidebar" aria-label="Navigasi utama">
  <a class="sidebar-brand" href="{{ route('dashboard') }}">
    <span class="brand-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14 0"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M8.5 16.43a6 6 0 0 1 7 0"/><circle cx="12" cy="20" r="1.2" fill="currentColor"/></svg>
    </span>
    <span class="brand-text"><b>ZeroNet</b><span>Hotspot Manager</span></span>
  </a>

  <nav class="nav">
    <a class="nav-item {{ $isActive('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12 12 3l9 9"/><path d="M5 10v10h14V10"/></svg>
      <span class="nav-label">Dashboard</span>
    </a>

    <a class="nav-item {{ $isActive('user-hotspot.*') ? 'active' : '' }}" href="{{ route('user-hotspot.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      <span class="nav-label">User Hotspot</span>
    </a>

    <a class="nav-item {{ $isActive('packages.*') ? 'active' : '' }}" href="{{ route('packages.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
      <span class="nav-label">Paket / Profile</span>
    </a>

    <a class="nav-item {{ $isActive('vouchers.*') ? 'active' : '' }}" href="{{ route('vouchers.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9.5V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2.5a2.5 2.5 0 0 0 0 5V17a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-2.5a2.5 2.5 0 0 0 0-5z"/></svg>
      <span class="nav-label">Voucher</span>
    </a>

    <a class="nav-item {{ $isActive('routers.*') ? 'active' : '' }}" href="{{ route('routers.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M15 10v4"/><path d="M17.84 7.17a4 4 0 0 0-5.66 0"/></svg>
      <span class="nav-label">Manajemen Router</span>
    </a>

    @if ($isAdmin)
      <a class="nav-item {{ $isActive('whatsapp.*') ? 'active' : '' }}" href="{{ route('whatsapp.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        <span class="nav-label">WhatsApp Gateway</span>
      </a>
    @endif

    <div class="nav-section">Logs</div>
    <a class="nav-item {{ $isActive('hotspot-logs.*') ? 'active' : '' }}" href="{{ route('hotspot-logs.index') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
      <span class="nav-label">Log Hotspot</span>
    </a>
    @if ($isAdmin)
      <a class="nav-item {{ $isActive('activity-logs.*') ? 'active' : '' }}" href="{{ route('activity-logs.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span class="nav-label">Log Aktivitas</span>
      </a>
    @endif
  </nav>

  <div class="sidebar-user">
    <div class="avatar">{{ strtoupper(substr($u->name ?? $u->username ?? 'A', 0, 1)) }}</div>
    <div class="user-meta">
      <b>{{ $u->name ?? $u->username ?? 'guest' }}</b>
      <span>{{ ucfirst($u->role ?? 'admin') }}</span>
    </div>
  </div>
</aside>
