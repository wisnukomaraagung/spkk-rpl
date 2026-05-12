<?php
session_start();
include '../config/koneksi.php';

// 🔒 Proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.html");
    exit;
}

// 🔒 Proteksi role (hanya admin)
if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// SETUP FILE UPLOADS
$dirUpload = '../assets/img/produk/';
if (!is_dir($dirUpload)) {
    mkdir($dirUpload, 0777, true);
}

$check = mysqli_query($conn, "SHOW COLUMNS FROM produk LIKE 'gambar'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) NULL");
}

// ➕ TAMBAH PRODUK
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    $gambar_nama = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_nama = 'PRD_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $dirUpload . $gambar_nama);
    }
    
    if ($gambar_nama) {
        mysqli_query($conn, "INSERT INTO produk (nama,harga,stok,gambar) VALUES ('$nama','$harga','$stok', '$gambar_nama')");
    } else {
        mysqli_query($conn, "INSERT INTO produk (nama,harga,stok) VALUES ('$nama','$harga','$stok')");
    }
}

// ✏️ EDIT PRODUK
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambar_lama = $_POST['gambar_lama'] ?? '';
    $hapus_gambar_flag = $_POST['hapus_gambar_flag'] ?? '0';
    
    $sql_gambar = "";
    
    if ($hapus_gambar_flag === '1') {
        if (!empty($gambar_lama) && file_exists($dirUpload . $gambar_lama)) {
            unlink($dirUpload . $gambar_lama);
        }
        $sql_gambar = ", gambar=NULL";
    }

    if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar_baru']['name'], PATHINFO_EXTENSION);
        $gambar_nama = 'PRD_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['gambar_baru']['tmp_name'], $dirUpload . $gambar_nama)) {
            // Hapus file usang
            if (!empty($gambar_lama) && file_exists($dirUpload . $gambar_lama)) {
                unlink($dirUpload . $gambar_lama);
            }
            $sql_gambar = ", gambar='$gambar_nama'";
        }
    }

    mysqli_query($conn, "UPDATE produk SET nama='$nama', harga='$harga', stok='$stok' $sql_gambar WHERE id=$id");
}

// ❌ HAPUS PRODUK
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $q_del = mysqli_query($conn, "SELECT gambar FROM produk WHERE id=$id");
    $d_del = mysqli_fetch_assoc($q_del);
    if ($d_del && !empty($d_del['gambar']) && file_exists($dirUpload . $d_del['gambar'])) {
        unlink($dirUpload . $d_del['gambar']);
    }
    mysqli_query($conn, "DELETE FROM produk WHERE id=$id");
}

