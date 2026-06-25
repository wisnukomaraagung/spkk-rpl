@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/laporan.css') }}">
<style>
    .hidden { display: none !important; }
</style>
@endsection

@section('content')
<!-- TOP FILTER BAR -->
<form method="GET" class="laporan-filter-bar" action="{{ route('laporan.index') }}" id="filterForm">
    <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">
    
    <div class="filter-left">
        <div class="filter-toggle">
            <button type="button" class="btn-mode {{ $mode == 'semua' ? 'active' : '' }}" onclick="setMode('semua')">♾️ Semua Waktu</button>
            <button type="button" class="btn-mode {{ $mode == 'harian' ? 'active' : '' }}" onclick="setMode('harian')">🗓 Harian</button>
            <button type="button" class="btn-mode {{ $mode == 'bulanan' ? 'active' : '' }}" onclick="setMode('bulanan')">📅 Bulanan</button>
        </div>
        
        <input type="date" id="inputHarian" value="{{ $mode == 'harian' ? $date_val : now()->format('Y-m-d') }}" class="{{ $mode != 'harian' ? 'hidden' : '' }}">
        <input type="month" id="inputBulanan" value="{{ $mode == 'bulanan' ? $date_val : now()->format('Y-m') }}" class="{{ $mode != 'bulanan' ? 'hidden' : '' }}">
        
        <button type="submit" class="btn-tampilkan" onclick="prepSubmit()">♈ Tampilkan</button>
    </div>
    
    <div class="filter-right">
         <a href="{{ route('laporan.print') }}?mode={{ $mode }}&date={{ $date_val }}" target="_blank" class="btn-cetak">🖨️ Cetak</a>
    </div>
    <!-- Input yang akan dikirim saat render -->
    <input type="hidden" name="date" id="finalDate" value="">
</form>

<!-- SUMMARY CARDS -->
<div class="laporan-summary-cards">
    <div class="laporan-card laporan-card-blue">
        <div class="card-label">Total Transaksi</div>
        <div class="card-value">{{ number_format($total_trx) }}</div>
        <div class="card-icon">🧾</div>
    </div>
    <div class="laporan-card laporan-card-green">
        <div class="card-label">Total Penjualan</div>
        <div class="card-value">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
        <div class="card-icon">💵</div>
    </div>
    <div class="laporan-card laporan-card-orange">
        <div class="card-label">Rata-rata / Transaksi</div>
        <div class="card-value">Rp {{ number_format($average, 0, ',', '.') }}</div>
        <div class="card-icon">📈</div>
    </div>
</div>

<!-- MAIN LAYOUT GRID -->
<div class="laporan-grid">
    
    <!-- PANEL KIRI (TABEL TRANSAKSI) -->
    <div class="laporan-panel">
        <div class="laporan-panel-header">
            📅 Detail Transaksi — {{ $human_filter }}
        </div>
        <style>
            .laporan-table-wrap {
                max-height: 600px;
                overflow-y: auto;
                position: relative;
                border-top: 1px solid #e2e8f0;
            }
            .laporan-table thead th {
                position: sticky;
                top: 0;
                background: #1e293b !important;
                color: #ffffff !important;
                z-index: 10;
            }
        </style>
        <div class="laporan-table-wrap">
            <table class="laporan-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>KODE</th>
                        <th>KASIR</th>
                        <th>METODE</th>
                        <th>TOTAL</th>
                        <th style="text-align: right;">WAKTU</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                    $no = 1; 
                    $grandTotal = 0;
                    @endphp
                    @forelse ($data_trx as $d)
                    @php
                    $grandTotal += $d->total;
                    $kode = "TRX-" . $d->tanggal->format('Ymd') . "-" . strtoupper(substr(md5($d->id), 0, 5));
                    $metode = strtoupper($d->metode);
                    $badgeStyle = "background: #10b981;"; // Default Green
                    if($metode == 'QRIS') $badgeStyle = "background: #0f172a;"; // Navy
                    if($metode == 'TRANSFER') $badgeStyle = "background: #2563eb;"; // Blue
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $kode }}</td>
                        <td>{{ $d->kasir }}</td>
                        <td><span class="badge-metode" style="{{ $badgeStyle }}">{{ $metode }}</span></td>
                        <td class="val-green">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #475569;">{{ $d->tanggal->format('d/m H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">Tidak ada transaksi ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; padding-right: 2rem; color: #64748b; font-weight: 500;">TOTAL PENDAPATAN</td>
                        <td class="val-green" colspan="2" style="font-size: 1.1rem; text-align: right;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- PANEL KANAN (PRODUK TERLARIS) -->
    <div class="laporan-panel">
        <div class="laporan-panel-header">
            🏆 Produk Terlaris
        </div>
        <div class="best-seller-list">
            @php $rank = 1; @endphp
            @forelse ($data_best as $p)
            @php
            $rClass = 'rank-other';
            if ($rank == 1) $rClass = 'rank-1';
            if ($rank == 2) $rClass = 'rank-2';
            if ($rank == 3) $rClass = 'rank-3';
            @endphp
            <div class="best-seller-item">
                <div class="rank-badge {{ $rClass }}">{{ $rank++ }}</div>
                <div class="best-seller-info">
                    <div class="bs-name">{{ $p->nama }}</div>
                    <div class="bs-qty">{{ $p->terjual }} terjual</div>
                </div>
                <div class="bs-revenue">
                    Rp {{ number_format($p->omzet, 0, ',', '.') }}
                </div>
            </div>
            @empty
            <div style="padding: 2rem; color:#94a3b8; text-align:center;">Belum ada penjualan.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function setMode(val) {
        document.getElementById('modeInput').value = val;
        document.querySelectorAll('.btn-mode').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
        
        if (val === 'harian') {
            document.getElementById('inputHarian').classList.remove('hidden');
            document.getElementById('inputBulanan').classList.add('hidden');
        } else if (val === 'bulanan') {
            document.getElementById('inputHarian').classList.add('hidden');
            document.getElementById('inputBulanan').classList.remove('hidden');
        } else {
            document.getElementById('inputHarian').classList.add('hidden');
            document.getElementById('inputBulanan').classList.add('hidden');
        }
    }
    
    function prepSubmit() {
        var m = document.getElementById('modeInput').value;
        if (m === 'semua') {
            document.getElementById('finalDate').value = "";
        } else {
            document.getElementById('finalDate').value = m === 'harian' ? document.getElementById('inputHarian').value : document.getElementById('inputBulanan').value;
        }
        
        // Disable temporary fields to prevent them from showing up in request query string
        document.getElementById('inputHarian').disabled = true;
        document.getElementById('inputBulanan').disabled = true;
    }
</script>
@endsection
