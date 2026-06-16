<?php
// Memanggil koneksi database
require 'koneksi.php';

// 1. AMBIL NOMOR DARI ADMIN SECARA OTOMATIS
$query_pengaturan = mysqli_query($koneksi, "SELECT telepon FROM pengaturan WHERE id = 1");
$data_pengaturan = mysqli_fetch_assoc($query_pengaturan);
$no_tujuan = $data_pengaturan['telepon'];

// 2. LOGIKA MEMBERSIHKAN NOMOR (Ubah 08... jadi 628...)
// Agar link WhatsApp tidak error saat diklik
$no_bersih = preg_replace('/[^0-9]/', '', $no_tujuan);
if (substr($no_bersih, 0, 1) == '0') {
    $wa_final = '62' . substr($no_bersih, 1);
} else {
    $wa_final = $no_bersih;
}

// Menangkap nama paket jika pengunjung klik dari halaman utama
$paket_pilihan = isset($_GET['paket']) ? $_GET['paket'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Online | Kertas Calistung</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #FAFBFF; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box;}
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .logo-box { text-align: center; margin-bottom: 20px; }
        .logo-box img { height: 45px; }
        h2 { color: #1E73FF; text-align: center; margin: 0 0 5px 0; }
        p.subtitle { text-align: center; color: #64748B; font-size: 13px; margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #1B2559;}
        input, select, textarea { width: 100%; padding: 14px; margin-bottom: 20px; border: 1px solid #E2E8F0; border-radius: 12px; box-sizing: border-box; background: #F8FAFC; font-family: 'Poppins'; }
        .btn-wa { width: 100%; background: #25D366; color: white; border: none; padding: 16px; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 16px; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px;}
        .btn-wa:hover { background: #128C7E; transform: translateY(-2px); }
        .back { display: block; text-align: center; margin-top: 20px; color: #64748B; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

<div class="card">
    <div class="logo-box">
        <img src="assets/logo.png" alt="Logo Kertas Calistung">
    </div>
    <h2>Form Pendaftaran</h2>
    <p class="subtitle">Isi data di bawah ini untuk mendaftar via WhatsApp</p>

    <form id="formPendaftaran">
        <label><i class="fas fa-user"></i> Nama Lengkap Orang Tua</label>
        <input type="text" id="ortu" placeholder="Contoh: Bunda Alya" required>

        <label><i class="fas fa-child"></i> Nama Lengkap Anak</label>
        <input type="text" id="anak" placeholder="Contoh: Rafa" required>

        <label><i class="fas fa-book"></i> Pilih Paket Belajar</label>
        <select id="paket" required>
            <option value="">-- Pilih Paket --</option>
            <?php 
            $q_pkt = mysqli_query($koneksi, "SELECT nama_paket, harga FROM paket ORDER BY urutan ASC");
            while($p = mysqli_fetch_assoc($q_pkt)) {
                $sel = ($p['nama_paket'] == $paket_pilihan) ? 'selected' : '';
                echo "<option value='{$p['nama_paket']}' $sel>{$p['nama_paket']} - {$p['harga']}</option>";
            }
            ?>
        </select>

        <label><i class="fas fa-comment"></i> Pertanyaan Tambahan (Opsional)</label>
        <textarea id="catatan" rows="2" placeholder="Tulis di sini..."></textarea>

        <button type="submit" class="btn-wa"><i class="fab fa-whatsapp"></i> Kirim ke WhatsApp Admin</button>
    </form>
    
    <a href="index.php" class="back">&larr; Kembali ke Beranda</a>
</div>

<script>
document.getElementById('formPendaftaran').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const namaOrtu = document.getElementById('ortu').value;
    const namaAnak = document.getElementById('anak').value;
    const paket = document.getElementById('paket').value;
    const catatan = document.getElementById('catatan').value || "Tidak ada";
    
    // NOMOR INI OTOMATIS BERUBAH SESUAI YANG DIINPUT DI ADMIN
    const waAdmin = "<?php echo $wa_final; ?>";

    const pesan = `*PENDAFTARAN KERTAS CALISTUNG* 📝%0A%0AHalo Admin! Saya ingin mendaftarkan anak saya dengan detail berikut:%0A%0A👤 *Nama Orang Tua:* ${namaOrtu}%0A👶 *Nama Anak:* ${namaAnak}%0A📚 *Paket Dipilih:* ${paket}%0A💬 *Catatan:* ${catatan}%0A%0AMohon informasi untuk proses pendaftarannya ya!`;

    window.open(`https://wa.me/${waAdmin}?text=${pesan}`, '_blank');
});
</script>

</body>
</html>