<!DOCTYPE html>
<html>
<head>
    <title>Struk #{{ $transaksi->id }}</title>
    <style>
        body {
            font-family: monospace;
            width: 250px;
        }
        h3 {
            text-align: center;
        }
        .line {
            border-top: 1px dashed black;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<h3>KOPERASI KAMPUS</h3>
<p>Tanggal: {{ $transaksi->tanggal->format('Y-m-d H:i:s') }}</p>

<div class="line"></div>

@foreach ($transaksi->detailTransaksi as $d)
<p>
{{ $d->produk->nama ?? 'Produk Terhapus' }}<br>
{{ $d->qty }} x {{ number_format($d->subtotal / $d->qty) }} 
= {{ number_format($d->subtotal) }}
</p>
@endforeach

<div class="line"></div>

<h4>Total: Rp {{ number_format($transaksi->total) }}</h4>

<p>Terima kasih 🙏</p>

<script>
window.print();
</script>

</body>
</html>
