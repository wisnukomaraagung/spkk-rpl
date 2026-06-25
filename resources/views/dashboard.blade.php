@extends('layouts.app')

@section('title', 'Dashboard Utama')
@section('page-title', 'Dashboard Utama')

@section('content')
<!-- SUMMARY CARDS -->
<div class="stat-cards-grid">
    
    @if (auth()->user()->role == 'kasir' || auth()->user()->role == 'admin')
    <div class="stat-card card-blue">
        <div class="stat-card-info">
            <h4>Transaksi Hari Ini</h4>
            <h2>{{ $trans_hari }}</h2>
        </div>
        <div class="stat-card-icon">🛒</div>
    </div>
    @endif

    <div class="stat-card card-green">
        <div class="stat-card-info">
            <h4>Penjualan Hari Ini</h4>
            <h2>Rp {{ number_format($jual_hari, 0, ',', '.') }}</h2>
        </div>
        <div class="stat-card-icon">💵</div>
    </div>

    @if (auth()->user()->role == 'manager' || auth()->user()->role == 'admin')
    <div class="stat-card card-cyan">
        <div class="stat-card-info">
            <h4>Penjualan Bulan Ini</h4>
            <h2>Rp {{ number_format($jual_bln, 0, ',', '.') }}</h2>
        </div>
        <div class="stat-card-icon">📅</div>
    </div>
    @endif

    @if (auth()->user()->role == 'admin')
    <div class="stat-card card-orange">
        <div class="stat-card-info">
            <h4>Stok Menipis</h4>
            <h2>{{ $stok_menipis }} Produk</h2>
        </div>
        <div class="stat-card-icon">📦</div>
    </div>
    @endif

</div>

<!-- PANELS GRID -->
<div class="panels-grid">
    <!-- Transaksi Terbaru Panel -->
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">⏱️ Transaksi Terbaru</h3>
            @if (auth()->user()->role == 'kasir' || auth()->user()->role == 'admin')
            <a href="{{ route('transaksi.index') }}" style="font-size: 0.85rem; color: #0284c7; text-decoration: none; border: 1px solid #e0f2fe; padding: 0.25rem 0.75rem; border-radius: 4px;">Lihat Semua</a>
            @endif
        </div>
        
        @if ($recent_trans->count() > 0)
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b;">
                <th style="padding: 0.5rem 0;">ID</th>
                <th>Waktu</th>
                <th style="text-align: right;">Total</th>
            </tr>
            @foreach ($recent_trans as $tr)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; font-weight: 600;">#{{ $tr->id }}</td>
                <td style="color: #475569;">{{ $tr->tanggal->format('H:i') }}</td>
                <td style="text-align: right; font-weight: 600; color: #10b981;">Rp {{ number_format($tr->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
        @else
        <div style="text-align: center; padding: 2rem 0; color: #94a3b8;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📥</div>
            Belum ada transaksi hari ini
        </div>
        @endif
    </div>

    <!-- Stok Menipis Panel (Hanya admin) -->
    @if (auth()->user()->role == 'admin')
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">⚠️ Stok Menipis</h3>
            <a href="{{ route('produk.index') }}" style="font-size: 0.85rem; color: #f59e0b; text-decoration: none; border: 1px solid #fef3c7; padding: 0.25rem 0.75rem; border-radius: 4px;">Kelola</a>
        </div>
        
        @if ($stok_menipis_list->count() > 0)
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b;">
                <th style="padding: 0.5rem 0;">Nama Produk</th>
                <th style="text-align: right;">Sisa Stok</th>
            </tr>
            @foreach ($stok_menipis_list as $st)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.75rem 0; font-weight: 500; color: #1e293b;">{{ $st->nama }}</td>
                <td style="text-align: right;">
                    <span style="background: #fee2e2; color: #ef4444; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">
                        {{ $st->stok }}
                    </span>
                </td>
            </tr>
            @endforeach
        </table>
        @else
        <div style="text-align: center; padding: 2rem 0; color: #10b981;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
            Semua stok aman
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
