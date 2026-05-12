<?php
$conn = mysqli_connect("host.docker.internal", "root", "", "dbpos_koperasi");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>