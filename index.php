<?php
require 'koneksi.php';

$pesan_kirim = "";
if(isset($_POST['kirim_testimoni'])) {
    $nama_ortu = mysqli_real_escape_string($koneksi, $_POST['nama_ortu']);
    $bintang = (int)$_POST['bintang'];
    $ulasan = mysqli_real_escape_string($koneksi, $_POST['ulasan']);
    if(mysqli_query($koneksi, "INSERT INTO testimoni (nama_ortu, ulasan, bintang) VALUES ('$nama_ortu', '$ulasan', '$bintang')")) {
        $pesan_kirim = "Terima kasih! Ulasan Anda berhasil dikirim.";
    }
}

$data_stat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM statistik WHERE id = 1"));
$data_pengaturan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id = 1"));
$data_konten = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM konten_web WHERE id = 1")); 

$nomor_wa = preg_replace('/[^0-9]/', '', $data_pengaturan['telepon']); 
$nomor_wa_link = (substr($nomor_wa, 0, 1) == '0') ? '62' . substr($nomor_wa, 1) : $nomor_wa;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kertas Calistung | Belajar Menyenangkan untuk Anak</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container navbar">
        <div class="logo"><img src="assets/logo.png" alt="Logo Kertas Calistung" onerror="this.src='https://via.placeholder.com/150x50?text=Logo+Kertas';"></div>
        <nav id="nav-menu">
            <a href="#">Beranda</a>
            <a href="#keunggulan">Keunggulan</a>
            <a href="#program">Program</a>
            <a href="#paket">Paket</a>
            <a href="#kegiatan">Kegiatan</a>
            <a href="#testimoni">Testimoni</a>
        </nav>
        <div class="nav-actions">
            <a href="#paket" class="btn-yellow">Daftar Sekarang</a>
            <div class="hamburger" id="hamburger"><i class="fas fa-bars"></i></div>
        </div>
    </div>
</header>

<section class="hero container">
    <div class="hero-text" data-aos="fade-right">
        <div class="badge"><?php echo htmlspecialchars($data_konten['hero_badge']); ?></div>
        <h1><?php echo $data_konten['hero_judul']; ?></h1>
        <p><?php echo htmlspecialchars($data_konten['hero_teks']); ?></p>
        <div class="hero-buttons">
            <a href="#paket" class="btn-blue">Mulai Belajar</a>
            <a href="#program" class="btn-white">Lihat Program</a>
        </div>
    </div>
    
    <div class="hero-image" data-aos="fade-left" data-aos-delay="200">
        <?php
        // Mengambil nama file dari database (kolom 'foto_utama')
        $foto_utama = !empty($data_konten['foto_utama']) ? "uploads/".$data_konten['foto_utama'] : "assets/hero-anak.png";
        ?>
        <img src="<?php echo $foto_utama; ?>" class="hero-kids" alt="Anak Belajar" onerror="this.src='https://via.placeholder.com/500x500?text=Foto+Anak';">
    </div>
</section>

<section class="features" id="keunggulan">
    <div class="container">
        <div class="title" data-aos="fade-up">
            <h2><?php echo htmlspecialchars($data_konten['judul_keunggulan']); ?></h2>
        </div>
        <div class="feature-grid">
            <?php
            $q_keunggulan = mysqli_query($koneksi, "SELECT * FROM keunggulan"); $delay_k = 100;
            while($keung = mysqli_fetch_assoc($q_keunggulan)) { ?>
            <div class="feature-box" data-aos="zoom-in" data-aos-delay="<?php echo $delay_k; ?>">
                <div class="icon-circle">
                    <?php if(!empty($keung['gambar'])): ?>
                        <img src="uploads/<?php echo $keung['gambar']; ?>" alt="<?php echo $keung['judul']; ?>">
                    <?php else: ?>
                        <i class="<?php echo $keung['icon']; ?>"></i>
                    <?php endif; ?>
                </div>
                <h4><?php echo $keung['judul']; ?></h4>
                <p><?php echo $keung['deskripsi']; ?></p>
            </div>
            <?php $delay_k += 100; } ?>
        </div>
    </div>
</section>

<section class="program" id="program">
    <div class="container">
        <div class="title" data-aos="fade-up">
            <h2><?php echo htmlspecialchars($data_konten['judul_program']); ?></h2>
        </div>
        <div class="program-grid">
            <?php
            $q_program = mysqli_query($koneksi, "SELECT * FROM program"); $delay_p = 100;
            while($prog = mysqli_fetch_assoc($q_program)) { ?>
            <div class="card" data-aos="fade-up" data-aos-delay="<?php echo $delay_p; ?>">
                <div class="icon">
                    <?php if(!empty($prog['gambar'])): ?>
                        <img src="uploads/<?php echo $prog['gambar']; ?>" alt="Program">
                    <?php else: ?>
                        <span><?php echo $prog['emoji']; ?></span>
                    <?php endif; ?>
                </div>
                <h3><?php echo $prog['nama_program']; ?></h3>
                <p><?php echo $prog['deskripsi']; ?></p>
            </div>
            <?php $delay_p += 100; } ?>
        </div>
    </div>
