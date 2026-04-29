<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher — ZeroNet</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            color: #111;
        }

        /* ── Toolbar ── */
        .toolbar {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; background: #fff;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 14px;
            position: sticky; top: 0; z-index: 10;
        }
        .btn-print {
            padding: 8px 18px; background: #7c3aed; color: #fff;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-print:hover { background: #6d28d9; }
        .btn-close {
            padding: 8px 14px; background: #e5e7eb; color: #374151;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer;
        }
        .count { font-size: 13px; color: #6b7280; margin-left: auto; }

        /* ── Page wrap ── */
        .page-wrap { padding: 0 14px 24px; }

        /* ── Grid layar: 4 kolom ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 7px;
        }

        /* ── Card ── */
        .card {
            border: 1.5px dashed #c4b5fd;
            border-radius: 7px;
            padding: 6px 8px;
            background: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Header */
        .card-head {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 4px;
        }
        .brand     { font-size: 9px; font-weight: 800; color: #7c3aed; letter-spacing: 0.07em; text-transform: uppercase; }
        .brand-sub { font-size: 7px; color: #a78bfa; margin-top: 1px; }
        .type-badge {
            font-size: 7.5px; font-weight: 700; color: #6d28d9;
            background: #ede9fe; padding: 2px 6px; border-radius: 20px; white-space: nowrap;
        }

        hr.dash { border: none; border-top: 1px dashed #ddd6fe; margin: 4px 0; }

        /* Credentials */
        .creds { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
        .cred  {
            background: #faf5ff; border: 1px solid #ddd6fe;
            border-radius: 5px; padding: 3px 5px; text-align: center;
        }
        .cred-lbl {
            font-size: 6.5px; text-transform: uppercase;
            letter-spacing: 0.1em; color: #a78bfa; margin-bottom: 2px;
        }
        .cred-val {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 900; color: #1e1b4b;
            letter-spacing: 0.04em; line-height: 1;
            font-size: 13px;
        }

        /* Meta */
        .meta { margin-top: 4px; }
        .meta-row {
            display: flex; justify-content: space-between; align-items: baseline;
            font-size: 7.5px; margin-bottom: 1.5px;
        }
        .meta-key { color: #9ca3af; }
        .meta-val { color: #374151; font-weight: 600; text-align: right; }

        /* Footer */
        .card-foot {
            margin-top: 4px; padding-top: 3px;
            border-top: 1px dashed #ddd6fe;
            text-align: center; font-size: 7px;
            color: #a78bfa; letter-spacing: 0.04em;
        }

        /* ── PRINT ──────────────────────────────────────────────────
           A4 portrait usable: 202mm × 289mm (margin 4mm tiap sisi)
           5 kol × 10 baris = 50 voucher/lembar
           Lebar card : (202 - 4 gap × 2.5mm) / 5 = 38.4mm
           Tinggi card: (289 - 9 gap × 2.5mm) / 10 = 26.65mm → 26mm
        ─────────────────────────────────────────────────────────── */
        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        @media print {
            body { background: #fff; }
            .toolbar   { display: none !important; }
            .page-wrap { padding: 0; }

            .grid {
                grid-template-columns: repeat(5, 1fr);
                grid-auto-rows: 26mm;
                gap: 2.5mm;
            }

            .card {
                height: 26mm;
                overflow: hidden;
                padding: 2.5px 5px;
                border-color: #a78bfa;
                border-radius: 5px;
            }

            /* Sembunyikan sub-brand agar hemat ruang */
            .brand-sub { display: none; }

            .card-head { margin-bottom: 2px; }
            .brand      { font-size: 8px; }
            .type-badge { font-size: 7px; padding: 1px 5px; }

            hr.dash { margin: 2.5px 0; }

            .creds    { gap: 3px; }
            .cred     { padding: 2px 4px; }
            .cred-lbl { font-size: 6px; margin-bottom: 1px; }
            .cred-val { font-size: 11px; }

            .meta       { margin-top: 2.5px; }
            .meta-row   { font-size: 7px; margin-bottom: 1px; }

            .card-foot  { margin-top: 2.5px; padding-top: 2px; font-size: 6px; }

            .cred { background: #f5f3ff; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨 Print</button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
        <span class="count">
            {{ $vouchers->count() }} voucher
            &mdash; ~{{ ceil($vouchers->count() / 50) }} lembar A4
            (50 per lembar)
        </span>
    </div>

    @if($vouchers->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
            <p style="font-size:15px;">Tidak ada voucher untuk dicetak.</p>
        </div>
    @else
        <div class="page-wrap">
            <div class="grid">
                @foreach($vouchers as $v)
                    @php
                        $typeLabels = ['4h' => '4 Jam', '5h' => '5 Jam', '7d' => '7 Hari'];
                        $typeDesc   = ['4h' => '1 hari', '5h' => '1 hari', '7d' => '7 hari'];
                    @endphp
                    <div class="card">

                        <div class="card-head">
                            <div>
                                <div class="brand">ZeroNet</div>
                            </div>
                            <span class="type-badge">{{ $typeLabels[$v->type] ?? $v->type }}</span>
                        </div>

                        <hr class="dash">

                        <div class="creds">
                            <div class="cred">
                                <div class="cred-lbl">Username</div>
                                <div class="cred-val">{{ $v->code }}</div>
                            </div>
                            <div class="cred">
                                <div class="cred-lbl">Password</div>
                                <div class="cred-val">{{ $v->password ?? '—' }}</div>
                            </div>
                        </div>

                        <hr class="dash">

                        <div class="meta">
                            <div class="meta-row">
                                <span class="meta-key">Paket</span>
                                <span class="meta-val">{{ $v->package?->groupname ?? '—' }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-key">Durasi</span>
                                <span class="meta-val">{{ $typeDesc[$v->type] ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="card-foot">login http://zero.net</div>

                    </div>
                @endforeach
            </div>
        </div>
    @endif

</body>
</html>
