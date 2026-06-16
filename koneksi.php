<?php
$host     = "localhost";
$user     = "root"; // Username bawaan XAMPP
$password = "";     // Password bawaan XAMPP (kosong)
$db       = "kertas"; // Nama database sudah diubah menjadi kertas

$koneksi = mysqli_connect($host, $user, $password, $db);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>