<?php
session_start();
if (!isset($_SESSION['login_admin'])) { header("Location: login.php"); exit; }

require 'koneksi.php';
$pesan_sukses = "";

/* =========================================================
   🌟 FUNGSI AJAIB UNTUK PROSES GAMBAR (RESIZE BIASA) 🌟
======================================================== */
function processUploadImage($file, $type = 'general') {
    $folder = 'uploads/';
    $tmp_name = $file['tmp_name']; $orig_name = $file['name']; $file_size = $file['size'];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) { return "gagal_ekstensi"; }
    // Batasi menjadi 50MB (50.000.000 bytes) agar server tidak hang
if ($file_size > 50000000) { return "gagal_ukuran"; }

    list($width, $height, $type_code) = getimagesize($tmp_name);
    switch ($type_code) {
        case IMAGETYPE_JPEG: $srcImage = imagecreatefromjpeg($tmp_name); break;
        case IMAGETYPE_PNG:  $srcImage = imagecreatefrompng($tmp_name);  break;
        case IMAGETYPE_WEBP: $srcImage = imagecreatefromwebp($tmp_name); break;
        default: return "gagal_bukan_gambar";
    }

    $target_w = 500;
    if ($width <= $target_w) {
        $nama_baru = time() . '_' . rand(100,999) . '_' . preg_replace('/[^a-zA-Z0-9.\-]/', '_', $orig_name);
        move_uploaded_file($tmp_name, $folder . $nama_baru); 
        return $nama_baru;
    }
    
    $target_h = $target_w / ($width / $height);
    $dstImage = imagecreatetruecolor($target_w, $target_h);
    imagealphablending($dstImage, false); imagesavealpha($dstImage, true);
    imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $target_w, $target_h, $width, $height);

    $nama_baru = time() . '_' . rand(100,999) . '.' . $file_ext;
    $dst_path = $folder . $nama_baru;

    switch ($type_code) {
        case IMAGETYPE_JPEG: imagejpeg($dstImage, $dst_path, 85); break;
        case IMAGETYPE_PNG:  imagepng($dstImage, $dst_path, 9); break;
        case IMAGETYPE_WEBP: imagewebp($dstImage, $dst_path, 85); break;
    }
    imagedestroy($srcImage); imagedestroy($dstImage);
    return $nama_baru;
}

/* =========================================
   PROSES KONTEN DATABASE
========================================= */

if (isset($_POST['tambah_admin'])) {
    $user_baru = mysqli_real_escape_string($koneksi, $_POST['username_baru']);
    $pass_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
    $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$user_baru'");
    if (mysqli_num_rows($cek) > 0) { header("Location: admin.php?pesan=gagal_username"); exit; } 
    else { mysqli_query($koneksi, "INSERT INTO admin (username, password) VALUES ('$user_baru', '$pass_baru')"); header("Location: admin.php?pesan=admin_sukses"); exit; }
}

if (isset($_POST['ganti_password'])) {
    $user_target = mysqli_real_escape_string($koneksi, $_POST['username_target']);
    $password_baru = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
    mysqli_query($koneksi, "UPDATE admin SET password='$password_baru' WHERE username='$user_target'");
    header("Location: admin.php?pesan=password_sukses"); exit;
}

