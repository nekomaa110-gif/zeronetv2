/* =========================================================================
   ZeroNet v2 — Shell behavior (theme, sidebar, tweaks)
   Loaded by every page so user preferences persist via localStorage.
   ========================================================================= */

(function () {
  'use strict';

  const LS_KEY = 'zeronet-prefs-v2';
  const DEFAULTS = {
    theme: 'light',         // 'light' | 'dark'
    sidebar: 'full',        // 'full' | 'icon' | 'floating'
    density: 'cozy',        // 'compact' | 'cozy' | 'comfortable' (cozy = default vars)
    cardStyle: 'outlined',  // 'flat' | 'outlined' | 'elevated'
    accent: 'emerald',      // 'emerald' | 'violet' | 'orange' | 'rose' | 'indigo'
  };

  // --- Load + apply preferences as early as possible -----------------------
  function loadPrefs() {
    try {
      const raw = localStorage.getItem(LS_KEY);
      if (raw) return { ...DEFAULTS, ...JSON.parse(raw) };
      // Migrasi pertama-kali: hormati `localStorage.theme` lama (light/dark) dari panel sebelumnya.
      const legacy = localStorage.getItem('theme');
      const seed = { ...DEFAULTS };
      if (legacy === 'dark' || legacy === 'light') seed.theme = legacy;
      else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) seed.theme = 'dark';
      return seed;
    } catch { return { ...DEFAULTS }; }
  }
  function savePrefs(p) {
    try { localStorage.setItem(LS_KEY, JSON.stringify(p)); } catch {}
  }
  function applyPrefs(p) {
    const r = document.documentElement;
    r.dataset.theme = p.theme;
    // Sync .dark class + legacy `theme` key for Tailwind dark: + Chart.js code yang masih cek classList.
    r.classList.toggle('dark', p.theme === 'dark');
    try { localStorage.setItem('theme', p.theme); } catch {}
    r.dataset.sidebar = p.sidebar;
    if (p.density === 'cozy') r.removeAttribute('data-density');
    else r.dataset.density = p.density;
    r.dataset.cardStyle = p.cardStyle;
    r.dataset.accent = p.accent;
  }

  const Prefs = loadPrefs();
  applyPrefs(Prefs);
  window.ZeroNet = window.ZeroNet || {};
  window.ZeroNet.prefs = Prefs;
  window.ZeroNet.setPref = function (key, value) {
    Prefs[key] = value;
    applyPrefs(Prefs);
    savePrefs(Prefs);
    document.dispatchEvent(new CustomEvent('zeronet:prefs', { detail: { key, value, prefs: Prefs } }));
  };
  window.ZeroNet.getPref = (k) => Prefs[k];

  // --- Wire DOM after load --------------------------------------------------
  function wire() {
    // Theme toggle button(s)
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const next = (document.documentElement.dataset.theme === 'dark') ? 'light' : 'dark';
        window.ZeroNet.setPref('theme', next);
        updateThemeIcons();
      });
    });
    updateThemeIcons();

    // User chip dropdown
    document.querySelectorAll('[data-user-menu]').forEach((chip) => {
      chip.addEventListener('click', (e) => {
        e.stopPropagation();
        const menu = chip.nextElementSibling;
        if (!menu) return;
        menu.classList.toggle('open');
      });
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.user-pop.open').forEach((m) => m.classList.remove('open'));
    });

    // Sidebar toggle (only meaningful in 'floating' mode — exposes hamburger)
    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const sb = document.querySelector('.sidebar');
        if (sb) sb.classList.toggle('is-open');
      });
    });
  }

  function updateThemeIcons() {
    const isDark = document.documentElement.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
      el.innerHTML = isDark
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wire);
  } else {
    wire();
  }
})();
