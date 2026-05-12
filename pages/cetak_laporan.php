<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/koneksi.php';

// 🔒 Proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.html");
    exit;
}

// 🔒 Role
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager') {
    echo "Akses ditolak!";
    exit;
}

/* FILTER LOGIC (Sync with Laporan.php) */
$mode = $_GET['mode'] ?? 'semua';
$date_val = $_GET['date'] ?? date('Y-m-d');

$where = "1";
$human_filter = "Keseluruhan Waktu";

if ($mode == 'harian') {
    $where = "DATE(tanggal) = '$date_val'";
    $human_filter = date('d F Y', strtotime($date_val));
} else if ($mode == 'bulanan') {
    $month = date('m', strtotime($date_val));
    $year = date('Y', strtotime($date_val));
    $where = "MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year'";
    $human_filter = class_exists('IntlDateFormatter') ? 
        date('F Y', strtotime($date_val)) : date('F Y', strtotime($date_val));
}

// AMBIL DATA
$data = mysqli_query($conn, "SELECT * FROM transaksi WHERE $where ORDER BY tanggal DESC");
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan - <?= $human_filter ?></title>
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
<div class="subtitle">Periode: <?= $human_filter ?></div>

<table>
    <tr>
        <th>No</th>
        <th>Kode Transaksi</th>
        <th>Kasir / Metode</th>
        <th>Waktu / Tgl</th>
        <th>Sub Total</th>
    </tr>

    <?php 
    $no = 1;
    if (mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $total += $d['total'];
            $kode = "TRX-" . date('Ymd', strtotime($d['tanggal'])) . "-" . strtoupper(substr(md5($d['id']), 0, 5));
            $kasir = $d['kasir'] ?? 'Administrator';
            $metode = $d['metode'] ?? 'TUNAI';
    ?>
    <tr>
        <td><?= $no++; ?></td>
        <td><?= $kode; ?></td>
        <td><?= htmlspecialchars($kasir) ?> <br> <small>(<?= $metode ?>)</small></td>
        <td><?= date('d/m/Y H:i', strtotime($d['tanggal'])); ?></td>
        <td class="text-right">Rp <?= number_format($d['total'], 0, ',', '.'); ?></td>
    </tr>
    <?php 
        } 
    } else {
        echo "<tr><td colspan='5'>Tidak ada transaksi di periode ini.</td></tr>";
    }
    ?>
    <tr class="total-row">
        <td colspan="4" class="text-right">TOTAL PENDAPATAN</td>
        <td class="text-right">Rp <?= number_format($total, 0, ',', '.'); ?></td>
    </tr>
</table>

<script>
window.onload = function() { window.print(); }
</script>

</body>
</html>