<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->timestamp('tanggal')->useCurrent();
            $table->integer('total')->default(0);
            $table->string('metode', 50)->default('Tunai');
            $table->string('kasir', 100)->default('Administrator');
            $table->decimal('bayar', 12, 2)->default(0);
            $table->decimal('kembali', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
