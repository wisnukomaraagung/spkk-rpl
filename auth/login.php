<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Menggunakan BINARY pencocokan huruf besar/kecil (case-sensitive) pada username dan password
$query = mysqli_query($conn, "SELECT * FROM users WHERE BINARY username='$username' AND BINARY password='$password'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $_SESSION['login'] = true;
    $_SESSION['user'] = $data['username'];
    $_SESSION['role'] = $data['role'];

    // Redirect ke dashboard universal
    header("Location: ../dashboard/index.php");
    exit;
} else {
    // Redirect kembali ke login.html dengan URL parameter error
    header("Location: login.html?error=1");
    exit;
}