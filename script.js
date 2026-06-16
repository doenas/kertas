// Mengambil elemen tombol hamburger dan menu navigasi
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('nav-menu');

// Jika tombol hamburger diklik, munculkan atau sembunyikan menu
hamburger.addEventListener('click', function() {
    navMenu.classList.toggle('active');
});

// Jika salah satu menu diklik (misal klik "Paket Belajar"), otomatis tutup menunya
const navLinks = document.querySelectorAll('#nav-menu a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
    });
});