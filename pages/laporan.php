<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/koneksi.php';

// 🔒 Proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.html");
    exit;
}

// 🔒 Role admin & manager
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager') {
    echo "Akses ditolak!";
    exit;
}

// Migrasi Cepat: Pastikan database mendukung metadata modern
$check = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'metode'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN metode VARCHAR(50) DEFAULT 'TUNAI'");
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN kasir VARCHAR(100) DEFAULT 'Administrator'");
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN bayar DECIMAL(10,2) DEFAULT 0");
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN kembali DECIMAL(10,2) DEFAULT 0");
}

/* FILTER LOGIC */
$mode = $_GET['mode'] ?? 'semua';
$date_val = $_GET['date'] ?? ($mode == 'bulanan' ? date('Y-m') : date('Y-m-d'));

$where = "1";
$human_filter = "Keseluruhan Waktu";

if ($mode == 'harian') {
    $where = "DATE(tanggal) = '$date_val'";
    $human_filter = date('d F Y', strtotime($date_val));
} else if ($mode == 'bulanan') {
    $month = date('m', strtotime($date_val));
    $year = date('Y', strtotime($date_val));
    $where = "MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year'";
    $human_filter = date('F Y', strtotime($date_val));
}

/* SUMMARY QUERIES */
$q_sum = mysqli_query($conn, "SELECT COUNT(id) as total_trx, SUM(total) as revenue FROM transaksi WHERE $where");
$summary = mysqli_fetch_assoc($q_sum);
$total_trx = $summary['total_trx'] ?? 0;
$revenue = $summary['revenue'] ?? 0;
$average = $total_trx > 0 ? $revenue / $total_trx : 0;

/* TRANSAKSI LIST */
$data_trx = mysqli_query($conn, "SELECT * FROM transaksi WHERE $where ORDER BY tanggal DESC");

/* PRODUK TERLARIS */
$sql_best = "SELECT p.nama, SUM(d.qty) as terjual, SUM(d.subtotal) as omzet 
             FROM detail_transaksi d 
             JOIN produk p ON d.produk_id = p.id 
             JOIN transaksi t ON d.transaksi_id = t.id 
             WHERE $where 
             GROUP BY d.produk_id 
             ORDER BY terjual DESC LIMIT 5";
