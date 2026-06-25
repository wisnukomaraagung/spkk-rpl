<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function index()
    {
        $produk = Produk::all();
        $produk_json = $produk->toJson();
        return view('transaksi.index', compact('produk_json'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaksi_data' => 'required|string',
        ]);

        $data = json_decode($request->transaksi_data, true);

        if (!$data || !isset($data['items']) || count($data['items']) == 0) {
            return redirect()->route('transaksi.index')->with('error', 'Data transaksi tidak valid.');
        }

        try {
            DB::transaction(function () use ($data, &$transaksi) {
                $transaksi = Transaksi::create([
                    'tanggal' => now(),
                    'total' => floatval($data['total']),
                    'metode' => $data['metode'] ?? 'Tunai',
                    'kasir' => Auth::user()->username ?? 'Administrator',
                    'bayar' => floatval($data['bayar']),
                    'kembali' => floatval($data['kembali']),
                ]);

                foreach ($data['items'] as $item) {
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $item['id'],
                        'qty' => $item['qty'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Potong stok
                    $produk = Produk::find($item['id']);
                    if ($produk) {
                        $produk->decrement('stok', $item['qty']);
                    }
                }
            });

            // Ambil data transaksi yang baru dibuat beserta detailnya
            $newTransaksi = Transaksi::with('detailTransaksi.produk')->latest()->first();
            $trx_id = "TRX-" . now()->format('Ymd') . "-" . strtoupper(substr(md5($newTransaksi->id), 0, 5));

            return redirect()->route('transaksi.index')->with([
                'trx_success' => true,
                'trx_id' => $trx_id,
                'trx_data' => [
                    'id' => $newTransaksi->id,
                    'kode' => $trx_id,
                    'date' => now()->format('j/n/Y, H.i.s'),
                    'total' => $newTransaksi->total,
                    'bayar' => $newTransaksi->bayar,
                    'kembali' => $newTransaksi->kembali,
                    'items' => $newTransaksi->detailTransaksi->map(function ($dt) {
                        return [
                            'name' => $dt->produk->nama ?? 'Produk Terhapus',
                            'qty' => $dt->qty,
                            'subtotal' => $dt->subtotal,
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function printReceipt($id)
    {
        $transaksi = Transaksi::with('detailTransaksi.produk')->findOrFail($id);
        return view('transaksi.struk', compact('transaksi'));
    }
}
