<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;

// Public routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Role-based routes
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
    });

    Route::middleware(['role:admin,manager,kasir'])->group(function () {
        Route::get('/transaksi/{id}/print', [TransaksiController::class, 'printReceipt'])->name('transaksi.print');
    });

    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    });
});
