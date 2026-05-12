<?php
session_start();
include '../config/koneksi.php';

// 🔒 Proteksi login & kasir/admin
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.html");
    exit;
}
if ($_SESSION['role'] != 'kasir' && $_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// SIMPAN TRANSAKSI VIA POST (dari JSON)
$trx_success = false;
$trx_id = "";
$trx_data = [];

if (isset($_POST['transaksi_data'])) {
    $data_json = json_decode($_POST['transaksi_data'], true);
    if ($data_json && isset($data_json['items']) && count($data_json['items']) > 0) {
        $total = floatval($data_json['total']);
        $bayar = floatval($data_json['bayar']);
        $kembali = floatval($data_json['kembali']);
        $metode = mysqli_real_escape_string($conn, $data_json['metode'] ?? 'TUNAI');
        $kasir = $_SESSION['nama'] ?? 'Administrator';
        
        mysqli_query($conn, "INSERT INTO transaksi(total, bayar, kembali, metode, kasir) VALUES($total, $bayar, $kembali, '$metode', '$kasir')");
        $id_transaksi = mysqli_insert_id($conn);
        
        foreach ($data_json['items'] as $item) {
            $id = intval($item['id']);
            $qty = intval($item['qty']);
            $subtotal = floatval($item['subtotal']);
            
            mysqli_query($conn, "INSERT INTO detail_transaksi(transaksi_id,produk_id,qty,subtotal) VALUES($id_transaksi, $id, $qty, $subtotal)");
            mysqli_query($conn, "UPDATE produk SET stok = stok - $qty WHERE id=$id");
        }
        
        $trx_success = true;
        // Generate pseudo-receipt code
        $trx_id = "TRX-" . date('Ymd') . "-" . strtoupper(substr(md5($id_transaksi), 0, 5));
        $trx_data = $data_json;
        $trx_data['id'] = $id_transaksi;
        $trx_data['kode'] = $trx_id;
        $trx_data['date'] = date('j/n/Y, H.i.s');
    }
}

// Ambil List Produk (kirim ke JS)
$q_produk = mysqli_query($conn, "SELECT * FROM produk");
$produk_list = [];
while ($row = mysqli_fetch_assoc($q_produk)) {
    $produk_list[] = $row;
}
$produk_json = json_encode($produk_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan</title>

    <!-- ✅ CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/pos.css?v=<?= time() ?>"> <!-- Specific POS CSS -->
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-wrapper">
    <?php $page_title = 'Transaksi'; include '../includes/header.php'; ?>

    <main class="content-body" style="padding-bottom: 0;">
        <div class="pos-layout">
           
           <div class="pos-main">
              <div class="pos-topbar">
                  <div style="padding-right: 0.5rem; display: flex; align-items: center;">
                      <img src="../assets/img/search.png" alt="Search" style="width: 18px; filter: grayscale(1) opacity(0.6);">
                  </div>
                  <input type="text" id="searchInput" placeholder="Cari produk atau kode barcode...">
                  <select id="catSelect"><option>— Semua Kategori —</option></select>
                  <span class="product-count" id="prodCount">0 produk ditampilkan</span>
              </div>
              <div class="pos-products-grid" id="productGrid">
                 <!-- Generated via JS -->
              </div>
           </div>

           <div class="pos-sidebar">
              <div class="cart-header">
                 <h3>🛒 Keranjang</h3>
                 <button class="btn-clear-cart" id="btnClearCart" title="Kosongkan Keranjang">🗑️</button>
              </div>
              <div class="cart-items" id="cartItems">
                 <!-- Generated via JS -->
              </div>
              <div class="cart-summary">
                 <div class="summary-line total-line">
                    <span>Total</span>
                    <span id="cartTotal">Rp 0</span>
                 </div>
                 <div class="payment-method">
                    <p>Metode Bayar</p>
                    <div class="method-buttons">
                       <button type="button" class="btn-method active" data-method="Tunai">💵 Tunai</button>
                       <button type="button" class="btn-method" data-method="Transfer">
                           <img src="../assets/img/credit-card.png" alt="Transfer" style="width: 16px; margin-right: 4px; vertical-align: middle;"> Transfer
                       </button>
                       <button type="button" class="btn-method" data-method="QRIS" style="display: flex; align-items: center; justify-content: center;">
                           <img src="../assets/img/logo-qris.png" alt="QRIS" style="height: 24px; width: auto; object-fit: contain;">
                       </button>
                    </div>
                 </div>
                 <div class="input-money">
                    <p>Uang Diterima</p>
                    <input type="number" id="inputBayar" placeholder="0">
                 </div>
                 <div class="summary-line change-line">
                    <span>Kembalian:</span>
                    <span id="cartKembalian">Rp 0</span>
                 </div>
                 <input type="text" placeholder="Catatan (opsional)..." class="input-notes">
                 <form id="formCheckout" method="POST" action="">
                     <input type="hidden" name="transaksi_data" id="transaksiData">
                     <button type="button" id="btnProses" class="btn-proses-checkout" disabled>✅ Proses Pembayaran</button>
                 </form>
              </div>
           </div>

        </div>
    </main>
</div>

<!-- MODAL STRUK -->
<?php if ($trx_success): ?>
<div class="receipt-modal-overlay" id="receiptModal">
   <div class="receipt-modal">
       <div class="receipt-header">
           <h3>🧾 Transaksi Berhasil</h3>
           <button class="btn-close-modal" onclick="window.location.href='transaksi.php'">✖</button>
       </div>
       <div class="receipt-body" id="printArea">
           <div class="receipt-paper">
               =================================<br>
               <div style="text-align: center;">KOPERASI DIGITAL POS</div>
               =================================<br>
               Kode : <?= $trx_data['kode'] ?><br>
               Tgl  : <?= $trx_data['date'] ?><br>
               =================================<br>
               <table style="width: 100%; font-size: inherit;">
                  <?php foreach($trx_data['items'] as $item): ?>
                  <tr>
                     <td style="padding-top: 0.5rem;"><?= htmlspecialchars($item['name']) ?></td>
                     <td style="text-align: right; padding-top: 0.5rem;"><?= $item['qty'] ?>x</td>
                  </tr>
                  <tr>
                     <td></td>
                     <td style="text-align: right;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                  </tr>
                  <?php endforeach; ?>
               </table>
               =================================<br>
               <table style="width: 100%; font-size: inherit;">
                  <tr><td>TOTAL</td><td style="text-align: right;">Rp <?= number_format($trx_data['total'], 0, ',', '.') ?></td></tr>
                  <tr><td>BAYAR</td><td style="text-align: right;">Rp <?= number_format($trx_data['bayar'], 0, ',', '.') ?></td></tr>
                  <tr><td>KEMBALI</td><td style="text-align: right;">Rp <?= number_format($trx_data['kembali'], 0, ',', '.') ?></td></tr>
               </table>
               =================================<br>
               <div style="text-align: center;">Terima Kasih! 🙏</div>
               =================================<br>
           </div>
       </div>
       <div class="receipt-actions">
           <button class="btn-print" onclick="printReceipt()">🖨️ Cetak</button>
           <button class="btn-new-trx" onclick="window.location.href='transaksi.php'">+ Transaksi Baru</button>
       </div>
   </div>
</div>
<script>
function printReceipt() {
    var prtContent = document.getElementById("printArea");
    var WinPrint = window.open('', '', 'left=0,top=0,width=400,height=600,toolbar=0,scrollbars=0,status=0');
    WinPrint.document.write('<html><head><title>Print Struk</title>');
    WinPrint.document.write('<style>body { font-family: monospace; font-size: 14px; margin: 0; padding: 20px; text-align: left; } table { width: 100%; border-collapse: collapse; } </style>');
    WinPrint.document.write('</head><body>');
    WinPrint.document.write(prtContent.innerHTML);
    WinPrint.document.write('</body></html>');
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    WinPrint.close();
}
</script>
<?php endif; ?>

<!-- MODAL QRIS SIMULATION -->
<div class="receipt-modal-overlay" id="qrisModal" style="display: none;">
    <div class="receipt-modal qris-modal">
        <div class="receipt-header">
            <h3>📱 Pembayaran QRIS</h3>
            <button class="btn-close-modal" onclick="closeQRISModal()">✖</button>
        </div>
        <div class="qris-content">
            <div class="qris-info">Scan QR di bawah untuk membayar</div>
            <div class="qris-amount" id="qrisDisplayAmount">Rp 0</div>
            
            <div class="qris-qr-container">
                <img src="" id="qrisImage" class="qris-qr-image" alt="QR Code">
                <div id="qrisLoading" style="position: absolute; display: none;">⏳ Loading QR...</div>
            </div>

            <div id="qrisVerificationArea" style="width: 100%;">
                <button type="button" class="btn-verify-qris" id="btnVerifyQRIS" onclick="simulateQRISVerification()">
                    ✅ Konfirmasi Selesai Bayar
                </button>
            </div>
            
            <div id="qrisSuccessUI" style="display: none; width: 100%;">
                <div class="qris-success-badge">
                    <span>🎉 Pembayaran Berhasil!</span>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">Silakan tutup jendela ini untuk memproses struk.</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TRANSFER SIMULATION -->
<div class="receipt-modal-overlay" id="transferModal" style="display: none;">
    <div class="receipt-modal qris-modal">
        <div class="receipt-header" style="background: #1e40af; border: none;">
            <h3 style="color: white;">🏦 Bank Transfer</h3>
            <button class="btn-close-modal" onclick="closeTransferModal()" style="color: white;">✖</button>
        </div>
        <div class="qris-content">
            <div class="qris-info">Silakan bayar sesuai nominal ke rekening:</div>
            <div class="qris-amount" id="tfDisplayAmount" style="color: #1e40af;">Rp 0</div>
            
            <div class="transfer-card">
                <div class="bank-logo-wrap">
                    <img src="../assets/img/credit-card.png" style="width: 24px;">
                    <span style="font-weight: 800; color: #1e3a8a;">Virtual Bank Koperasi</span>
                </div>
                
                <div class="bank-info-row">
                    <div class="bank-label">Nomor Rekening</div>
                    <div class="bank-value">
                        129 0822 4567 8901
                    </div>
                </div>
                
                <div class="bank-info-row">
                    <div class="bank-label">Nama Penerima</div>
                    <div class="bank-value">KOPERASI DIGITAL POS</div>
                </div>
            </div>

            <div class="transfer-alert">
                <span>ℹ️</span>
                <span>Pastikan Anda telah melakukan transfer sebelum menekan konfirmasi.</span>
            </div>

            <div id="tfVerificationArea" style="width: 100%;">
                <button type="button" class="btn-verify-qris" id="btnVerifyTF" onclick="simulateTFVerification()" style="background: #1e40af;">
                    ✅ Konfirmasi Dana Masuk
                </button>
            </div>
            
            <div id="tfSuccessUI" style="display: none; width: 100%;">
                <div class="qris-success-badge">
                    <span>🎉 Dana Berhasil Diverifikasi!</span>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">Pembayaran telah diterima sistem bank.</p>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT POS -->
<script>
const products = <?= $produk_json ?>;
let cart = {};

const grid = document.getElementById('productGrid');
const cartItemsContainer = document.getElementById('cartItems');
const elTotal = document.getElementById('cartTotal');
const elKembalian = document.getElementById('cartKembalian');
const inputBayar = document.getElementById('inputBayar');
const btnProses = document.getElementById('btnProses');
const transaksiDataObj = document.getElementById('transaksiData');
const formCheckout = document.getElementById('formCheckout');
const prodCount = document.getElementById('prodCount');
const searchInput = document.getElementById('searchInput');

let currentMethod = "Tunai";
let qrisPaid = false;
let tfPaid = false;

// Format Rupiah
function formatRp(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function renderProducts(filter = "") {
    grid.innerHTML = "";
    let count = 0;
    products.forEach(p => {
        if (filter && !p.nama.toLowerCase().includes(filter.toLowerCase())) return;
        count++;
        
        const isOutOfStock = parseInt(p.stok) <= 0;
        
        const card = document.createElement('div');
        card.className = `pos-product-card ${isOutOfStock ? 'empty-stock' : ''}`;
        card.onclick = () => { if(!isOutOfStock) addToCart(p); };
        
        const photoHtml = p.gambar 
            ? `<img src="../assets/img/produk/${p.gambar}" style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; display: block; margin-left: auto; margin-right: auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">`
            : `<div class="pos-product-icon">🛒</div>`;
        
        card.innerHTML = `
            ${photoHtml}
            <div class="pos-product-name">${p.nama}</div>
            <div class="pos-product-price">${formatRp(p.harga)}</div>
            <div class="pos-product-stock">Stok: ${p.stok}</div>
        `;
        grid.appendChild(card);
    });
    prodCount.innerText = `${count} produk ditampilkan`;
}

function addToCart(p) {
    if (cart[p.id]) {
        if (cart[p.id].qty < p.stok) {
            cart[p.id].qty++;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        cart[p.id] = { ...p, qty: 1 };
    }
    renderCart();
}

function updateQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
        delete cart[id];
    } else {
        const prodMatch = products.find(prod => prod.id == id);
        if (prodMatch && cart[id].qty > prodMatch.stok) {
            cart[id].qty = prodMatch.stok;
            alert('Mencapai batas stok!');
        }
    }
    renderCart();
}

function renderCart() {
    cartItemsContainer.innerHTML = "";
    let total = 0;
    let itemCount = Object.keys(cart).length;
    
    if (itemCount === 0) {
        cartItemsContainer.innerHTML = `
            <div class="cart-empty-state">
                <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <br>Belum ada produk dipilih
            </div>
        `;
        elTotal.innerText = "Rp 0";
        btnProses.disabled = true;
        recalcChange();
        return;
    }
    
    btnProses.disabled = false;
    
    Object.values(cart).forEach(item => {
        let subtotal = item.harga * item.qty;
        total += subtotal;
        
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${item.nama}</div>
                <div class="cart-item-price">${formatRp(item.harga)}</div>
                <div class="cart-item-qty">
                    <button type="button" class="btn-qty" onclick="updateQty(${item.id}, -1)">-</button>
                    <span>${item.qty}</span>
                    <button type="button" class="btn-qty" onclick="updateQty(${item.id}, 1)">+</button>
                </div>
            </div>
            <div class="cart-item-sum">
                ${formatRp(subtotal)}
            </div>
        `;
        cartItemsContainer.appendChild(div);
    });
    
    elTotal.innerText = formatRp(total);
    cartItemsContainer.dataset.total = total;
    recalcChange();
}

function recalcChange() {
    let total = parseInt(cartItemsContainer.dataset.total) || 0;
    let bayar = parseInt(inputBayar.value) || 0;
    let change = bayar - total;
    
    if (total === 0) {
       elKembalian.innerText = "Rp 0";
       elKembalian.style.color = "#22c55e";
    } else {
       if (change < 0 && currentMethod === 'Tunai') {
           elKembalian.innerText = "Kurang " + formatRp(Math.abs(change));
           elKembalian.style.color = "#ef4444";
           btnProses.disabled = true;
       } else {
           elKembalian.innerText = formatRp(change > 0 ? change : 0);
           elKembalian.style.color = "#22c55e";
           btnProses.disabled = false;
       }
    }
}

inputBayar.addEventListener('input', recalcChange);
searchInput.addEventListener('input', (e) => renderProducts(e.target.value));
document.getElementById('btnClearCart').addEventListener('click', () => {cart = {}; renderCart();});

document.querySelectorAll('.btn-method').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const targetBtn = e.currentTarget;
        document.querySelectorAll('.btn-method').forEach(b => b.classList.remove('active'));
        targetBtn.classList.add('active');
        currentMethod = targetBtn.dataset.method;
        
        if (currentMethod === 'QRIS') {
            if (!qrisPaid) openQRISModal();
            inputBayar.value = cartItemsContainer.dataset.total || 0;
            inputBayar.readOnly = true;
        } else if (currentMethod === 'Transfer') {
            if (!tfPaid) openTransferModal();
            inputBayar.value = cartItemsContainer.dataset.total || 0;
            inputBayar.readOnly = true;
        } else {
            inputBayar.value = "";
            inputBayar.readOnly = false;
        }
        recalcChange();
    });
});

function openQRISModal() {
    const total = parseInt(cartItemsContainer.dataset.total) || 0;
    if (total <= 0) {
        alert("Keranjang masih kosong!");
        return;
    }
    
    document.getElementById('qrisDisplayAmount').innerText = formatRp(total);
    document.getElementById('qrisModal').style.display = 'flex';
    document.getElementById('qrisLoading').style.display = 'block';
    
    // Fake QR Generation via API
    const qrData = "PAY_KOPERASI_" + Date.now() + "_" + total;
    document.getElementById('qrisImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${qrData}`;
    document.getElementById('qrisImage').onload = () => {
        document.getElementById('qrisLoading').style.display = 'none';
    };
}

function closeQRISModal() {
    document.getElementById('qrisModal').style.display = 'none';
}

function simulateQRISVerification() {
    const btn = document.getElementById('btnVerifyQRIS');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Menghubungkan ke Server Bank...';
    
    setTimeout(() => {
        btn.innerHTML = '<div class="spinner"></div> Verifikasi Nominal...';
        setTimeout(() => {
            document.getElementById('qrisVerificationArea').style.display = 'none';
            document.getElementById('qrisSuccessUI').style.display = 'block';
            qrisPaid = true;
            btnProses.disabled = false;
            btnProses.innerHTML = '✅ Proses Pembayaran (QRIS OK)';
        }, 1500);
    }, 1500);
}

function openTransferModal() {
    const total = parseInt(cartItemsContainer.dataset.total) || 0;
    if (total <= 0) {
        alert("Keranjang masih kosong!");
        return;
    }
    
    document.getElementById('tfDisplayAmount').innerText = formatRp(total);
    document.getElementById('transferModal').style.display = 'flex';
}

function closeTransferModal() {
    document.getElementById('transferModal').style.display = 'none';
}

function simulateTFVerification() {
    const btn = document.getElementById('btnVerifyTF');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Mencari Mutasi Rekening...';
    
    setTimeout(() => {
        btn.innerHTML = '<div class="spinner"></div> Verifikasi Saldo Masuk...';
        setTimeout(() => {
            document.getElementById('tfVerificationArea').style.display = 'none';
            document.getElementById('tfSuccessUI').style.display = 'block';
            tfPaid = true;
            btnProses.disabled = false;
            btnProses.innerHTML = '✅ Proses Pembayaran (Transfer OK)';
        }, 1500);
    }, 1500);
}

btnProses.addEventListener('click', () => {
    let total = parseInt(cartItemsContainer.dataset.total) || 0;
    let bayar = parseInt(inputBayar.value) || 0;
    if (total > 0 && (bayar >= total || currentMethod !== 'Tunai')) {
        // Validasi QRIS simulasi
        if (currentMethod === 'QRIS' && !qrisPaid) {
            openQRISModal();
            return;
        }

        // Validasi Transfer simulasi
        if (currentMethod === 'Transfer' && !tfPaid) {
            openTransferModal();
            return;
        }

        const txData = {
            total: total,
            bayar: currentMethod !== 'Tunai' ? total : bayar,
            kembali: currentMethod !== 'Tunai' ? 0 : Math.max(0, bayar - total),
            metode: currentMethod,
            items: Object.values(cart).map(i => ({id: i.id, name: i.nama, qty: i.qty, subtotal: i.harga * i.qty}))
        };
        transaksiDataObj.value = JSON.stringify(txData);
        formCheckout.submit();
    } else {
        alert("Pembayaran tidak valid!");
    }
});

// Init
renderProducts();
renderCart();
</script>
</body>
</html>