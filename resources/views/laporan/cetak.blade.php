<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan - {{ $human_filter }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e293b; }
        h2 { text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 20px; font-size: 14px; }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th {
            background: #1e293b;
            color: white;
            font-size: 13px;
            text-transform: uppercase;
        }
        td, th {
            padding: 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-size: 14px;
        }
        .total-row {
            font-weight: bold;
            background: #f8fafc;
        }
        .text-right { text-align: right; }
    </style>
</head>
<body>

<h2>Laporan Penjualan Koperasi</h2>
<div class="subtitle">Periode: {{ $human_filter }}</div>

<table>
    <tr>
        <th>No</th>
        <th>Kode Transaksi</th>
        <th>Kasir / Metode</th>
        <th>Waktu / Tgl</th>
        <th>Sub Total</th>
    </tr>

    @php $no = 1; @endphp
    @forelse ($data_trx as $d)
    @php
    $kode = "TRX-" . $d->tanggal->format('Ymd') . "-" . strtoupper(substr(md5($d->id), 0, 5));
    $kasir = $d->kasir ?? 'Administrator';
    $metode = $d->metode ?? 'TUNAI';
    @endphp
    <tr>
        <td>{{ $no++ }}</td>
        <td>{{ $kode }}</td>
        <td>{{ $kasir }} <br> <small>({{ $metode }})</small></td>
        <td>{{ $d->tanggal->format('d/m/Y H:i') }}</td>
        <td class="text-right">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="5">Tidak ada transaksi di periode ini.</td>
    </tr>
    @endforelse
    <tr class="total-row">
        <td colspan="4" class="text-right">TOTAL PENDAPATAN</td>
        <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
    </tr>
</table>

<script>
window.onload = function() { window.print(); }
</script>

</body>
</html>
