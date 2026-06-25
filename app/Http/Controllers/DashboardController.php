<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Transaksi Hari Ini
        $trans_hari = Transaksi::whereDate('tanggal', $today)->count();

        // 2. Penjualan Hari Ini
        $jual_hari = Transaksi::whereDate('tanggal', $today)->sum('total') ?? 0;

        // 3. Penjualan Bulan Ini
        $jual_bln = Transaksi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('total') ?? 0;

        // 4. Produk Stok Menipis (< 10)
        $stok_menipis = Produk::where('stok', '<', 10)->count();

        // Transaksi Terbaru
        $recent_trans = Transaksi::orderBy('tanggal', 'desc')->limit(5)->get();

        // Daftar Stok Menipis
        $stok_menipis_list = Produk::where('stok', '<', 10)->orderBy('stok', 'asc')->limit(5)->get();

        return view('dashboard', compact(
            'trans_hari',
            'jual_hari',
            'jual_bln',
            'stok_menipis',
            'recent_trans',
            'stok_menipis_list'
        ));
    }
}
