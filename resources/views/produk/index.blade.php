@extends('layouts.app')

@section('title', 'Manajemen Produk')
@section('page-title', 'Manajemen Produk')

@section('styles')
<style>
    /* ── Tombol Aksi ── */
    .btn-primary { background: #3b82f6; color: white; padding: 0.65rem 1.25rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; }
    .btn-primary:hover { background: #2563eb; }
    .btn-edit   { background: #10b981; color: white; padding: 0.45rem 0.9rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; transition: 0.2s; }
    .btn-edit:hover { background: #059669; }
    .btn-delete { background: #ef4444; color: white; padding: 0.45rem 0.9rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem; transition: 0.2s; }
    .btn-delete:hover { background: #dc2626; }

    /* ── Layout split: form kiri, tabel kanan ── */
    .produk-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    /* ── Panel form tambah ── */
    .form-panel {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
        position: sticky;
        top: 1rem;
    }
    .form-panel-header {
        background: #0f172a;
        color: #ffffff;
        padding: 1rem 1.25rem;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-panel-body { padding: 1.25rem; }
    .form-row { margin-bottom: 1rem; display: flex; flex-direction: column; }
    .form-row label { font-weight: 600; margin-bottom: 0.35rem; font-size: 0.85rem; color: #475569; }
    .form-row input[type="text"],
    .form-row input[type="number"] {
        padding: 0.6rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        width: 100%;
        box-sizing: border-box;
        outline: none;
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-row input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .form-row input[type="file"] { font-size: 0.85rem; padding: 0.4rem 0; width: 100%; cursor: pointer; }

    /* ── Panel tabel ── */
    .table-panel {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .table-panel-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-panel-title { font-weight: 700; font-size: 1rem; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .badge-count { background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; }

    .table-produk { width: 100%; border-collapse: collapse; }
    .table-produk th { background: #f8fafc; color: #64748b; padding: 0.85rem 1rem; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-bottom: 2px solid #e2e8f0; }
    .table-produk td { padding: 0.9rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .table-produk tr:last-child td { border-bottom: none; }
    .table-produk tr:hover td { background: #f8fafc; }

    .thumbnail-img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; background: #f8fafc; display: block; margin: 0 auto; }
    .no-img { width: 44px; height: 44px; border-radius: 8px; border: 1px dashed #cbd5e1; background: #f8fafc; display: flex; justify-content: center; align-items: center; color: #94a3b8; font-size: 0.7rem; margin: 0 auto; }
    .badge-tipis { font-size: 0.7rem; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; margin-left: 4px; font-weight: 600; }

    /* ── Modal Edit ── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: 0.2s; padding: 1rem; box-sizing: border-box; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-content { background: white; width: 100%; max-width: 480px; border-radius: 12px; padding: 1.5rem; transform: translateY(20px); transition: 0.3s; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); box-sizing: border-box; }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
    .modal-header h3 { margin: 0; color: #1e293b; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
    .btn-close-modal { background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer; transition: 0.2s; }
    .btn-close-modal:hover { color: #ef4444; }
    .modal-content .form-row { margin-bottom: 0.85rem; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .produk-layout { grid-template-columns: 1fr; }
        .form-panel { position: static; }
    }
</style>
@endsection

@section('content')

@if (session('success'))
    <div style="background:#d1fae5; color:#065f46; padding:0.85rem 1.25rem; border-radius:8px; margin-bottom:1.5rem; border:1px solid #6ee7b7; font-weight:600;">
        ✅ {{ session('success') }}
    </div>
@endif

<div class="produk-layout">

    {{-- ── PANEL FORM TAMBAH (kiri) ── --}}
    <div class="form-panel">
        <div class="form-panel-header">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Produk Baru
        </div>
        <div class="form-panel-body">
            <form method="POST" action="{{ route('produk.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <label>Nama Produk</label>
                    <input type="text" name="nama" required placeholder="Contoh: Indomie Goreng" value="{{ old('nama') }}">
                    @error('nama')<span style="color:#ef4444; font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" placeholder="Contoh: 3500" value="{{ old('harga') }}">
                    @error('harga')<span style="color:#ef4444; font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <label>Stok Awal</label>
                    <input type="number" name="stok" required min="0" placeholder="Contoh: 100" value="{{ old('stok') }}">
                    @error('stok')<span style="color:#ef4444; font-size:0.8rem;">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <label>Gambar Produk <span style="color:#94a3b8; font-weight:400;">(Opsional)</span></label>
                    <input type="file" name="gambar" accept="image/*">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; justify-content:center; margin-top:0.5rem;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk
                </button>
            </form>
        </div>
    </div>

    {{-- ── PANEL TABEL DAFTAR PRODUK (kanan) ── --}}
    <div class="table-panel">
        <div class="table-panel-header">
            <h3 class="table-panel-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Daftar Produk
                <span class="badge-count">{{ $produk->count() }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-produk">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="text-align:center; width:60px;">Foto</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th style="text-align:right; padding-right:1.5rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @php $no = 1; @endphp
                @forelse ($produk as $d)
                <tr>
                    <td style="color:#94a3b8; font-size:0.85rem;">{{ $no++ }}</td>
                    <td style="text-align:center;">
                        @if ($d->gambar && file_exists(public_path('assets/img/produk/' . $d->gambar)))
                            <img src="{{ asset('assets/img/produk/' . $d->gambar) }}" class="thumbnail-img">
                        @else
                            <div class="no-img">N/A</div>
                        @endif
                    </td>
                    <td style="font-weight:600; color:#0f172a;">{{ $d->nama }}</td>
                    <td style="color:#10b981; font-weight:600;">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                    <td>
                        <span style="font-weight:600; {{ $d->stok <= 5 ? 'color:#ef4444;' : 'color:#475569;' }}">{{ $d->stok }}</span>
                        @if ($d->stok <= 5)<span class="badge-tipis">Tipis</span>@endif
                    </td>
                    <td style="text-align:right; padding-right:1rem;">
                        <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                            <button class="btn-edit" onclick="openEditModal({{ $d->id }}, '{{ addslashes($d->nama) }}', {{ $d->harga }}, {{ $d->stok }}, '{{ addslashes($d->gambar ?? '') }}')">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <form action="{{ route('produk.destroy', $d->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus produk \'{{ addslashes($d->nama) }}\'?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#94a3b8; padding:3rem;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40" style="margin:0 auto 0.75rem; display:block; color:#cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Belum ada produk. Tambahkan produk pertama di form sebelah kiri.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── MODAL EDIT ── --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Produk
            </h3>
            <button class="btn-close-modal" onclick="closeEditModal()">✕</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="hapus_gambar_flag" id="editHapusGambarFlag" value="0">

            <div class="form-row">
                <label>Nama Produk</label>
                <input type="text" name="nama" id="editNama" required>
            </div>
            <div class="form-row">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="editHarga" required min="0">
            </div>
            <div class="form-row">
                <label>Stok</label>
                <input type="number" name="stok" id="editStok" required min="0">
            </div>
            <div class="form-row">
                <label>Ganti Gambar <span style="color:#94a3b8; font-weight:400;">(opsional)</span></label>
                <div id="gambarPreviewContainer" style="margin-bottom:0.5rem;"></div>
                <label style="display:flex; align-items:center; gap:0.75rem; border:1px solid #cbd5e1; padding:0.4rem 0.6rem; border-radius:6px; cursor:pointer; background:#f8fafc;">
                    <input type="file" id="editGambarBaru" name="gambar_baru" accept="image/*" style="display:none;"
                        onchange="document.getElementById('fileNameDisplay').innerText = this.files[0] ? this.files[0].name : '(belum dipilih)'; document.getElementById('fileNameDisplay').style.color='#10b981';">
                    <span style="background:#e2e8f0; padding:0.35rem 0.7rem; border-radius:4px; font-size:0.82rem; font-weight:600; color:#475569; white-space:nowrap;">📂 Pilih File</span>
                    <span id="fileNameDisplay" style="font-size:0.82rem; color:#94a3b8; font-style:italic;">(belum dipilih)</span>
                </label>
            </div>
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.25rem;">
                <button type="button" onclick="closeEditModal()" style="padding:0.65rem 1.25rem; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:6px; font-weight:600; cursor:pointer;">Batal</button>
                <button type="submit" class="btn-primary" style="background:#0f172a;">✅ Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openEditModal(id, nama, harga, stok, gambarLama) {
    document.getElementById('editNama').value  = nama;
    document.getElementById('editHarga').value = harga;
    document.getElementById('editStok').value  = stok;
    document.getElementById('editHapusGambarFlag').value = '0';
    document.getElementById('editGambarBaru').value = '';
    document.getElementById('fileNameDisplay').innerText = '(belum dipilih)';
    document.getElementById('fileNameDisplay').style.color = '#94a3b8';
    document.getElementById('editForm').action = '/produk/' + id;

    const preview = document.getElementById('gambarPreviewContainer');
    if (gambarLama) {
        preview.innerHTML = `
            <div id="previewBox" style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:0.6rem 0.75rem; border:1px solid #e2e8f0; border-radius:6px;">
                <div style="display:flex; align-items:center; gap:0.6rem;">
                    <img src="{{ asset('assets/img/produk') }}/${gambarLama}" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1;">
                    <span style="font-size:0.82rem; color:#64748b;">Foto saat ini: <strong style="color:#0f172a;">${gambarLama}</strong></span>
                </div>
                <button type="button" onclick="removeCurrentImage()" title="Hapus foto" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1rem;">✕</button>
            </div>`;
    } else {
        preview.innerHTML = `<p style="font-size:0.82rem; color:#f59e0b; margin:0 0 0.4rem;">⚠️ Belum ada foto.</p>`;
    }

    document.getElementById('editModal').classList.add('active');
}

function removeCurrentImage() {
    document.getElementById('editHapusGambarFlag').value = '1';
    document.getElementById('gambarPreviewContainer').innerHTML =
        `<p style="font-size:0.82rem; color:#ef4444; font-weight:600; margin:0 0 0.4rem;">🗑 Foto akan dihapus saat disimpan.</p>`;
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Tutup modal klik di luar
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endsection
