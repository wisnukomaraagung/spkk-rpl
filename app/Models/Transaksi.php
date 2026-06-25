<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'tanggal',
        'total',
        'metode',
        'kasir',
        'bayar',
        'kembali',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'bayar'   => 'decimal:2',
            'kembali' => 'decimal:2',
        ];
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
