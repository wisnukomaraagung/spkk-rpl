<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['nama' => 'Pensil',          'harga' => 5000,  'stok' => 50],
            ['nama' => 'Tip x',           'harga' => 5000,  'stok' => 30],
            ['nama' => 'Buku',            'harga' => 20000, 'stok' => 20],
            ['nama' => 'Penggaris 30 cm', 'harga' => 5000,  'stok' => 25],
            ['nama' => 'Penghapus',       'harga' => 5000,  'stok' => 50],
            ['nama' => 'Materai',         'harga' => 12000, 'stok' => 30],
            ['nama' => 'Map hijau',       'harga' => 5000,  'stok' => 50],
        ];

        foreach ($products as $product) {
            Produk::updateOrCreate(
                ['nama' => $product['nama']],
                $product
            );
        }
    }
}
