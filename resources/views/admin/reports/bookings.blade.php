<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Peminjaman – AniRent</title>
    <style>
        /* ── Base ─────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px; color: #1a1a2e;
            background: #f5f5f5;
            padding: 24px;
        }

        /* ── Wrapper (screen only) ────────────────────────── */
        .wrapper {
            max-width: 900px; margin: 0 auto;
            background: #fff; border-radius: 12px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* ── Header ──────────────────────────────────────── */
        .report-header {
            background: #1a1a2e;
            color: #fff; padding: 28px 32px 20px;
        }
        .report-title   { font-size: 20px; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.3px; }
        .report-sub     { font-size: 12px; color: #9d9dbc; }
        .header-meta {
            display: flex; justify-content: space-between; align-items: flex-end;
            margin-top: 16px; flex-wrap: wrap; gap: 8px;
        }
        .header-meta-item { }
        .meta-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #7878a0; margin-bottom: 3px; }
        .meta-val   { font-size: 13px; font-weight: 600; color: #fff; }

        /* ── Action bar (screen only) ─────────────────────── */
        .action-bar {
            padding: 14px 32px;
            background: #F0EFFE; border-bottom: 1px solid #dddaf8;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .filter-label { font-size: 11px; color: #534AB7; font-weight: 600; margin-right: 4px; }
        .filter-select {
            padding: 5px 10px; border: 1px solid #C4BFFA; border-radius: 6px;
            font-size: 11px; color: #534AB7; background: #fff; cursor: pointer;
        }
        .btn-print {
            margin-left: auto;
            padding: 7px 20px; border-radius: 8px;
            background: #534AB7; color: #fff;
            font-size: 12px; font-weight: 600;
            border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;
        }
        .btn-print:hover { background: #3C3489; }
        .btn-back {
            padding: 7px 16px; border-radius: 8px;
            background: transparent; color: #534AB7;
            border: 1.5px solid #534AB7;
            font-size: 12px; font-weight: 500; cursor: pointer; text-decoration: none;
        }
        .btn-back:hover { background: #EEEDFE; }

        /* ── Summary Stats ───────────────────────────────── */
        .stats-row {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 0; border-bottom: 1px solid #eee;
        }
        .stat-box {
            padding: 16px 20px; text-align: center;
            border-right: 1px solid #eee;
        }
        .stat-box:last-child { border-right: none; }
        .stat-num   { font-size: 22px; font-weight: 700; color: #1a1a2e; line-height: 1; }
        .stat-lbl   { font-size: 10px; color: #aaa; margin-top: 4px; }
        .stat-blue  .stat-num { color: #534AB7; }
        .stat-teal  .stat-num { color: #0F6E56; }
        .stat-red   .stat-num { color: #B91C1C; }
        .stat-amber .stat-num { color: #92400E; }

        /* ── Table ────────────────────────────────────────── */
        .table-section { padding: 0 32px 24px; }
        .table-title {
            font-size: 12px; font-weight: 700; color: #888;
            text-transform: uppercase; letter-spacing: 0.8px;
            padding: 16px 0 10px;
        }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #F8F7F4; }
        th {
            padding: 8px 10px; text-align: left;
            font-size: 9px; font-weight: 700; color: #aaa;
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px solid #e0ddd5;
        }
        td {
            padding: 9px 10px; font-size: 10.5px;
            border-bottom: 1px solid #f2f0eb; vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #FAFAF8; }
        .row-late { background: #FFF8F7 !important; }

        /* Cells */
        .code { font-family: monospace; font-size: 10px; font-weight: 700; color: #534AB7;
                background: #EEEDFE; padding: 2px 6px; border-radius: 4px; }
        .name-cell { font-weight: 500; }
        .date-cell { white-space: nowrap; color: #555; }
        .text-red  { color: #e53935; font-weight: 600; }
        .item-type {
            font-size: 8px; font-weight: 700; padding: 1px 5px; border-radius: 3px;
            text-transform: uppercase; display: inline-block; margin-bottom: 2px;
        }
        .unit-type   { background: #EEEDFE; color: #534AB7; }
        .bundle-type { background: #FEF3C7; color: #92400E; }
        .denda-val  { color: #e53935; font-weight: 600; }
        .pill-fine {
            font-size: 8px; font-weight: 600; padding: 2px 6px; border-radius: 6px;
            display: inline-block;
        }
        .fine-paid    { background: #E1F5EE; color: #0F6E56; }
        .fine-unpaid  { background: #FEF2F2; color: #B91C1C; }

        /* ── Footer ──────────────────────────────────────── */
        .report-footer {
            padding: 14px 32px; background: #F8F7F4;
            border-top: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 10px; color: #aaa;
        }

        /* ── Empty ───────────────────────────────────────── */
        .empty-row td { text-align: center; color: #ccc; padding: 32px !important; }

        /* ── @media print ────────────────────────────────── */
        @media print {
            body { background: white; padding: 0; font-size: 10px; }
            .wrapper { box-shadow: none; border-radius: 0; max-width: 100%; }
            .action-bar { display: none !important; }
            .report-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stats-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .row-late { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .code, .item-type, .pill-fine { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="report-header">
        <div class="report-title">📋 Laporan Peminjaman – AniRent</div>
        <div class="report-sub">Sistem Manajemen Peminjaman Unit Anime</div>
        <div class="header-meta">
            <div class="header-meta-item">
                <div class="meta-label">Periode</div>
                <div class="meta-val">{{ $periodeLabel }}</div>
            </div>
            <div class="header-meta-item">
                <div class="meta-label">Total Data</div>
                <div class="meta-val">{{ $bookings->count() }} transaksi</div>
            </div>
            <div class="header-meta-item">
                <div class="meta-label">Dicetak</div>
                <div class="meta-val">{{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>
            <div class="header-meta-item">
                <div class="meta-label">Dicetak Oleh</div>
                <div class="meta-val">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>

    <!-- Action Bar (screen only) -->
    <div class="action-bar">
        <a href="{{ route('admin.bookings.history') }}" class="btn-back">← Kembali</a>
        <span class="filter-label">Filter:</span>

        <form method="GET" action="{{ route('admin.bookings.report') }}" style="display:flex;gap:6px;align-items:center;">
            <select name="bulan" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Bulan</option>
                @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $m => $nm)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $nm }}</option>
                @endforeach
            </select>

            <select name="tahun" class="filter-select" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>

        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Stats Summary -->
    <div class="stats-row">
        <div class="stat-box stat-blue">
            <div class="stat-num">{{ $bookings->count() }}</div>
            <div class="stat-lbl">Total Transaksi</div>
        </div>
        <div class="stat-box stat-teal">
            <div class="stat-num">{{ $totalTepatWaktu }}</div>
            <div class="stat-lbl">Tepat Waktu</div>
        </div>
        <div class="stat-box stat-red">
            <div class="stat-num">{{ $totalTerlambat }}</div>
            <div class="stat-lbl">Terlambat</div>
        </div>
        <div class="stat-box stat-amber">
            <div class="stat-num">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
            <div class="stat-lbl">Total Denda</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-section">
        <div class="table-title">Detail Transaksi</div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode Booking</th>
                    <th>Anggota</th>
                    <th>Item</th>
                    <th>Tgl Mulai</th>
                    <th>Tenggat</th>
                    <th>Dikembalikan</th>
                    <th>Denda</th>
                    <th>Status Denda</th>
                    <th>Diproses</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $i => $b)
                <tr class="{{ $b->fine ? 'row-late' : '' }}">
                    <td style="color:#aaa;">{{ $i + 1 }}</td>
                    <td><span class="code">{{ $b->kode_booking }}</span></td>
                    <td>
                        <div class="name-cell">{{ $b->user?->name ?? '—' }}</div>
                        <div style="color:#aaa;font-size:9px;">{{ $b->user?->email ?? '' }}</div>
                    </td>
                    <td>
                        @if($b->unit)
                            <span class="item-type unit-type">Unit</span><br>
                            <span>{{ $b->unit->nama_unit }}</span>
                        @elseif($b->bundle)
                            <span class="item-type bundle-type">Bundle</span><br>
                            <span>{{ $b->bundle->nama_bundle }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($b->tanggal_mulai)->format('d M Y') }}</td>
                    <td class="date-cell {{ $b->fine ? 'text-red' : '' }}">
                        {{ \Carbon\Carbon::parse($b->tanggal_selesai_rencana)->format('d M Y') }}
                    </td>
                    <td class="date-cell">
                        {{ $b->tanggal_selesai_aktual ? \Carbon\Carbon::parse($b->tanggal_selesai_aktual)->format('d M Y') : '—' }}
                    </td>
                    <td>
                        @if($b->fine)
                            <span class="denda-val">Rp {{ number_format($b->fine->jumlah_denda, 0, ',', '.') }}</span>
                            <div style="color:#aaa;font-size:9px;">{{ $b->fine->hari_terlambat }} hari</div>
                        @else
                            <span style="color:#aaa;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($b->fine)
                            <span class="pill-fine {{ $b->fine->status === 'paid' ? 'fine-paid' : 'fine-unpaid' }}">
                                {{ $b->fine->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        @else
                            <span style="color:#aaa;">—</span>
                        @endif
                    </td>
                    <td style="color:#777;">{{ $b->processedBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="10">Tidak ada data untuk periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="report-footer">
        <span>AniRent &copy; {{ date('Y') }} — Laporan Peminjaman</span>
        <span>Periode: {{ $periodeLabel }}</span>
        <span>Total: {{ $bookings->count() }} transaksi | Denda: Rp {{ number_format($totalDenda, 0, ',', '.') }}</span>
    </div>

</div>
</body>
</html>