$data_best = mysqli_query($conn, $sql_best);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>

    <!-- ✅ PANGGIL CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/laporan.css">
    <style>
        /* Sembunyikan input mode yang tidak aktif */
        .hidden { display: none !important; }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrapper">
    <?php $page_title = 'Laporan Penjualan'; include '../includes/header.php'; ?>

    <main class="content-body">
        
        <!-- TOP FILTER BAR -->
        <form method="GET" class="laporan-filter-bar">
            <input type="hidden" name="mode" id="modeInput" value="<?= htmlspecialchars($mode) ?>">
            
            <div class="filter-left">
                <div class="filter-toggle">
                    <button type="button" class="btn-mode <?= $mode == 'semua' ? 'active' : '' ?>" onclick="setMode('semua')">♾️ Semua Waktu</button>
                    <button type="button" class="btn-mode <?= $mode == 'harian' ? 'active' : '' ?>" onclick="setMode('harian')">🗓 Harian</button>
                    <button type="button" class="btn-mode <?= $mode == 'bulanan' ? 'active' : '' ?>" onclick="setMode('bulanan')">📅 Bulanan</button>
                </div>
                
                <input type="date" name="date_harian" id="inputHarian" value="<?= $mode == 'harian' ? $date_val : date('Y-m-d') ?>" class="<?= $mode != 'harian' ? 'hidden' : '' ?>">
                <input type="month" name="date_bulanan" id="inputBulanan" value="<?= $mode == 'bulanan' ? $date_val : date('Y-m') ?>" class="<?= $mode != 'bulanan' ? 'hidden' : '' ?>">
                
                <button type="submit" class="btn-tampilkan" onclick="prepSubmit()">♈ Tampilkan</button>
            </div>
            
            <div class="filter-right">
                 <a href="cetak_laporan.php?mode=<?= $mode ?>&date=<?= $date_val ?>" target="_blank" class="btn-cetak">🖨️ Cetak</a>
            </div>
            <!-- Input yang akan dikirim saat render -->
            <input type="hidden" name="date" id="finalDate" value="">
        </form>

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
                
                // Matikan field temp agar tidak numpuk di url parameters
                document.getElementById('inputHarian').disabled = true;
                document.getElementById('inputBulanan').disabled = true;
            }
        </script>

        <!-- SUMMARY CARDS -->
        <div class="laporan-summary-cards">
            <div class="laporan-card laporan-card-blue">
                <div class="card-label">Total Transaksi</div>
                <div class="card-value"><?= number_format($total_trx) ?></div>
                <div class="card-icon">🧾</div>
            </div>
            <div class="laporan-card laporan-card-green">
                <div class="card-label">Total Penjualan</div>
                <div class="card-value">Rp <?= number_format($revenue, 0, ',', '.') ?></div>
                <div class="card-icon">💵</div>
            </div>
            <div class="laporan-card laporan-card-orange">
                <div class="card-label">Rata-rata / Transaksi</div>
                <div class="card-value">Rp <?= number_format($average, 0, ',', '.') ?></div>
                <div class="card-icon">📈</div>
            </div>
        </div>

        <!-- MAIN LAYOUT GRID -->
        <div class="laporan-grid">
            
            <!-- PANEL KIRI (TABEL TRANSAKSI) -->
            <div class="laporan-panel">
                <div class="laporan-panel-header">
                    📅 Detail Transaksi — <?= $human_filter ?>
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
                        background: #1e293b !important; /* Biru Gelap kembali */
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
                            <?php 
                            $no = 1;
                            $grandTotal = 0;
                            if (mysqli_num_rows($data_trx) > 0) {
                                while ($d = mysqli_fetch_assoc($data_trx)) {
                                    $grandTotal += $d['total'];
                                    // Pseudo kode ambil id
                                    $kode = "TRX-" . date('Ymd', strtotime($d['tanggal'])) . "-" . strtoupper(substr(md5($d['id']), 0, 5));
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $kode; ?></td>
                                <td><?= htmlspecialchars($d['kasir']) ?></td>
                                <?php 
                                    $metode = strtoupper($d['metode']);
                                    $badgeStyle = "background: #10b981;"; // Default Green
                                    if($metode == 'QRIS') $badgeStyle = "background: #0f172a;"; // Navy
                                    if($metode == 'TRANSFER') $badgeStyle = "background: #2563eb;"; // Blue
                                ?>
                                <td><span class="badge-metode" style="<?= $badgeStyle ?>"><?= $metode ?></span></td>
                                <td class="val-green">Rp <?= number_format($d['total'], 0, ',', '.'); ?></td>
                                <td style="text-align: right; color: #475569;"><?= date('d/m H:i', strtotime($d['tanggal'])); ?></td>
                            </tr>
                            <?php 
                                } 
                            } else { ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">Tidak ada transaksi ditemukan.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-right: 2rem; color: #64748b; font-weight: 500;">TOTAL PENDAPATAN</td>
                                <td class="val-green" colspan="2" style="font-size: 1.1rem; text-align: right;">Rp <?= number_format($grandTotal, 0, ',', '.'); ?></td>
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
                    <?php 
                    $rank = 1;
                    if (mysqli_num_rows($data_best) > 0) {
                        while ($p = mysqli_fetch_assoc($data_best)) {
                            $rClass = 'rank-other';
                            if ($rank == 1) $rClass = 'rank-1';
                            if ($rank == 2) $rClass = 'rank-2';
                            if ($rank == 3) $rClass = 'rank-3';
                    ?>
                    <div class="best-seller-item">
                        <div class="rank-badge <?= $rClass ?>"><?= $rank ?></div>
                        <div class="best-seller-info">
                            <div class="bs-name"><?= htmlspecialchars($p['nama']) ?></div>
                            <div class="bs-qty"><?= $p['terjual'] ?> terjual</div>
                        </div>
                        <div class="bs-revenue">
                            Rp <?= number_format($p['omzet'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <?php 
                            $rank++;
                        } 
                    } else { ?>
                        <div style="padding: 2rem; color:#94a3b8; text-align:center;">Belum ada penjualan.</div>
                    <?php } ?>
                </div>
            </div>

        </div>

    </main>
</div>

</body>
</html>