// FIX: MODIFIKASI SIMPAN KONTEN UNTUK FOTO UTAMA
if (isset($_POST['simpan_konten'])) {
    $hb = mysqli_real_escape_string($koneksi, $_POST['hero_badge']); $hj = mysqli_real_escape_string($koneksi, $_POST['hero_judul']); $ht = mysqli_real_escape_string($koneksi, $_POST['hero_teks']);
    $jk = mysqli_real_escape_string($koneksi, $_POST['judul_keunggulan']); $jpr = mysqli_real_escape_string($koneksi, $_POST['judul_program']);
    $jpa = mysqli_real_escape_string($koneksi, $_POST['judul_paket']); $jt = mysqli_real_escape_string($koneksi, $_POST['judul_testimoni']);
    $ft = mysqli_real_escape_string($koneksi, $_POST['footer_teks']);
    
    // Cek apakah ada file foto utama yang diupload
    if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] == 0) {
        $upload = processUploadImage($_FILES['foto_utama']);
        if(strpos($upload, 'gagal_') !== false) { header("Location: admin.php?pesan=$upload"); exit; }
        // Update teks DAN foto
        mysqli_query($koneksi, "UPDATE konten_web SET hero_badge='$hb', hero_judul='$hj', hero_teks='$ht', judul_keunggulan='$jk', judul_program='$jpr', judul_paket='$jpa', judul_testimoni='$jt', footer_teks='$ft', foto_utama='$upload' WHERE id=1");
    } else {
        // Update teks SAJA (foto tidak diubah)
        mysqli_query($koneksi, "UPDATE konten_web SET hero_badge='$hb', hero_judul='$hj', hero_teks='$ht', judul_keunggulan='$jk', judul_program='$jpr', judul_paket='$jpa', judul_testimoni='$jt', footer_teks='$ft' WHERE id=1");
    }
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['simpan_stat'])) {
    $murid = mysqli_real_escape_string($koneksi, $_POST['jml_murid']); $tutor = mysqli_real_escape_string($koneksi, $_POST['jml_tutor']);
    $cabang = mysqli_real_escape_string($koneksi, $_POST['jml_cabang']); $puas = mysqli_real_escape_string($koneksi, $_POST['persen_puas']);
    mysqli_query($koneksi, "UPDATE statistik SET jml_murid='$murid', jml_tutor='$tutor', jml_cabang='$cabang', persen_puas='$puas' WHERE id=1");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['tambah_keunggulan'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']); $desk = mysqli_real_escape_string($koneksi, $_POST['deskripsi']); $icon = mysqli_real_escape_string($koneksi, $_POST['icon']);
    $gambar = "";
    if (isset($_FILES['gambar_keunggulan']) && $_FILES['gambar_keunggulan']['error'] == 0) {
        $upload = processUploadImage($_FILES['gambar_keunggulan']);
        if(strpos($upload, 'gagal_') !== false) { header("Location: admin.php?pesan=$upload"); exit; }
        $gambar = $upload;
    }
    mysqli_query($koneksi, "INSERT INTO keunggulan (judul, deskripsi, icon, gambar) VALUES ('$judul', '$desk', '$icon', '$gambar')");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['simpan_keunggulan'])) {
    $id = (int)$_POST['id_keunggulan']; $icon = mysqli_real_escape_string($koneksi, $_POST['icon']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_keunggulan']); $deskripsi = mysqli_real_escape_string($koneksi, $_POST['desk_keunggulan']);
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload = processUploadImage($_FILES['gambar']);
        if(strpos($upload, 'gagal_') !== false) { header("Location: admin.php?pesan=$upload"); exit; }
        mysqli_query($koneksi, "UPDATE keunggulan SET icon='$icon', gambar='$upload', judul='$judul', deskripsi='$deskripsi' WHERE id='$id'");
    } else { 
        mysqli_query($koneksi, "UPDATE keunggulan SET icon='$icon', judul='$judul', deskripsi='$deskripsi' WHERE id='$id'"); 
    }
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['tambah_program'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']); $desk = mysqli_real_escape_string($koneksi, $_POST['deskripsi']); $emoji = mysqli_real_escape_string($koneksi, $_POST['emoji']);
    $gambar = "";
    if (isset($_FILES['gambar_program']) && $_FILES['gambar_program']['error'] == 0) {
        $upload = processUploadImage($_FILES['gambar_program']);
        if(strpos($upload, 'gagal_') !== false) { header("Location: admin.php?pesan=$upload"); exit; }
        $gambar = $upload;
    }
    mysqli_query($koneksi, "INSERT INTO program (nama_program, deskripsi, emoji, gambar) VALUES ('$nama', '$desk', '$emoji', '$gambar')");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['simpan_program'])) {
    $id = (int)$_POST['id_program']; $emoji = mysqli_real_escape_string($koneksi, $_POST['emoji']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_program']); $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload = processUploadImage($_FILES['gambar']);
        if(strpos($upload, 'gagal_') !== false) { header("Location: admin.php?pesan=$upload"); exit; }
        mysqli_query($koneksi, "UPDATE program SET emoji='$emoji', nama_program='$nama', deskripsi='$deskripsi', gambar='$upload' WHERE id='$id'");
    } else { 
        mysqli_query($koneksi, "UPDATE program SET emoji='$emoji', nama_program='$nama', deskripsi='$deskripsi' WHERE id='$id'"); 
    }
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['tambah_paket'])) {
    $urutan = (int)$_POST['urutan_baru']; $nama = mysqli_real_escape_string($koneksi, $_POST['nama_paket_baru']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga_baru']); $durasi = mysqli_real_escape_string($koneksi, $_POST['durasi_baru']);
    $f1 = mysqli_real_escape_string($koneksi, $_POST['fasilitas1_baru']); $f2 = mysqli_real_escape_string($koneksi, $_POST['fasilitas2_baru']);
    $f3 = mysqli_real_escape_string($koneksi, $_POST['fasilitas3_baru']); $f4 = mysqli_real_escape_string($koneksi, $_POST['fasilitas4_baru']);
    $is_popular = isset($_POST['is_popular_baru']) ? 1 : 0;
    mysqli_query($koneksi, "INSERT INTO paket (urutan, nama_paket, harga, durasi, fasilitas1, fasilitas2, fasilitas3, fasilitas4, is_popular) VALUES ('$urutan', '$nama', '$harga', '$durasi', '$f1', '$f2', '$f3', '$f4', '$is_popular')");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['simpan_paket'])) {
    $id = (int)$_POST['id_paket']; $urutan = (int)$_POST['urutan']; $nama = mysqli_real_escape_string($koneksi, $_POST['nama_paket']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']); $durasi = mysqli_real_escape_string($koneksi, $_POST['durasi']);
    $f1 = mysqli_real_escape_string($koneksi, $_POST['fasilitas1']); $f2 = mysqli_real_escape_string($koneksi, $_POST['fasilitas2']);
    $f3 = mysqli_real_escape_string($koneksi, $_POST['fasilitas3']); $f4 = mysqli_real_escape_string($koneksi, $_POST['fasilitas4']);
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    mysqli_query($koneksi, "UPDATE paket SET urutan='$urutan', nama_paket='$nama', harga='$harga', durasi='$durasi', fasilitas1='$f1', fasilitas2='$f2', fasilitas3='$f3', fasilitas4='$f4', is_popular='$is_popular' WHERE id='$id'");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_POST['simpan_kontak'])) {
    $wa = mysqli_real_escape_string($koneksi, $_POST['telepon']); $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']); $ig = mysqli_real_escape_string($koneksi, $_POST['instagram']);
    $fb = mysqli_real_escape_string($koneksi, $_POST['facebook']); $yt = mysqli_real_escape_string($koneksi, $_POST['youtube']);
    mysqli_query($koneksi, "UPDATE pengaturan SET telepon='$wa', alamat='$alamat', email='$email', instagram='$ig', facebook='$fb', youtube='$yt' WHERE id=1");
    header("Location: admin.php?pesan=sukses"); exit;
}

