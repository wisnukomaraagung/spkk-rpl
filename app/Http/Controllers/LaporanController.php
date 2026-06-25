<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function parseFilters(Request $request)
    {
        $mode = $request->get('mode', 'semua');
        $date_val = $request->get('date');

        if (!$date_val) {
            $date_val = $mode == 'bulanan' ? now()->format('Y-m') : now()->format('Y-m-d');
        }

        $query = Transaksi::query();
        $human_filter = "Keseluruhan Waktu";

        if ($mode == 'harian') {
            $query->whereDate('tanggal', $date_val);
            $human_filter = Carbon::parse($date_val)->translatedFormat('d F Y');
        } elseif ($mode == 'bulanan') {
            $year = Carbon::parse($date_val . '-01')->year;
            $month = Carbon::parse($date_val . '-01')->month;
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
            $human_filter = Carbon::parse($date_val . '-01')->translatedFormat('F Y');
        }

        return [$query, $mode, $date_val, $human_filter];
    }

    public function index(Request $request)
    {
        [$query, $mode, $date_val, $human_filter] = $this->parseFilters($request);

        // Copy query for statistics
        $statsQuery = clone $query;
        $total_trx = $statsQuery->count();
        $revenue = $statsQuery->sum('total');
        $average = $total_trx > 0 ? $revenue / $total_trx : 0;

        // Transactions list
        $data_trx = $query->orderBy('tanggal', 'desc')->get();

        // Best seller products in filtered period
        $bestSellerQuery = DetailTransaksi::join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->select('produk.nama', DB::raw('SUM(detail_transaksi.qty) as terjual'), DB::raw('SUM(detail_transaksi.subtotal) as omzet'))
            ->groupBy('produk.nama', 'detail_transaksi.produk_id')
            ->orderBy('terjual', 'desc')
            ->limit(5);

        if ($mode == 'harian') {
            $bestSellerQuery->whereDate('transaksi.tanggal', $date_val);
        } elseif ($mode == 'bulanan') {
            $year = Carbon::parse($date_val . '-01')->year;
            $month = Carbon::parse($date_val . '-01')->month;
            $bestSellerQuery->whereYear('transaksi.tanggal', $year)->whereMonth('transaksi.tanggal', $month);
        }

        $data_best = $bestSellerQuery->get();

        return view('laporan.index', compact(
            'mode',
            'date_val',
            'human_filter',
            'total_trx',
            'revenue',
            'average',
            'data_trx',
            'data_best'
        ));
    }

    public function print(Request $request)
    {
        [$query, $mode, $date_val, $human_filter] = $this->parseFilters($request);

        $data_trx = $query->orderBy('tanggal', 'desc')->get();
        $total = $data_trx->sum('total');

        return view('laporan.cetak', compact('data_trx', 'total', 'human_filter'));
    }
}
