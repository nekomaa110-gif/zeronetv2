@php $u = auth()->user(); @endphp
<header class="topbar">
  <button class="icon-btn" data-sidebar-toggle aria-label="Toggle sidebar" style="display:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <h1>@yield('page-title', $title ?? 'Dashboard')</h1>
  <div class="topbar-spacer"></div>

  <button class="icon-btn" data-theme-toggle aria-label="Toggle tema" title="Toggle tema">
    <span data-theme-icon style="display:contents"></span>
  </button>

  <div style="position:relative">
    <button class="user-chip" data-user-menu type="button">
      <span class="avatar sm">{{ strtoupper(substr($u->name ?? $u->username ?? 'A', 0, 1)) }}</span>
      <span class="user-chip-name">{{ $u->name ?? $u->username ?? 'guest' }}</span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="user-pop">
      <a href="{{ route('profile.edit') }}" class="user-pop-item">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profil Saya
      </a>
      <a href="{{ route('two-factor.setup') }}" class="user-pop-item">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Two-Factor Auth
      </a>
      <div class="user-pop-sep"></div>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="user-pop-item danger">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Keluar
        </button>
      </form>
    </div>
  </div>
</header>

<style>
  .user-pop {
    position: absolute; right: 0; top: calc(100% + 6px); z-index: 60;
    min-width: 200px;
    background: var(--bg-elev); border: 1px solid var(--border); border-radius: var(--r-md);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px); opacity: 0; pointer-events: none;
    transition: transform .15s ease, opacity .15s ease;
    padding: 4px;
  }
  .user-pop.open { transform: none; opacity: 1; pointer-events: auto; }
  .user-pop-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 10px; border-radius: var(--r-sm);
    color: var(--text); font-size: 13px; font-weight: 500;
    background: none; border: 0; width: 100%; text-align: left; cursor: pointer;
  }
  .user-pop-item:hover { background: var(--bg-mute); }
  .user-pop-item.danger { color: var(--err); }
  .user-pop-item.danger:hover { background: color-mix(in srgb, var(--err) 8%, transparent); }
  .user-pop-sep { height:1px; background: var(--border); margin: 4px 2px; }
  @media (max-width: 720px) { .user-chip-name { display: none; } }
</style>