</section>

<section class="pricing" id="paket">
    <div class="container">
        <div class="title" data-aos="fade-up">
            <h2><?php echo htmlspecialchars($data_konten['judul_paket']); ?></h2>
        </div>
        <div class="pricing-grid">
            <?php
            $query_paket = mysqli_query($koneksi, "SELECT * FROM paket ORDER BY urutan ASC"); $delay = 100;
            while($paket = mysqli_fetch_assoc($query_paket)) {
                $is_popular = ($paket['is_popular'] == 1) ? 'popular' : '';
                $btn_style = ($paket['is_popular'] == 1) ? 'btn-yellow' : 'btn-white';
                $aos_anim = ($paket['is_popular'] == 1) ? 'zoom-in' : 'fade-up';
            ?>
            <div class="price-card <?php echo $is_popular; ?>" data-aos="<?php echo $aos_anim; ?>" data-aos-delay="<?php echo $delay; ?>">
                <?php if($paket['is_popular'] == 1) echo '<div class="ribbon">Paling Diminati</div>'; ?>
                <h3><?php echo $paket['nama_paket']; ?></h3>
                <div class="price"><?php echo $paket['harga']; ?><span><?php echo $paket['durasi']; ?></span></div>
                <ul>
                    <?php if(!empty($paket['fasilitas1'])) echo "<li><i class='fas fa-check'></i> {$paket['fasilitas1']}</li>"; ?>
                    <?php if(!empty($paket['fasilitas2'])) echo "<li><i class='fas fa-check'></i> {$paket['fasilitas2']}</li>"; ?>
                    <?php if(!empty($paket['fasilitas3'])) echo "<li><i class='fas fa-check'></i> {$paket['fasilitas3']}</li>"; ?>
                    <?php if(!empty($paket['fasilitas4'])) echo "<li><i class='fas fa-check'></i> {$paket['fasilitas4']}</li>"; ?>
                </ul>
                <a href="daftar.php?paket=<?php echo urlencode($paket['nama_paket']); ?>" class="<?php echo $btn_style; ?>">Daftar Sekarang</a>
            </div>
            <?php $delay += 100; } ?>
        </div>
    </div>
</section>

<section class="stats" data-aos="fade-up">
    <div class="container">
        <div class="stats-box">
            <div><h3><?php echo $data_stat['jml_murid']; ?></h3><p>Murid Aktif</p></div>
            <div><h3><?php echo $data_stat['jml_tutor']; ?></h3><p>Tutor Ceria</p></div>
            <div><h3><?php echo $data_stat['jml_cabang']; ?></h3><p>Cabang</p></div>
            <div><h3><?php echo $data_stat['persen_puas']; ?></h3><p>Orang Tua Puas</p></div>
        </div>
    </div>
</section>

<section class="kegiatan" id="kegiatan">
    <div class="container">
        <div class="title" data-aos="fade-up">
            <h2>Keseruan Belajar Kami</h2>
            <p>Intip momen ceria anak-anak saat bermain dan belajar bersama KERTAS.</p>
        </div>
        <div class="gallery-grid">
            <?php
            $q_keg = mysqli_query($koneksi, "SELECT * FROM kegiatan ORDER BY id DESC");
            $delay_keg = 100;
            if(mysqli_num_rows($q_keg) > 0) {
                while($keg = mysqli_fetch_assoc($q_keg)) { ?>
                    <div class="gallery-item" data-aos="zoom-in" data-aos-delay="<?php echo $delay_keg; ?>">
                        <img src="uploads/<?php echo $keg['gambar']; ?>" alt="<?php echo $keg['judul']; ?>">
                        <div class="gallery-overlay">
                            <h4><?php echo $keg['judul']; ?></h4>
                        </div>
                    </div>
                <?php $delay_keg += 100; }
            } else {
                echo "<p style='text-align:center; color:#718096; width:100%;'>Belum ada foto kegiatan.</p>";
            }
            ?>
        </div>
    </div>
</section>

