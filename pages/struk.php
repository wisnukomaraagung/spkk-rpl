<?php
include '../config/koneksi.php';

$id = $_GET['id'];

// Ambil detail transaksi
$data = mysqli_query($conn, "
SELECT p.nama, d.qty, d.subtotal
FROM detail_transaksi d
JOIN produk p ON d.produk_id = p.id
WHERE d.transaksi_id = $id
");

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk</title>
    <style>
        body {
            font-family: monospace;
            width: 250px;
        }
        h3 {
            text-align: center;
        }
        .line {
            border-top: 1px dashed black;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<h3>KOPERASI KAMPUS</h3>
<p>Tanggal: <?= date('Y-m-d H:i:s'); ?></p>

<div class="line"></div>

<?php while ($d = mysqli_fetch_assoc($data)) { 
    $total += $d['subtotal'];
?>
<p>
<?= $d['nama']; ?><br>
<?= $d['qty']; ?> x <?= number_format($d['subtotal'] / $d['qty']); ?> 
= <?= number_format($d['subtotal']); ?>
</p>
<?php } ?>

<div class="line"></div>

<h4>Total: Rp <?= number_format($total); ?></h4>

<p>Terima kasih 🙏</p>

<script>
window.print();
</script>

</body>
</html>