// AMBIL DATA
$data = mysqli_query($conn, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>

    <!-- ✅ PANGGIL CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <style>
        .form-tambah-container {
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .form-row { margin-bottom: 1rem; display: flex; flex-direction: column; }
        .form-row label { font-weight: 600; margin-bottom: 0.4rem; font-size: 0.9rem; color: #1e293b; }
        .form-row input[type="text"], .form-row input[type="number"] { padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; width: 100%; box-sizing: border-box; outline: none; font-size: 0.95rem; }
        .form-row input[type="file"] { font-size: 0.9rem; padding: 0.5rem 0; width: 100%; cursor: pointer;}
        button.btn-primary { background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; font-size: 0.95rem; }
        button.btn-primary:hover { background: #2563eb; }
        
        .btn-edit { background: #10b981; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: 0.2s;}
        .btn-edit:hover { background: #059669; }
        .btn-delete { background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: 0.2s; }
        .btn-delete:hover { background: #dc2626; }
        
        .thumbnail-img { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; background: #f8fafc; }
        .table-produk { width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .table-produk th { background: #1e293b; color: #ffffff; padding: 1rem; font-weight: 600; text-align: left; }
        .table-produk td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .table-produk tr:last-child td { border-bottom: none; }
        .table-produk tr:hover { background: #f8fafc; }
        
        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: 0.2s ease; padding: 1rem; box-sizing: border-box; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content { background: white; width: 100%; max-width: 480px; border-radius: 12px; padding: 1.5rem !important; transform: translateY(20px); transition: 0.3s ease; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); box-sizing: border-box; }
        .modal-overlay.active .modal-content { transform: translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
        .modal-header h3 { margin: 0; color: #1e293b; font-size: 1.2rem; font-weight: 700; }
        .btn-close-modal { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; transition: 0.2s; padding: 0; line-height: 1; }
        .btn-close-modal:hover { color: #ef4444; }
        
        .modal-content .form-row { margin-bottom: 0.85rem; }
        .modal-content .form-row label { font-size: 0.85rem; margin-bottom: 0.3rem; }
        .modal-content input[type="text"], .modal-content input[type="number"] { padding: 0.5rem; font-size: 0.9rem; }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrapper">
    <?php $page_title = 'Manajemen Produk'; include '../includes/header.php'; ?>

    <main class="content-body">
        <div class="panel">
            <h2 class="panel-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <img src="../assets/img/box.png" alt="Produk" style="height: 32px; width: auto; object-fit: contain; display: block;"> Data dan Manajemen Produk
            </h2>

<div class="form-tambah-container">
    <h3 style="margin-top: 0; color: #1e293b;">Tambah Produk Baru</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <label>Nama Produk</label>
            <input type="text" name="nama" required>
        </div>
        <div class="form-row">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" required>
        </div>
        <div class="form-row">
            <label>Stok Awal</label>
            <input type="number" name="stok" required>
        </div>
        <div class="form-row">
            <label>Gambar Produk (Opsional)</label>
            <input type="file" name="gambar" accept="image/*">
        </div>
        <button name="tambah" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            <img src="../assets/img/plus.png" alt="Add" style="width: 14px; height: 14px; filter: brightness(0) invert(1);"> Tambah Produk
        </button>
    </form>
</div>

<hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 2rem;">

<!-- TABEL PRODUK -->
<h3 style="margin-top: 0; color: #1e293b; margin-bottom: 1rem;">Daftar Produk Aktif</h3>
<div style="overflow-x: auto;">
    <table class="table-produk">
    <tr>
        <th style="width: 50px;">No</th>
        <th style="text-align: center; width: 80px;">Foto</th>
        <th>Nama Produk</th>
        <th>Harga</th>
        <th>Stok</th>
        <th style="text-align: right; padding-right: 2rem;">Aksi</th>
    </tr>

    <?php 
    $no = 1;
    while ($d = mysqli_fetch_assoc($data)) { 
        $imgSrc = !empty($d['gambar']) && file_exists("../assets/img/produk/" . $d['gambar']) 
                  ? "../assets/img/produk/" . htmlspecialchars($d['gambar']) 
                  : ""; 
    ?>
    <tr>
        <td style="color: #64748b; font-weight: 500;"><?= $no++; ?></td>
        <td style="text-align: center;">
            <?= $imgSrc ? "<img src='$imgSrc' class='thumbnail-img'>" : "<div class='thumbnail-img' style='display:flex; justify-content:center; align-items:center; color:#94a3b8; font-size:0.75rem; margin:0 auto;'>N/A</div>" ?>
        </td>
        <td style="font-weight: 600; font-size: 1.05rem; color: #0f172a;"><?= htmlspecialchars($d['nama']); ?></td>
        <td style="color: #10b981; font-weight: 600;">Rp <?= number_format($d['harga'],0,',','.'); ?></td>
        <td style="font-weight: 600; <?= $d['stok'] <= 5 ? "color: #ef4444;" : "color: #475569;" ?>">
            <?= $d['stok']; ?> <?= $d['stok'] <= 5 ? "<span style='font-size:0.75rem; background:#fee2e2; padding:2px 6px; border-radius:4px; margin-left:4px;'>Tipis</span>" : "" ?>
        </td>
        <td style="text-align: right; padding-right: 1.5rem;">
            <!-- Pemicu Modal JavaScript -->
            <button class="btn-edit" onclick="openEditModal(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['nama'])) ?>', <?= $d['harga'] ?>, <?= $d['stok'] ?>, '<?= htmlspecialchars(addslashes($d['gambar'] ?? '')) ?>')">
                <img src="../assets/img/pencil.png" alt="Edit" style="width: 14px; height: 14px; filter: brightness(0) invert(1);"> Edit
            </button>
            <a href="?hapus=<?= $d['id']; ?>" onclick="return confirm('Yakin menghapus produk <?= htmlspecialchars(addslashes($d['nama'])) ?> secara permanen?')" class="btn-delete">
                <img src="../assets/img/bin.png" alt="Hapus" style="width: 14px; height: 14px; filter: brightness(0) invert(1);"> Hapus
            </a>
        </td>
    </tr>
    <?php } ?>
    </table>
</div>

        </div>
    </main>
</div>

<!-- MODAL EDIT PRODUK -->
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                <img src="../assets/img/pencil.png" alt="Edit" style="width: 22px; height: 22px;"> Edit Produk
            </h3>
            <button class="btn-close-modal" onclick="closeEditModal()">✖</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="gambar_lama" id="editGambarLama">
            <input type="hidden" name="hapus_gambar_flag" id="editHapusGambarFlag" value="0">
            
            <div class="form-row">
                <label>Nama Produk</label>
                <input type="text" name="nama" id="editNama" required>
            </div>
            <div class="form-row">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="editHarga" required>
            </div>
            <div class="form-row">
                <label>Stok Produk</label>
                <input type="number" name="stok" id="editStok" required>
            </div>
            <div class="form-row">
                <label>Ganti Gambar <span style="color:#94a3b8; font-weight:normal;">(Pilih file untuk mengganti gambar)</span></label>
                <div id="gambarPreviewContainer" style="margin-bottom: 0.75rem;"></div>
                <label style="display:flex; align-items:center; gap:0.75rem; border:1px solid #cbd5e1; padding:0.4rem 0.5rem; border-radius:6px; cursor:pointer; background:#ffffff; transition: 0.2s;">
                    <input type="file" id="editGambarBaru" name="gambar_baru" accept="image/*" style="display:none;" onchange="document.getElementById('fileNameDisplay').innerText = this.files[0] ? this.files[0].name : '(Belum ada file baru dipilih)'; document.getElementById('fileNameDisplay').style.color = '#10b981';">
                    <div style="background:#f1f5f9; padding:0.4rem 0.75rem; border-radius:4px; font-size:0.85rem; font-weight:600; color:#475569; border:1px solid #e2e8f0;">📂 Pilih File Gambar</div>
                    <span id="fileNameDisplay" style="font-size:0.85rem; color:#94a3b8; font-style:italic;">(Belum ada file baru dipilih)</span>
                </label>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn-primary" style="background:#ef4444;" onclick="closeEditModal()">Batal</button>
                <button type="submit" name="edit" class="btn-primary" style="background:#0f172a;">✅ Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nama, harga, stok, gambarLama) {
    document.getElementById('editId').value = id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editHarga').value = harga;
    document.getElementById('editStok').value = stok;
    document.getElementById('editGambarLama').value = gambarLama;
    
    document.getElementById('editHapusGambarFlag').value = '0';
    document.getElementById('editGambarBaru').value = '';
    document.getElementById('fileNameDisplay').innerText = '(Belum ada file baru dipilih)';
    document.getElementById('fileNameDisplay').style.color = '#94a3b8';
    
    // Tampilkan jejak file histori yang sedang aktif
    const preview = document.getElementById('gambarPreviewContainer');
    if (gambarLama !== '') {
        preview.innerHTML = `
            <div id="previewBox" style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="../assets/img/produk/${gambarLama}" style="width:48px; height:48px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1;"> 
                    <span style="font-size: 0.85rem; color: #64748b;">File terpasang saat ini:<br><strong style="color: #0f172a; font-size: 0.95rem;">${gambarLama}</strong></span>
                </div>
                <button type="button" onclick="removeCurrentImage()" style="background: none; border: none; font-size: 1.25rem; color: #ef4444; cursor: pointer; padding: 0 0.5rem; transition: 0.2s;" title="Hapus foto ini">&#10006;</button>
            </div>
        `;
    } else {
        preview.innerHTML = `<div style="font-size: 0.85rem; color: #ef4444; padding-bottom: 0.5rem;">⚠️ Belum ada foto yang terpasang pada produk ini.</div>`;
    }
    
    document.getElementById('editModal').classList.add('active');
}

function removeCurrentImage() {
    document.getElementById('editHapusGambarFlag').value = '1';
    document.getElementById('previewBox').style.display = 'none';
    const preview = document.getElementById('gambarPreviewContainer');
    preview.innerHTML += `<div style="font-size: 0.85rem; color: #ef4444; padding: 0.5rem 0; font-weight:600;">🗑 Foto akan dihapus permanen ketika Anda "Simpan Perubahan".</div>`;
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}
</script>

</body>
</html>