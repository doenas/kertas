<?php
// Memanggil koneksi database
require 'koneksi.php';

// Username Anda
$username = "donas";

// Password baru yang Anda inginkan
$password_baru = "admin123";

// Sistem otomatis mengubahnya jadi kode enkripsi yang benar
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Perbarui database khusus untuk username 'donas'
$update = mysqli_query($koneksi, "UPDATE admin SET password='$password_hash' WHERE username='$username'");

if ($update) {
    echo "<h2 style='color:green; text-align:center; font-family:sans-serif; margin-top:50px;'>
            Sukses! Password untuk username '{$username}' berhasil direset menjadi: <b>{$password_baru}</b>
          </h2>
          <p style='text-align:center; font-family:sans-serif;'>
            <a href='login.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none; border-radius:5px;'>Pergi ke Halaman Login</a>
          </p>";
} else {
    echo "<h2 style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>
            Gagal mereset password. Pastikan username 'donas' benar-benar ada di database.
          </h2>";
}
?>