<?php
session_start();
include '../config/koneksi.php';

// 🔒 Cek login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.html");
    exit;
}

$role = $_SESSION['role'];
$page_title = 'Dashboard Utama';

// --- STATISTIK ---
$tgl_hari_ini = date('Y-m-d');
$bln_ini = date('Y-m'); // Format Y-m untuk pencarian LIKE '2026-04%'

// 1. Transaksi Hari Ini
$q_trans_hari = mysqli_query($conn, "SELECT COUNT(id) as jml FROM transaksi WHERE DATE(tanggal) = '$tgl_hari_ini'");
$trans_hari = mysqli_fetch_assoc($q_trans_hari)['jml'] ?? 0;

// 2. Penjualan Hari Ini
$q_jual_hari = mysqli_query($conn, "SELECT SUM(total) as tot FROM transaksi WHERE DATE(tanggal) = '$tgl_hari_ini'");
$jual_hari = mysqli_fetch_assoc($q_jual_hari)['tot'] ?? 0;

// 3. Penjualan Bulan Ini
$q_jual_bln = mysqli_query($conn, "SELECT SUM(total) as tot FROM transaksi WHERE tanggal LIKE '$bln_ini%'");
$jual_bln = mysqli_fetch_assoc($q_jual_bln)['tot'] ?? 0;

// 4. Produk Stok Menipis (< 10)
$q_stok = mysqli_query($conn, "SELECT COUNT(id) as jml FROM produk WHERE stok < 10");
$stok_menipis = mysqli_fetch_assoc($q_stok)['jml'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Koperasi - Dashboard</title>
    <!-- ✅ PANGGIL CSS GLOBAL & LAYOUT -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body>

<!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">
    <!-- HEADER -->
    <?php include '../includes/header.php'; ?>

    <!-- CONTENT BODY -->
    <main class="content-body">
        
        <!-- SUMMARY CARDS -->
        <div class="stat-cards-grid">
            
            <?php if ($role == 'kasir' || $role == 'admin'): ?>
            <div class="stat-card card-blue">
                <div class="stat-card-info">
                    <h4>Transaksi Hari Ini</h4>
                    <h2><?= $trans_hari ?></h2>
                </div>
                <div class="stat-card-icon">🛒</div>
            </div>
            <?php endif; ?>

            <div class="stat-card card-green">
                <div class="stat-card-info">
                    <h4>Penjualan Hari Ini</h4>
                    <h2>Rp <?= number_format($jual_hari, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-card-icon">💵</div>
            </div>

            <?php if ($role == 'manager' || $role == 'admin'): ?>
            <div class="stat-card card-cyan">
                <div class="stat-card-info">
                    <h4>Penjualan Bulan Ini</h4>
                    <h2>Rp <?= number_format($jual_bln, 0, ',', '.') ?></h2>
                </div>
                <div class="stat-card-icon">📅</div>
            </div>
            <?php endif; ?>

            <?php if ($role == 'admin'): ?>
            <div class="stat-card card-orange">
                <div class="stat-card-info">
                    <h4>Stok Menipis</h4>
                    <h2><?= $stok_menipis ?> Produk</h2>
                </div>
                <div class="stat-card-icon">📦</div>
            </div>
            <?php endif; ?>

        </div>

        <!-- PANELS GRID -->
        <div class="panels-grid">
            <!-- Transaksi Terbaru Panel -->
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">⏱️ Transaksi Terbaru</h3>
                    <?php if ($role == 'kasir' || $role == 'admin'): ?>
                    <a href="../pages/transaksi.php" style="font-size: 0.85rem; color: #0284c7; text-decoration: none; border: 1px solid #e0f2fe; padding: 0.25rem 0.75rem; border-radius: 4px;">Lihat Semua</a>
                    <?php endif; ?>
                </div>
                
                <?php
                $q_recent = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tanggal DESC LIMIT 5");
                if (mysqli_num_rows($q_recent) > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b;">
                        <th style="padding: 0.5rem 0;">ID</th>
                        <th>Waktu</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                    <?php while($tr = mysqli_fetch_assoc($q_recent)): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 0; font-weight: 600;">#<?= $tr['id'] ?></td>
                        <td style="color: #475569;"><?= date('H:i', strtotime($tr['tanggal'])) ?></td>
                        <td style="text-align: right; font-weight: 600; color: #10b981;">Rp <?= number_format($tr['total'],0,',','.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem 0; color: #94a3b8;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📥</div>
                    Belum ada transaksi hari ini
                </div>
                <?php endif; ?>
            </div>

            <!-- Stok Menipis Panel (Hanya admin) -->
            <?php if ($role == 'admin'): ?>
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">⚠️ Stok Menipis</h3>
                    <a href="../pages/produk.php" style="font-size: 0.85rem; color: #f59e0b; text-decoration: none; border: 1px solid #fef3c7; padding: 0.25rem 0.75rem; border-radius: 4px;">Kelola</a>
                </div>
                
                <?php
                $q_stok_list = mysqli_query($conn, "SELECT * FROM produk WHERE stok < 10 ORDER BY stok ASC LIMIT 5");
                if (mysqli_num_rows($q_stok_list) > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b;">
                        <th style="padding: 0.5rem 0;">Nama Produk</th>
                        <th style="text-align: right;">Sisa Stok</th>
                    </tr>
                    <?php while($st = mysqli_fetch_assoc($q_stok_list)): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 0; font-weight: 500; color: #1e293b;"><?= $st['nama'] ?></td>
                        <td style="text-align: right;">
                            <span style="background: #fee2e2; color: #ef4444; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">
                                <?= $st['stok'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem 0; color: #10b981;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                    Semua stok aman
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
