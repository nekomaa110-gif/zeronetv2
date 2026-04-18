<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher — ZeroNet</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #fff; padding: 12px; color: #111; }

        /* Toolbar (hidden on print) */
        .toolbar { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #f3f4f6; border-radius: 8px; margin-bottom: 16px; }
        .btn-print { padding: 8px 18px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-print:hover { background: #6d28d9; }
        .btn-close { padding: 8px 14px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; }
        .count { font-size: 13px; color: #6b7280; margin-left: auto; }

        /* Grid */
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }

        /* Card */
        .card {
            border: 1.5px dashed #c4b5fd;
            border-radius: 10px;
            padding: 10px 12px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Header */
        .card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .brand { font-size: 11px; font-weight: 800; color: #7c3aed; letter-spacing: 0.06em; text-transform: uppercase; }
        .brand-sub { font-size: 9px; color: #a78bfa; margin-top: 1px; }
        .type-badge { font-size: 9px; font-weight: 700; color: #6d28d9; background: #ede9fe; padding: 2px 7px; border-radius: 20px; white-space: nowrap; }

        hr.dash { border: none; border-top: 1px dashed #ddd6fe; margin: 7px 0; }

        /* Credentials block */
        .creds { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin: 4px 0; }
        .cred { background: #faf5ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 6px 8px; text-align: center; }
        .cred-lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.09em; color: #a78bfa; margin-bottom: 3px; }
        .cred-val { font-family: 'Courier New', Courier, monospace; font-weight: 900; color: #1e1b4b; letter-spacing: 0.08em; line-height: 1; }
        .val-user { font-size: 15px; }
        .val-pass { font-size: 26px; }

        /* Meta rows */
        .meta { margin-top: 7px; }
        .meta-row { display: flex; justify-content: space-between; align-items: baseline; font-size: 10px; margin-bottom: 3px; }
        .meta-key { color: #9ca3af; }
        .meta-val { color: #374151; font-weight: 600; text-align: right; }

        /* Footer */
        .card-foot { margin-top: 7px; padding-top: 6px; border-top: 1px dashed #ddd6fe; text-align: center; font-size: 8.5px; color: #a78bfa; letter-spacing: 0.03em; }

        @media (max-width: 640px) {
            .grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media print {
            .toolbar { display: none !important; }
            body { padding: 4px; }
            .grid { grid-template-columns: repeat(3, 1fr); gap: 6px; }
            .card { border-color: #a78bfa; }
            .cred { background: #f5f3ff; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨 Print</button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
        <span class="count">{{ $vouchers->count() }} voucher</span>
    </div>

    @if($vouchers->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
            <p style="font-size:15px;">Tidak ada voucher untuk dicetak.</p>
        </div>
    @else
        <div class="grid">
            @foreach($vouchers as $v)
                @php
                    $typeLabels = ['4h' => '4 Jam', '5h' => '5 Jam', '7d' => '7 Hari'];
                    $typeDesc   = ['4h' => '4 jam · berlaku 24 jam', '5h' => '5 jam · berlaku 24 jam', '7d' => '7 hari penuh'];
                @endphp
                <div class="card">

                    <div class="card-head">
                        <div>
                            <div class="brand">ZeroNet</div>
                            <div class="brand-sub">Hotspot Voucher</div>
                        </div>
                        <span class="type-badge">{{ $typeLabels[$v->type] ?? $v->type }}</span>
                    </div>

                    <hr class="dash">

                    <div class="creds">
                        <div class="cred">
                            <div class="cred-lbl">Username</div>
                            <div class="cred-val val-user">{{ $v->code }}</div>
                        </div>
                        <div class="cred">
                            <div class="cred-lbl">Password</div>
                            <div class="cred-val val-pass">{{ $v->password ?? '—' }}</div>
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

                    <div class="card-foot">zeronet.id · hotspot voucher</div>

                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
