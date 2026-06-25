<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Support\Facades\File;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::all();
        return view('produk.index', compact('produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $gambar_nama = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $gambar_nama = 'PRD_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img/produk'), $gambar_nama);
        }

        Produk::create([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'gambar' => $gambar_nama,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'gambar_baru' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'hapus_gambar_flag' => 'nullable|string',
        ]);

        $gambar_nama = $produk->gambar;

        // Cek flag hapus gambar
        if ($request->hapus_gambar_flag === '1') {
            if ($produk->gambar && File::exists(public_path('assets/img/produk/' . $produk->gambar))) {
                File::delete(public_path('assets/img/produk/' . $produk->gambar));
            }
            $gambar_nama = null;
        }

        if ($request->hasFile('gambar_baru')) {
            // Hapus file lama jika ada
            if ($produk->gambar && File::exists(public_path('assets/img/produk/' . $produk->gambar))) {
                File::delete(public_path('assets/img/produk/' . $produk->gambar));
            }

            $file = $request->file('gambar_baru');
            $gambar_nama = 'PRD_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img/produk'), $gambar_nama);
        }

        $produk->update([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'gambar' => $gambar_nama,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->gambar && File::exists(public_path('assets/img/produk/' . $produk->gambar))) {
            File::delete(public_path('assets/img/produk/' . $produk->gambar));
        }

        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
