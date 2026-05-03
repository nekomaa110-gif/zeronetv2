@extends('layouts.app')

@section('title', 'Generate Voucher')
@section('page-title', 'Generate Voucher')

@section('content')

  <header class="page-head">
    <div>
      <h2>Generate Voucher</h2>
      <p>Buat voucher dalam jumlah banyak — kode auto, sinkron ke RADIUS.</p>
    </div>
    <div class="head-actions">
      <a class="btn btn-ghost" href="{{ route('vouchers.index') }}">← Kembali</a>
    </div>
  </header>

  <form method="POST" action="{{ route('vouchers.store') }}"
        x-data="voucherForm({{ \Illuminate\Support\Js::from($types) }}, {{ \Illuminate\Support\Js::from($packages->map(fn($p) => ['id' => $p->id, 'name' => $p->groupname, 'desc' => $p->description])) }})"
        style="display:grid;grid-template-columns:1.4fr 1fr;gap:18px;align-items:flex-start">
    @csrf

    <div class="card">
      <div class="card-head">
        <div>
          <h3>Form Generate Voucher</h3>
          <p style="margin:0;color:var(--text-2);font-size:12.5px">Kode dibuat otomatis dan langsung disinkronkan ke RADIUS.</p>
        </div>
      </div>
      <div class="card-pad" style="display:flex;flex-direction:column;gap:18px">

        {{-- Tipe voucher --}}
        <div class="field">
          <label>Tipe Voucher <span class="req">*</span></label>
          <div class="vch-types">
            @foreach($types as $key => $cfg)
              <label class="vch-type" :class="form.type === '{{ $key }}' ? 'selected' : ''">
                <input type="radio" name="type" value="{{ $key }}" x-model="form.type"
                       {{ old('type', '4h') === $key ? 'checked' : '' }}>
                <div class="vch-icon">
                  @if(str_ends_with($key, 'h'))
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  @endif
                </div>
                <b>{{ $cfg['label'] }}</b>
                <span>{{ $cfg['description'] }}</span>
              </label>
            @endforeach
          </div>
          @error('type') <small style="color:var(--err);font-size:12px;margin-top:6px;display:block">{{ $message }}</small> @enderror
        </div>

        {{-- Paket --}}
        <div class="field">
          <label>Paket / Profile <span class="req">*</span></label>
          <select class="select" name="package_id" x-model="form.packageId">
            <option value="">— Pilih Paket —</option>
            @foreach($packages as $pkg)
              <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                {{ $pkg->groupname }}{{ $pkg->description ? ' — ' . $pkg->description : '' }}
              </option>
            @endforeach
          </select>
          @error('package_id') <small style="color:var(--err);font-size:12px;margin-top:4px;display:block">{{ $message }}</small> @enderror
          @if($packages->isEmpty() && \Illuminate\Support\Facades\Route::has('packages.create'))
            <small style="color:var(--warn);font-size:12px;margin-top:4px;display:block">
              Belum ada paket aktif. <a href="{{ route('packages.create') }}" style="text-decoration:underline">Buat paket dahulu →</a>
            </small>
          @endif
        </div>

        {{-- Prefix --}}
        <div class="field">
          <label>Prefix Username <span style="color:var(--text-3);font-weight:400">(opsional)</span></label>
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <input class="input mono" name="prefix" x-model="form.prefix" maxlength="20"
                   placeholder="CONTOH: ZERO" style="flex:0 0 180px;text-transform:uppercase"
                   oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9]/g,'')">
            <span style="color:var(--text-3);font-family:'JetBrains Mono',monospace;font-size:14px">+</span>
            <span class="mono" style="background:var(--bg-mute);border:1px dashed var(--border-strong);border-radius:8px;padding:9px 14px;color:var(--text-3);font-size:13px;letter-spacing:.1em">XXXX</span>
            <span class="mono" style="color:var(--text-3);font-size:12.5px;margin-left:auto">format: PREFIXKODE</span>
          </div>
          @error('prefix') <small style="color:var(--err);font-size:12px;margin-top:6px;display:block">{{ $message }}</small> @enderror
          <small style="color:var(--text-3);font-size:11.5px;margin-top:6px;display:block">
            Jika diisi, username menjadi <span class="mono" style="color:var(--text-2)">PREFIXKODE</span>. Kosongkan untuk format <span class="mono" style="color:var(--text-2)">XXXXXX</span> (6 karakter).
          </small>
        </div>

        {{-- Jumlah --}}
        <div class="field">
          <label>Jumlah Voucher <span class="req">*</span></label>
          <div style="display:flex;align-items:center;gap:10px;max-width:280px">
            <button type="button" class="icon-btn" @click="form.count = Math.max(1, form.count - 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <input class="input mono" name="count" type="number" min="1" max="100" x-model.number="form.count"
                   style="text-align:center;font-size:16px;font-weight:600">
            <button type="button" class="icon-btn" @click="form.count = Math.min(100, form.count + 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
          </div>
          @error('count') <small style="color:var(--err);font-size:12px;margin-top:4px;display:block">{{ $message }}</small> @enderror
          <small style="color:var(--text-3);font-size:11.5px;display:block;margin-top:4px">Maksimal 100 voucher per generate.</small>
        </div>

        {{-- Catatan --}}
        <div class="field">
          <label>Catatan <span style="color:var(--text-3);font-weight:400">(opsional)</span></label>
          <input class="input" name="note" value="{{ old('note') }}" placeholder="Contoh: Batch acara 18 April">
        </div>

        <div style="display:flex;gap:10px;margin-top:6px">
          <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="margin-right:6px;vertical-align:-2px"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Generate Voucher
          </button>
          <a href="{{ route('vouchers.index') }}" class="btn btn-ghost">Batal</a>
        </div>
      </div>
    </div>

    {{-- Sticky preview --}}
    <aside style="display:flex;flex-direction:column;gap:14px;position:sticky;top:84px">
      <h4 style="margin:0;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.08em">Preview Voucher</h4>
      <div class="vch-card">
        <div class="vch-card-top">
          <div>
            <div style="font-size:11px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.1em">ZeroNet WiFi</div>
            <div style="font-size:18px;font-weight:700;margin-top:4px" x-text="typeLabel + (currentPackage ? ' · ' + currentPackage.name : '')"></div>
          </div>
          <span class="brand-mark" style="width:38px;height:38px;background:rgba(255,255,255,.18);box-shadow:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px"><path d="M5 12.55a11 11 0 0 1 14 0"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M8.5 16.43a6 6 0 0 1 7 0"/><circle cx="12" cy="20" r="1.2" fill="currentColor"/></svg>
          </span>
        </div>
        <div class="vch-card-body">
          <div class="vch-code" x-text="previewCode"></div>
          <div class="vch-meta">
            <div><span>Username</span><b class="mono" x-text="(form.prefix || '') + 'XXXX'"></b></div>
            <div><span>Password</span><b class="mono" x-text="(form.prefix || '') + 'XXXX'"></b></div>
          </div>
        </div>
        <div class="vch-card-foot">SSID: <b>ZeroNet</b> · <span x-text="typeFoot"></span></div>
      </div>

      <div class="card card-pad" style="background:var(--bg-mute)">
        <div class="kvp"><span class="k">Akan dibuat</span><span class="v"><b style="font-size:16px" x-text="form.count"></b> voucher</span></div>
        <div class="kvp"><span class="k">Format kode</span><span class="v mono" x-text="form.prefix ? 'PREFIX + 4 acak' : '6 karakter acak'"></span></div>
        <div class="kvp"><span class="k">Sinkronisasi</span><span class="v"><span class="badge ok">RADIUS otomatis</span></span></div>
      </div>
    </aside>
  </form>

  <style>
    .vch-types { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; }
    .vch-type { position:relative; padding:16px; border:1.5px solid var(--border); border-radius:var(--r-md); background:var(--bg-elev); cursor:pointer; transition:all .15s ease; display:flex; flex-direction:column; gap:6px; }
    .vch-type:hover { border-color:var(--border-strong); }
    .vch-type input { position:absolute; opacity:0; pointer-events:none; }
    .vch-type.selected { border-color:var(--brand-3); background:color-mix(in srgb,var(--brand-3) 6%,transparent); box-shadow:var(--ring); }
    .vch-icon { width:30px; height:30px; border-radius:8px; background:color-mix(in srgb,var(--brand-3) 14%,transparent); color:var(--brand-3); display:grid; place-items:center; margin-bottom:4px; }
    .vch-icon svg { width:16px; height:16px; }
    .vch-type b { font-size:14px; font-weight:600; }
    .vch-type span { color:var(--text-2); font-size:11.5px; line-height:1.5; }

    .vch-card { background:var(--brand-grad); color:white; border-radius:var(--r-lg); padding:18px; box-shadow:var(--shadow-lg); position:relative; overflow:hidden; }
    .vch-card::before { content:""; position:absolute; left:-12px; top:50%; transform:translateY(-50%); width:24px; height:24px; border-radius:50%; background:var(--bg); }
    .vch-card::after { content:""; position:absolute; right:-12px; top:50%; transform:translateY(-50%); width:24px; height:24px; border-radius:50%; background:var(--bg); }
    .vch-card-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
    .vch-card-body { background:rgba(255,255,255,.16); border:1px dashed rgba(255,255,255,.4); border-radius:8px; padding:12px; backdrop-filter:blur(8px); }
    .vch-code { font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:700; letter-spacing:.18em; text-align:center; margin-bottom:10px; }
    .vch-meta { display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:11px; }
    .vch-meta span { display:block; color:rgba(255,255,255,.7); margin-bottom:2px; text-transform:uppercase; letter-spacing:.08em; }
    .vch-meta b { font-size:12px; }
    .vch-card-foot { margin-top:12px; font-size:11px; color:rgba(255,255,255,.85); text-align:center; }

    @media (max-width: 880px) {
      form[x-data="voucherForm"] { grid-template-columns: 1fr !important; }
      aside { position: static !important; }
    }
  </style>

@endsection

@push('scripts')
<script>
function voucherForm(typesMap, packagesArr) {
  return {
    typesMap, packagesArr,
    form: {
      type: '{{ old('type', '4h') }}',
      packageId: '{{ old('package_id') }}',
      prefix: '{{ old('prefix') }}',
      count: {{ (int) old('count', 1) }},
    },
    get currentPackage() {
      const id = parseInt(this.form.packageId);
      return this.packagesArr.find(p => p.id === id);
    },
    get typeLabel() {
      return this.typesMap[this.form.type]?.label || '—';
    },
    get typeFoot() {
      return this.typesMap[this.form.type]?.description || '';
    },
    get previewCode() {
      const pfx = (this.form.prefix || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
      return pfx ? pfx + '·A7K9' : 'XXXXXX';
    },
  };
}
</script>
@endpush
