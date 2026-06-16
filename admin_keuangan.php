<?php
session_start();
if (!isset($_SESSION['login_admin'])) { header("Location: login.php"); exit; }
require 'koneksi.php';

// Proses Simpan Data
if (isset($_POST['simpan_transaksi'])) {
    $tgl = $_POST['tanggal'];
    $kat = $_POST['kategori'];
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $jml = (float)str_replace('.', '', $_POST['jumlah']);
    
    mysqli_query($koneksi, "INSERT INTO keuangan (tanggal, kategori, keterangan, jumlah) VALUES ('$tgl', '$kat', '$ket', '$jml')");
    header("Location: admin_keuangan.php?pesan=sukses"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Manajemen Keuangan - Kertas Calistung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <style>
        body { padding: 20px; font-family: 'Poppins', sans-serif; }
        .card { padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Dashboard Keuangan</h2>
        <a href="admin.php" class="btn btn-secondary">Kembali ke Admin</a>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h4>Tambah Transaksi</h4>
                    <form method="POST">
                        <input type="date" name="tanggal" class="form-control mb-2" required>
                        <select name="kategori" class="form-control mb-2">
                            <option value="Pemasukan">Pemasukan (SPP)</option>
                            <option value="Operasional">Biaya Operasional</option>
                            <option value="Gaji">Gaji Pengajar</option>
                        </select>
                        <input type="text" name="keterangan" placeholder="Keterangan" class="form-control mb-2" required>
                        <input type="number" name="jumlah" placeholder="Jumlah (Rp)" class="form-control mb-2" required>
                        <button type="submit" name="simpan_transaksi" class="btn btn-primary btn-block">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <h4>Laporan Transaksi</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $q = mysqli_query($koneksi, "SELECT * FROM keuangan ORDER BY tanggal DESC");
                            while($row = mysqli_fetch_assoc($q)) {
                                echo "<tr>
                                    <td>{$row['tanggal']}</td>
                                    <td>{$row['kategori']}</td>
                                    <td>{$row['keterangan']}</td>
                                    <td>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>