/* =========================================================
   🌟 PROSES SIMPAN FOTO GALERI (DARI CROPPER JS) 🌟
======================================================== */
if (isset($_POST['tambah_kegiatan'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_kegiatan']);
    $gambar = "";

    if (!empty($_POST['cropped_image'])) {
        $image_parts = explode(";base64,", $_POST['cropped_image']);
        $image_base64 = base64_decode($image_parts[1]);
        $nama_baru = time() . '_' . rand(100,999) . '.jpg';
        
        file_put_contents('uploads/' . $nama_baru, $image_base64);
        $gambar = $nama_baru;
    }

    mysqli_query($koneksi, "INSERT INTO kegiatan (judul, gambar) VALUES ('$judul', '$gambar')");
    header("Location: admin.php?pesan=sukses"); exit;
}

if (isset($_GET['hapus'])) {
    $tabel = mysqli_real_escape_string($koneksi, $_GET['tabel']); $id = (int)$_GET['id'];
    if($tabel == 'kegiatan') {
        $qkeg = mysqli_query($koneksi, "SELECT gambar FROM kegiatan WHERE id=$id");
        if($row = mysqli_fetch_assoc($qkeg)) {
            if(file_exists("uploads/".$row['gambar'])) { unlink("uploads/".$row['gambar']); }
        }
    }
    mysqli_query($koneksi, "DELETE FROM $tabel WHERE id=$id");
    header("Location: admin.php?pesan=sukses"); exit;
}

$d_stat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM statistik WHERE id=1"));
$d_peng = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id=1"));
$d_konten = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM konten_web WHERE id=1"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Kertas Calistung</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background: #FAFBFF; color: #1B2559; padding: 40px; }
        .dashboard { max-width: 1000px; margin: auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        h2 { color: #1E73FF; border-bottom: 2px solid #F1F5F9; padding-bottom: 15px; margin-bottom: 30px; }
        .form-box { background: #F8FAFC; padding: 25px; border-radius: 15px; border: 1px solid #E2E8F0; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; } label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Poppins'; }
        .btn-simpan { background: #06D6A0; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-weight: 600; font-family: 'Poppins';}
        .btn-logout { background: #EF476F; color: white; text-decoration: none; padding: 10px 20px; border-radius: 10px; float: right; font-size: 14px; font-weight: 600; }
        .btn-tambah { background: #1E73FF; color: white; padding: 8px 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-bottom: 15px; display: inline-block; text-decoration: none; font-size: 13px;}
        .btn-batal { background: #64748B; color: white; padding: 12px 25px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-left: 10px; display: inline-block; font-size: 14px;}
        .btn-edit, .btn-hapus { display: inline-block; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; color: white; margin-right: 5px; margin-bottom: 8px; transition: 0.3s;}
        .btn-edit { background: #1E73FF; } .btn-hapus { background: #EF476F; text-align: center; }
        .notif { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .sukses { background: #E4FFE7; color: #06D6A0; } .gagal { background: #FFE5EB; color: #EF476F; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; } th, td { padding: 12px; border: 1px solid #E2E8F0; text-align: left; font-size: 14px; } th { background: #F8FAFC; }
        
        /* Cropper Custom CSS */
        .cropper-view-box, .cropper-face { border-radius: 8px; }
    </style>
</head>
<body>

<div class="dashboard">
    <a href="logout.php" class="btn-logout">Logout</a>
    <a href="index.php" target="_blank" class="btn-logout" style="background: #1E73FF; margin-right: 10px;">Lihat Website</a>
    <h2>Dashboard Admin Kertas</h2>

    <?php if(isset($_GET['pesan'])): ?>
        <div class="notif <?php echo ($_GET['pesan'] == 'gagal_ekstensi' || $_GET['pesan'] == 'gagal_ukuran' || $_GET['pesan'] == 'gagal_username') ? 'gagal' : 'sukses'; ?>">
            <?php 
                if($_GET['pesan'] == 'sukses') echo "Data berhasil diperbarui!";
                if($_GET['pesan'] == 'admin_sukses') echo "Berhasil! Admin baru telah ditambahkan.";
                if($_GET['pesan'] == 'password_sukses') echo "Berhasil! Password admin telah diperbarui.";
                if($_GET['pesan'] == 'gagal_username') echo "Gagal! Username tersebut sudah terdaftar, gunakan nama lain.";
                if($_GET['pesan'] == 'gagal_ekstensi') echo "Gagal Upload: Format file salah! Gunakan JPG/PNG/WEBP.";
                if($_GET['pesan'] == 'gagal_ukuran') echo "Gagal Upload: File terlalu besar! Maksimal 2MB.";
            ?>
        </div>
    <?php endif; ?>

    <div class="form-box" style="border-left: 5px solid #FF8A00;">
        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Pengaturan Teks & Konten Website</h3>
            
            <div style="background: white; padding: 15px; border-radius: 10px; border: 1px dashed #FF8A00; margin-bottom: 20px; display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: center;">
                <div id="boxPreviewUtama" style="<?php echo (empty($d_konten['foto_utama'])) ? 'display:none;' : ''; ?>">
                    <img id="gambarPreviewUtama" src="<?php echo (empty($d_konten['foto_utama'])) ? '' : 'uploads/'.$d_konten['foto_utama']; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid #E2E8F0;">
                </div>
                <div>
                    <label style="color: #FF8A00;">Ganti Foto Utama (Hero Image) - Max 2MB</label>
                    <input type="file" name="foto_utama" id="inputFotoUtama" accept="image/*" style="background: #F8FAFC;">
                    <p style="font-size: 12px; color: #64748B; margin-top: 5px; font-weight: normal;">*Biarkan kosong jika tidak ingin mengganti foto saat ini.</p>
                </div>
            </div>

            <script>
                document.getElementById('inputFotoUtama').addEventListener('change', function(){ 
                    const f=this.files[0]; 
                    if(f){ 
                        const r=new FileReader(); 
                        r.onload=function(e){ 
                            document.getElementById('gambarPreviewUtama').src=e.target.result; 
                            document.getElementById('boxPreviewUtama').style.display='block'; 
                        }; 
                        r.readAsDataURL(f); 
                    } 
                });
            </script>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Slogan Kecil (Badge Atas)</label><input type="text" name="hero_badge" value="<?php echo htmlspecialchars($d_konten['hero_badge']); ?>"></div>
                <div class="form-group"><label>Judul Utama</label><input type="text" name="hero_judul" value="<?php echo htmlspecialchars($d_konten['hero_judul']); ?>"></div>
                <div class="form-group" style="grid-column: span 2;"><label>Deskripsi Bawah Judul</label><textarea name="hero_teks" rows="2"><?php echo htmlspecialchars($d_konten['hero_teks']); ?></textarea></div>
                <div class="form-group"><label>Judul Section Keunggulan</label><input type="text" name="judul_keunggulan" value="<?php echo htmlspecialchars($d_konten['judul_keunggulan']); ?>"></div>
                <div class="form-group"><label>Judul Section Program</label><input type="text" name="judul_program" value="<?php echo htmlspecialchars($d_konten['judul_program']); ?>"></div>
                <div class="form-group"><label>Judul Section Paket</label><input type="text" name="judul_paket" value="<?php echo htmlspecialchars($d_konten['judul_paket']); ?>"></div>
                <div class="form-group"><label>Judul Section Testimoni</label><input type="text" name="judul_testimoni" value="<?php echo htmlspecialchars($d_konten['judul_testimoni']); ?>"></div>
                <div class="form-group" style="grid-column: span 2;"><label>Deskripsi Footer</label><textarea name="footer_teks" rows="2"><?php echo htmlspecialchars($d_konten['footer_teks']); ?></textarea></div>
            </div>
            <button type="submit" name="simpan_konten" class="btn-simpan" style="background:#FF8A00;">Simpan Perubahan Teks & Foto</button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-box" style="border-left: 5px solid #1E73FF;">
            <form action="" method="POST">
                <h3>Tambah Admin Baru</h3>
                <div class="form-group"><label>Username Baru</label><input type="text" name="username_baru" required></div>
                <div class="form-group"><label>Password Akun Baru</label><input type="password" name="password_baru" required></div>
                <button type="submit" name="tambah_admin" class="btn-simpan" style="background:#1E73FF;">Daftarkan Admin</button>
            </form>
        </div>
        <div class="form-box" style="border-left: 5px solid #FFD43B;">
            <form action="" method="POST">
                <h3>Update Password Admin</h3>
                <div class="form-group"><label>Pilih Username Admin</label>
                    <select name="username_target" required>
                        <option value="">Pilih Admin...</option>
                        <?php $q_admin = mysqli_query($koneksi, "SELECT username FROM admin"); while($adm = mysqli_fetch_assoc($q_admin)) { echo "<option value='{$adm['username']}'>{$adm['username']}</option>"; } ?>
                    </select>
                </div>
                <div class="form-group"><label>Password Baru</label><input type="password" name="password_baru" required></div>
                <button type="submit" name="ganti_password" class="btn-simpan" style="background:#FFD43B; color:#1B2559;">Update Password</button>
            </form>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-box" style="border-left: 5px solid #25D366;">
            <form action="" method="POST">
                <h3>Pengaturan WhatsApp & Kontak</h3>
                <div class="form-group"><label>No WhatsApp</label><input type="text" name="telepon" value="<?php echo $d_peng['telepon']; ?>" required></div>
                <div class="form-group"><label>Email</label><input type="text" name="email" value="<?php echo $d_peng['email']; ?>"></div>
                <div class="form-group"><label>Instagram Link</label><input type="text" name="instagram" value="<?php echo $d_peng['instagram']; ?>"></div>
                <div class="form-group"><label>Facebook Link</label><input type="text" name="facebook" value="<?php echo $d_peng['facebook']; ?>"></div>
                <div class="form-group"><label>Alamat Lengkap</label><input type="text" name="alamat" value="<?php echo $d_peng['alamat']; ?>"></div>
                <button type="submit" name="simpan_kontak" class="btn-simpan" style="background:#25D366;">Simpan Kontak</button>
            </form>
        </div>
        <div class="form-box">
            <form action="" method="POST">
                <h3>Angka Statistik Website</h3>
                <div class="form-group"><label>Murid Aktif</label><input type="text" name="jml_murid" value="<?php echo $d_stat['jml_murid']; ?>"></div>
                <div class="form-group"><label>Tutor</label><input type="text" name="jml_tutor" value="<?php echo $d_stat['jml_tutor']; ?>"></div>
                <div class="form-group"><label>Cabang</label><input type="text" name="jml_cabang" value="<?php echo $d_stat['jml_cabang']; ?>"></div>
                <div class="form-group"><label>Persen Puas</label><input type="text" name="persen_puas" value="<?php echo $d_stat['persen_puas']; ?>"></div>
                <button type="submit" name="simpan_stat" class="btn-simpan">Simpan Statistik</button>
            </form>
        </div>
    </div>

    <div class="form-box">
        <h3>Keunggulan (Mengapa Memilih Kami)</h3>
        <a href="admin.php?tambah_keunggulan=true" class="btn-tambah">+ Tambah Keunggulan</a>
        <?php if(isset($_GET['tambah_keunggulan'])): ?>
        <form action="" method="POST" enctype="multipart/form-data" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #06D6A0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Judul</label><input type="text" name="judul" required></div>
                <div class="form-group"><label>Upload Gambar (Max 2MB)</label><input type="file" name="gambar_keunggulan" id="inputFoto1" accept="image/*">
                    <div id="boxPreview1" style="display: none; margin-top: 15px;"><img id="gambarPreview1" src="" style="width: 100px; height: 100px; object-fit: contain; border-radius: 10px; border: 3px solid #E2E8F0;"></div>
                </div>
            </div>
            <div class="form-group"><label>Atau Kode Icon</label><input type="text" name="icon" placeholder="fas fa-check"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" required></textarea></div>
            <div style="margin-top: 10px;"><button type="submit" name="tambah_keunggulan" class="btn-simpan">Simpan Data</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <script> document.getElementById('inputFoto1').addEventListener('change', function(){ const f=this.files[0]; if(f){ const r=new FileReader(); r.onload=function(e){ document.getElementById('gambarPreview1').src=e.target.result; document.getElementById('boxPreview1').style.display='block'; }; r.readAsDataURL(f); } }); </script>
        <?php endif; ?>
        
        <?php if(isset($_GET['edit_keunggulan'])): $dk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM keunggulan WHERE id=".(int)$_GET['edit_keunggulan'])); ?>
        <form action="" method="POST" enctype="multipart/form-data" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #1E73FF;">
            <input type="hidden" name="id_keunggulan" value="<?php echo $dk['id']; ?>">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Judul</label><input type="text" name="judul_keunggulan" value="<?php echo $dk['judul']; ?>" required></div>
                <div class="form-group"><label>Ganti Gambar</label><input type="file" name="gambar" id="inputFoto2" accept="image/*">
                    <div id="boxPreview2" style="margin-top: 15px; <?php echo (empty($dk['gambar'])) ? 'display:none;' : ''; ?>"><img id="gambarPreview2" src="<?php echo (empty($dk['gambar'])) ? '' : 'uploads/'.$dk['gambar']; ?>" style="width: 100px; height: 100px; object-fit: contain; border-radius: 10px; border: 3px solid #E2E8F0;"></div>
                </div>
            </div>
            <div class="form-group"><label>Atau Kode Icon</label><input type="text" name="icon" value="<?php echo $dk['icon']; ?>"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="desk_keunggulan" required><?php echo $dk['deskripsi']; ?></textarea></div>
            <div style="margin-top: 10px;"><button type="submit" name="simpan_keunggulan" class="btn-simpan" style="background:#1E73FF;">Update Data</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <script> document.getElementById('inputFoto2').addEventListener('change', function(){ const f=this.files[0]; if(f){ const r=new FileReader(); r.onload=function(e){ document.getElementById('gambarPreview2').src=e.target.result; document.getElementById('boxPreview2').style.display='block'; }; r.readAsDataURL(f); } }); </script>
        <?php endif; ?>
        <table><tr><th style="width:50px;">Icon</th><th>Judul</th><th>Deskripsi</th><th style="width:130px;">Aksi</th></tr>
            <?php $qk = mysqli_query($koneksi, "SELECT * FROM keunggulan"); while($k = mysqli_fetch_assoc($qk)){ ?>
            <tr><td style="text-align:center;"><?php echo (!empty($k['gambar'])) ? "<img src='uploads/{$k['gambar']}' width='30'>" : "<i class='{$k['icon']}'></i>"; ?></td>
                <td><strong><?php echo $k['judul']; ?></strong></td><td><?php echo $k['deskripsi']; ?></td>
                <td><a href="admin.php?edit_keunggulan=<?php echo $k['id']; ?>" class="btn-edit">Edit</a><a href="admin.php?hapus=true&tabel=keunggulan&id=<?php echo $k['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus?');">Hapus</a></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="form-box">
        <h3>Program Belajar</h3>
        <a href="admin.php?tambah_program=true" class="btn-tambah">+ Tambah Program</a>
        <?php if(isset($_GET['tambah_program'])): ?>
        <form action="" method="POST" enctype="multipart/form-data" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #06D6A0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Nama Program</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>Upload Gambar (Max 2MB)</label><input type="file" name="gambar_program" id="inputFoto3" accept="image/*">
                    <div id="boxPreview3" style="display: none; margin-top: 15px;"><img id="gambarPreview3" src="" style="width: 100px; height: 100px; object-fit: contain; border-radius: 10px; border: 3px solid #E2E8F0;"></div>
                </div>
            </div>
            <div class="form-group"><label>Atau Emoji</label><input type="text" name="emoji" placeholder="Cth: 🎨"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" required></textarea></div>
            <div style="margin-top: 10px;"><button type="submit" name="tambah_program" class="btn-simpan">Simpan Program</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <script> document.getElementById('inputFoto3').addEventListener('change', function(){ const f=this.files[0]; if(f){ const r=new FileReader(); r.onload=function(e){ document.getElementById('gambarPreview3').src=e.target.result; document.getElementById('boxPreview3').style.display='block'; }; r.readAsDataURL(f); } }); </script>
        <?php endif; ?>
        <?php if(isset($_GET['edit_program'])): $dp = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM program WHERE id=".(int)$_GET['edit_program'])); ?>
        <form action="" method="POST" enctype="multipart/form-data" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #1E73FF;">
            <input type="hidden" name="id_program" value="<?php echo $dp['id']; ?>">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group"><label>Nama Program</label><input type="text" name="nama_program" value="<?php echo $dp['nama_program']; ?>" required></div>
                <div class="form-group"><label>Ganti Gambar</label><input type="file" name="gambar" id="inputFoto4" accept="image/*">
                    <div id="boxPreview4" style="margin-top: 15px; <?php echo (empty($dp['gambar'])) ? 'display:none;' : ''; ?>"><img id="gambarPreview4" src="<?php echo (empty($dp['gambar'])) ? '' : 'uploads/'.$dp['gambar']; ?>" style="width: 100px; height: 100px; object-fit: contain; border-radius: 10px; border: 3px solid #E2E8F0;"></div>
                </div>
            </div>
            <div class="form-group"><label>Atau Emoji</label><input type="text" name="emoji" value="<?php echo $dp['emoji']; ?>"></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" required><?php echo $dp['deskripsi']; ?></textarea></div>
            <div style="margin-top: 10px;"><button type="submit" name="simpan_program" class="btn-simpan" style="background:#1E73FF;">Update Program</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <script> document.getElementById('inputFoto4').addEventListener('change', function(){ const f=this.files[0]; if(f){ const r=new FileReader(); r.onload=function(e){ document.getElementById('gambarPreview4').src=e.target.result; document.getElementById('boxPreview4').style.display='block'; }; r.readAsDataURL(f); } }); </script>
        <?php endif; ?>
        <table><tr><th style="width:50px;">Icon</th><th>Program</th><th>Deskripsi</th><th style="width:130px;">Aksi</th></tr>
            <?php $qp = mysqli_query($koneksi, "SELECT * FROM program"); while($p = mysqli_fetch_assoc($qp)){ ?>
            <tr><td style="text-align:center;"><?php echo (!empty($p['gambar'])) ? "<img src='uploads/{$p['gambar']}' width='30'>" : "<span style='font-size:20px;'>{$p['emoji']}</span>"; ?></td>
                <td><strong><?php echo $p['nama_program']; ?></strong></td><td><?php echo $p['deskripsi']; ?></td>
                <td><a href="admin.php?edit_program=<?php echo $p['id']; ?>" class="btn-edit">Edit</a><a href="admin.php?hapus=true&tabel=program&id=<?php echo $p['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus?');">Hapus</a></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="form-box">
        <h3>Paket Belajar</h3>
        <a href="admin.php?tambah_paket=true" class="btn-tambah">+ Tambah Paket</a>
        <?php if(isset($_GET['tambah_paket'])): ?>
        <form action="" method="POST" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #06D6A0;">
            <div style="display: grid; grid-template-columns: 80px 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Urutan</label><input type="number" name="urutan_baru" value="1" required></div>
                <div class="form-group"><label>Nama</label><input type="text" name="nama_paket_baru" required></div>
                <div class="form-group"><label>Harga</label><input type="text" name="harga_baru" required></div>
                <div class="form-group"><label>Durasi</label><input type="text" name="durasi_baru" required></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Fasilitas 1</label><input type="text" name="fasilitas1_baru"></div><div class="form-group"><label>Fasilitas 2</label><input type="text" name="fasilitas2_baru"></div>
                <div class="form-group"><label>Fasilitas 3</label><input type="text" name="fasilitas3_baru"></div><div class="form-group"><label>Fasilitas 4</label><input type="text" name="fasilitas4_baru"></div>
            </div>
            <div class="form-group"><label><input type="checkbox" name="is_popular_baru"> Jadikan Terpopuler</label></div>
            <div style="margin-top: 10px;"><button type="submit" name="tambah_paket" class="btn-simpan">Simpan Paket</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <?php endif; ?>
        <?php if(isset($_GET['edit_paket'])): $dpkt = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM paket WHERE id=".(int)$_GET['edit_paket'])); ?>
        <form action="" method="POST" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #1E73FF;">
            <input type="hidden" name="id_paket" value="<?php echo $dpkt['id']; ?>">
            <div style="display: grid; grid-template-columns: 80px 1fr 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Urutan</label><input type="number" name="urutan" value="<?php echo $dpkt['urutan']; ?>" required></div>
                <div class="form-group"><label>Nama</label><input type="text" name="nama_paket" value="<?php echo $dpkt['nama_paket']; ?>" required></div>
                <div class="form-group"><label>Harga</label><input type="text" name="harga" value="<?php echo $dpkt['harga']; ?>" required></div>
                <div class="form-group"><label>Durasi</label><input type="text" name="durasi" value="<?php echo $dpkt['durasi']; ?>" required></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group"><label>Fasilitas 1</label><input type="text" name="fasilitas1" value="<?php echo $dpkt['fasilitas1']; ?>"></div><div class="form-group"><label>Fasilitas 2</label><input type="text" name="fasilitas2" value="<?php echo $dpkt['fasilitas2']; ?>"></div>
                <div class="form-group"><label>Fasilitas 3</label><input type="text" name="fasilitas3" value="<?php echo $dpkt['fasilitas3']; ?>"></div><div class="form-group"><label>Fasilitas 4</label><input type="text" name="fasilitas4" value="<?php echo $dpkt['fasilitas4']; ?>"></div>
            </div>
            <div class="form-group"><label><input type="checkbox" name="is_popular" <?php if($dpkt['is_popular']) echo "checked"; ?>> Jadikan Terpopuler</label></div>
            <div style="margin-top: 10px;"><button type="submit" name="simpan_paket" class="btn-simpan" style="background:#1E73FF;">Update Paket</button><a href="admin.php" class="btn-batal">Batal</a></div>
        </form>
        <?php endif; ?>
        <table><tr><th style="width:50px; text-align:center;">Urutan</th><th>Paket</th><th>Harga</th><th style="width:130px;">Aksi</th></tr>
            <?php $qpk = mysqli_query($koneksi, "SELECT * FROM paket ORDER BY urutan ASC"); while($pk = mysqli_fetch_assoc($qpk)){ ?>
            <tr><td style="text-align:center;"><strong><?php echo $pk['urutan']; ?></strong></td><td><?php echo $pk['nama_paket']; ?></td><td><?php echo $pk['harga']; ?></td>
                <td><a href="admin.php?edit_paket=<?php echo $pk['id']; ?>" class="btn-edit">Edit</a><a href="admin.php?hapus=true&tabel=paket&id=<?php echo $pk['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus?');">Hapus</a></td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="form-box" style="border-left: 5px solid #FF6B6B;">
        <h3>Galeri Kegiatan & Keseruan Belajar</h3>
        <a href="admin.php?tambah_kegiatan=true" class="btn-tambah" style="background:#FF6B6B;">+ Upload & Potong Foto</a>
        
        <?php if(isset($_GET['tambah_kegiatan'])): ?>
        <form action="" method="POST" id="formKegiatan" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:15px; border:2px dashed #FF6B6B;">
            
            <input type="hidden" name="tambah_kegiatan" value="1">
            <input type="hidden" name="cropped_image" id="cropped_image">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Judul Kegiatan</label>
                    <input type="text" name="judul_kegiatan" placeholder="Cth: Lomba Mewarnai Hari Ibu" required>
                </div>
                <div class="form-group">
                    <label>Pilih Foto (Anda bisa mengatur posisinya nanti)</label>
                    <input type="file" id="inputFotoKegiatan" accept="image/*" required>
                </div>
            </div>

            <div id="boxCrop" style="display: none; margin-top: 15px; border: 2px solid #E2E8F0; padding: 15px; border-radius: 10px; background: #F8FAFC;">
                <p style="font-size: 14px; color: #1E73FF; margin-bottom: 15px; font-weight: bold;">
                    <i class="fas fa-crop-alt"></i> Area Crop: Geser, perbesar, atau perkecil kotak transparan di bawah untuk menyesuaikan foto!
                </p>
                <div style="max-height: 500px; width: 100%; overflow: hidden; border-radius: 8px;">
                    <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button type="button" id="btnSimpanCrop" class="btn-simpan" style="background:#FF6B6B;">Selesai Potong & Upload</button>
                <a href="admin.php" class="btn-batal">Batal</a>
            </div>
        </form>

        <script>
            let cropper;
            const inputFoto = document.getElementById('inputFotoKegiatan');
            const imageToCrop = document.getElementById('imageToCrop');
            const boxCrop = document.getElementById('boxCrop');
            const btnSimpanCrop = document.getElementById('btnSimpanCrop');
            const formKegiatan = document.getElementById('formKegiatan');
            const croppedImageInput = document.getElementById('cropped_image');

            inputFoto.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imageToCrop.src = event.target.result;
                        boxCrop.style.display = 'block';
                        
                        // Hancurkan cropper lama jika pengguna memilih ulang foto lain
                        if (cropper) { cropper.destroy(); }
                        
                        // Inisialisasi plugin Cropper.js
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 4 / 3, // Mengunci area potong (Rasio 4:3)
                            viewMode: 2, // Agar kotak tidak bisa ditarik keluar dari foto
                            autoCropArea: 0.9,
                            background: true,
                        });
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Aksi saat tombol "Selesai Potong & Upload" ditekan
            btnSimpanCrop.addEventListener('click', function() {
                // Validasi form manual
                if (!document.querySelector('input[name="judul_kegiatan"]').value) {
                    alert("Judul kegiatan wajib diisi!"); return;
                }
                if (!inputFoto.value) {
                    alert("Pilih foto terlebih dahulu!"); return;
                }
                
                if (cropper) {
                    // Ambil hasil gambar yang ada di dalam kotak (Resolusi pas 800x600)
                    const canvas = cropper.getCroppedCanvas({
                        width: 800,
                        height: 600,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });
                    
                    // Ubah jadi teks Base64 dan simpan di input hidden (Kualitas 85%)
                    croppedImageInput.value = canvas.toDataURL('image/jpeg', 0.85); 
                    
                    // Efek loading
                    btnSimpanCrop.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Mengupload...";
                    btnSimpanCrop.style.opacity = "0.7";
                    btnSimpanCrop.disabled = true;

                    // Kirim form ke PHP
                    formKegiatan.submit();
                }
            });
        </script>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top:20px;">
            <?php 
            $qkeg = mysqli_query($koneksi, "SELECT * FROM kegiatan ORDER BY id DESC"); 
            while($kg = mysqli_fetch_assoc($qkeg)){ 
            ?>
                <div style="border: 1px solid #E2E8F0; border-radius: 10px; padding: 10px; text-align: center; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <img src="uploads/<?php echo $kg['gambar']; ?>" style="width: 100%; height: 100px; object-fit: cover; border-radius: 5px; margin-bottom: 10px;">
                    <p style="font-size: 12px; font-weight: bold; margin:0 0 10px 0; color: #1B2559;"><?php echo $kg['judul']; ?></p>
                    <a href="admin.php?hapus=true&tabel=kegiatan&id=<?php echo $kg['id']; ?>" class="btn-hapus" style="margin:0; display: block; box-sizing: border-box;" onclick="return confirm('Hapus foto ini dari galeri?');">Hapus</a>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="form-box">
        <h3>Manajemen Testimoni</h3>
        <table><tr><th>Nama</th><th>Bintang</th><th>Ulasan</th><th style="width:80px;">Aksi</th></tr>
            <?php $qt = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC"); while($t = mysqli_fetch_assoc($qt)){ ?>
            <tr><td><?php echo $t['nama_ortu']; ?></td><td><?php echo $t['bintang']; ?> ⭐</td><td><?php echo $t['ulasan']; ?></td>
                <td><a href="admin.php?hapus=true&tabel=testimoni&id=<?php echo $t['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus ulasan ini?');">Hapus</a></td></tr>
            <?php } ?>
        </table>
    </div>

</div>

</body>
</html>