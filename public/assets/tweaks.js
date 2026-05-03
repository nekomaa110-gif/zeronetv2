/* =========================================================================
   ZeroNet v2 — Tweaks panel
   Floating button (right-bottom) opens a small popover for runtime tweaks:
   accent color, density, sidebar variant, card style.
   Persists via ZeroNet.setPref → localStorage.
   ========================================================================= */

window.mountTweaks = function mountTweaks() {
  if (document.getElementById('tw-fab')) return;

  // FAB
  const fab = document.createElement('button');
  fab.id = 'tw-fab';
  fab.className = 'tweaks-fab';
  fab.title = 'Tweaks';
  fab.setAttribute('aria-label', 'Buka panel tweaks');
  fab.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/></svg>`;

  // Panel
  const panel = document.createElement('div');
  panel.id = 'tw-panel';
  panel.innerHTML = `
    <div class="tw-head">
      <b>Tweaks</b>
      <button class="icon-btn" id="tw-close" aria-label="Tutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="tw-body">
      <!-- Accent color -->
      <div class="tw-row">
        <div class="tw-row-l">Accent</div>
        <div class="tw-swatches" data-pref="accent">
          ${['emerald','indigo','violet','orange','rose'].map(a => `<button class="tw-sw" data-val="${a}" data-accent="${a}" style="background:var(--brand-grad)" title="${a}"></button>`).join('')}
        </div>
      </div>

      <!-- Density -->
      <div class="tw-row">
        <div class="tw-row-l">Density</div>
        <div class="tw-seg" data-pref="density">
          <button data-val="compact">Compact</button>
          <button data-val="cozy">Cozy</button>
          <button data-val="comfortable">Comfortable</button>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="tw-row">
        <div class="tw-row-l">Sidebar</div>
        <div class="tw-seg" data-pref="sidebar">
          <button data-val="full">Full</button>
          <button data-val="icon">Icon</button>
          <button data-val="floating">Floating</button>
        </div>
      </div>

      <!-- Card style -->
      <div class="tw-row">
        <div class="tw-row-l">Card style</div>
        <div class="tw-seg" data-pref="cardStyle">
          <button data-val="flat">Flat</button>
          <button data-val="outlined">Outlined</button>
          <button data-val="elevated">Elevated</button>
        </div>
      </div>

      <!-- Theme -->
      <div class="tw-row">
        <div class="tw-row-l">Tema</div>
        <div class="tw-seg" data-pref="theme">
          <button data-val="light">Light</button>
          <button data-val="dark">Dark</button>
        </div>
      </div>
    </div>
  `;

  // Styles for the panel itself
  const css = document.createElement('style');
  css.textContent = `
    #tw-panel {
      position: fixed; right: 20px; bottom: 80px; z-index: 80;
      width: 280px; background: var(--bg-elev); color: var(--text);
      border: 1px solid var(--border); border-radius: var(--r-lg);
      box-shadow: var(--shadow-lg);
      transform: translateY(8px) scale(.96); opacity: 0; pointer-events: none;
      transition: transform .18s ease, opacity .18s ease;
    }
    #tw-panel.open { transform: translateY(0) scale(1); opacity: 1; pointer-events: auto; }
    #tw-panel .tw-head { display: flex; align-items: center; padding: 12px 14px; border-bottom: 1px solid var(--border); }
    #tw-panel .tw-head b { flex: 1; font-size: 14px; }
    #tw-panel .tw-body { padding: 10px 14px 14px; display: flex; flex-direction: column; gap: 14px; }
    #tw-panel .tw-row-l { font-size: 11.5px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .07em; margin-bottom: 6px; }
    #tw-panel .tw-seg { display: flex; gap: 4px; padding: 4px; background: var(--bg-mute); border-radius: var(--r-md); border: 1px solid var(--border); }
    #tw-panel .tw-seg button { flex: 1; background: transparent; border: 0; padding: 5px 6px; border-radius: 6px; color: var(--text-2); font-size: 12px; font-weight: 500; }
    #tw-panel .tw-seg button.on { background: var(--bg-elev); color: var(--text); box-shadow: var(--shadow-sm); font-weight: 600; }
    #tw-panel .tw-swatches { display: flex; gap: 8px; flex-wrap: wrap; }
    #tw-panel .tw-sw { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; }
    #tw-panel .tw-sw.on { border-color: var(--text); box-shadow: 0 0 0 2px var(--bg-elev) inset; }
  `;
  document.head.appendChild(css);
  document.body.append(fab, panel);

  // Open/close
  let open = false;
  const setOpen = (v) => { open = v; panel.classList.toggle('open', v); };
  fab.addEventListener('click', () => setOpen(!open));
  panel.querySelector('#tw-close').addEventListener('click', () => setOpen(false));
  document.addEventListener('click', (e) => {
    if (!open) return;
    if (panel.contains(e.target) || fab.contains(e.target)) return;
    setOpen(false);
  });

  // Wire all controls — segmented + swatches share same data-pref pattern
  function refresh() {
    panel.querySelectorAll('[data-pref]').forEach((group) => {
      const key = group.dataset.pref;
      const cur = window.ZeroNet.getPref(key);
      group.querySelectorAll('button').forEach((b) => b.classList.toggle('on', b.dataset.val === cur));
    });
  }
  panel.querySelectorAll('[data-pref]').forEach((group) => {
    const key = group.dataset.pref;
    group.querySelectorAll('button').forEach((b) => {
      b.addEventListener('click', () => {
        window.ZeroNet.setPref(key, b.dataset.val);
        refresh();
      });
    });
  });
  refresh();
  document.addEventListener('zeronet:prefs', refresh);
};