<section class="testimoni" id="testimoni">
    <div class="container">
        <div class="title" data-aos="fade-up">
            <h2><?php echo htmlspecialchars($data_konten['judul_testimoni']); ?></h2>
        </div>
        
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php
                $query_testi = mysqli_query($koneksi, "SELECT * FROM testimoni ORDER BY id DESC LIMIT 10");
                while($testi = mysqli_fetch_assoc($query_testi)) { 
                    $inisial = strtoupper(substr($testi['nama_ortu'], 0, 1));
                ?>
                    <div class="swiper-slide testi-card">
                        <i class="fas fa-quote-right quote-watermark"></i>
                        <div class="testi-header">
                            <div class="testi-avatar"><?php echo $inisial; ?></div>
                            <div class="testi-info">
                                <h4><?php echo htmlspecialchars($testi['nama_ortu']); ?></h4>
                                <div class="stars"><?php for($i=1; $i<=$testi['bintang']; $i++) echo "⭐"; ?></div>
                            </div>
                        </div>
                        <p>"<?php echo htmlspecialchars($testi['ulasan']); ?>"</p>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>

        <div class="form-testi-container" data-aos="fade-up">
            <h3 style="font-family: 'Fredoka'; color: #4A90E2; margin-bottom: 15px; font-size:24px;">Kirim Pengalaman Anda</h3>
            <?php if($pesan_kirim != "") echo "<div style='background:#E4FFE7; color:#00D09C; padding:15px; border-radius:15px; margin-bottom:20px; font-weight:700;'>$pesan_kirim</div>"; ?>
            <form action="#testimoni" method="POST" style="text-align: left;">
                <input type="text" name="nama_ortu" placeholder="Nama Anda (Cth: Bunda Alya)" required class="input-modern">
                <select name="bintang" required class="input-modern">
                    <option value="5">⭐⭐⭐⭐⭐ Sangat Puas</option>
                    <option value="4">⭐⭐⭐⭐ Puas</option><option value="3">⭐⭐⭐ Cukup</option>
                    <option value="2">⭐⭐ Kurang</option><option value="1">⭐ Sangat Kurang</option>
                </select>
                <textarea name="ulasan" rows="4" placeholder="Tulis pengalaman anak Anda..." required class="input-modern" style="resize: none;"></textarea>
                <button type="submit" name="kirim_testimoni" class="btn-blue" style="width: 100%; border: none; cursor: pointer;">Kirim Ulasan</button>
            </form>
        </div>
    </div>
</section>

<footer id="kontak">
    <div class="container footer-content">
        <div class="footer-col" data-aos="fade-right">
            <div class="logo"><img src="assets/logo.png" alt="Logo" style="height: 50px; margin-bottom: 15px;"></div>
            <p><?php echo htmlspecialchars($data_konten['footer_teks']); ?></p>
            <div class="socials">
                <a href="<?php echo htmlspecialchars($data_pengaturan['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="<?php echo htmlspecialchars($data_pengaturan['facebook']); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="<?php echo htmlspecialchars($data_pengaturan['youtube']); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-col" data-aos="fade-up" data-aos-delay="100">
            <h3>Menu Cepat</h3>
            <a href="#keunggulan">Keunggulan</a><a href="#program">Program Belajar</a><a href="#paket">Paket & Biaya</a><a href="#kegiatan">Galeri Kegiatan</a>
        </div>
        <div class="footer-col" data-aos="fade-left" data-aos-delay="200">
            <h3>Hubungi Kami</h3>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($data_pengaturan['alamat']); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($data_pengaturan['telepon']); ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($data_pengaturan['email']); ?></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Kertas Calistung. Seluruh Hak Cipta Dilindungi.</p>
    </div>
</footer>

<a href="https://wa.me/<?php echo $nomor_wa_link; ?>?text=Halo%20Admin%20Kertas,%20saya%20ingin%20bertanya." class="float-wa" target="_blank" title="Chat WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    AOS.init({ duration: 800, once: true, offset: 100 });

    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    hamburger.addEventListener('click', function() { navMenu.classList.toggle('active'); });
    const navLinks = document.querySelectorAll('#nav-menu a');
    navLinks.forEach(link => { link.addEventListener('click', () => { navMenu.classList.remove('active'); }); });

    // FIX: Perbaikan parameter Swiper agar tampil 1 kotak pas di HP dan 3 di Laptop
    var swiper = new Swiper(".mySwiper", {
        effect: "coverflow", 
        grabCursor: true,
        centeredSlides: true, 
        loop: true,
        coverflowEffect: {
            rotate: 0,       
            stretch: 50,     
            depth: 250,      
            modifier: 1,
            slideShadows: false, 
        },
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: { el: ".swiper-pagination", clickable: true },
        navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        breakpoints: {
            0: { slidesPerView: 1, spaceBetween: 20 }, 
            768: { slidesPerView: 3, spaceBetween: 0 }  
        },
    });
</script>

</body>
</html>